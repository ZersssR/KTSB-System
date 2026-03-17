<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';
$currentUser = getCurrentUser();

if (!isset($_GET['id'])) {
    header('Location: endorsements.php');
    exit;
}

$id = $_GET['id'];

require_once __DIR__ . '/../../../config/app.php';
$conn = getDBConnection();

// Fetch marine request details
$query = "SELECT mr.*, a.full_name as agent_name, a.username as agent_username, 
          u.username as requester_name, u.company_name as requester_company
          FROM marine_requests mr 
          LEFT JOIN agents a ON mr.assigned_agent_id = a.agent_id
          LEFT JOIN users u ON mr.user_id = u.user_id 
          WHERE mr.marine_id = ? AND mr.status = 'pending endorsement'";
$stmt = $conn->prepare($query);
$stmt->execute([$id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header('Location: endorsements.php');
    exit;
}

// Handle endorsement actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notes = $_POST['amendment_notes'] ?? '';
    $currentUserId = $currentUser['user_id'] ?? $currentUser['agent_id'];
    
    if ($action === 'endorse') {
        // Update status to endorsed with endorser info
        $updateStmt = $conn->prepare("UPDATE marine_requests 
                                      SET status = 'endorsed', 
                                          endorsed_by = ?,
                                          endorsed_at = NOW() 
                                      WHERE marine_id = ?");
        $updateStmt->execute([$currentUserId, $id]);
        
        // Record activity
        $activityStmt = $conn->prepare("INSERT INTO activities (user_id, activity_type, description, reference_id) VALUES (?, 'endorsement', 'Marine request endorsed', ?)");
        $activityStmt->execute([$currentUserId, $id]);
        
        header('Location: endorsements.php?message=endorsed');
        exit;
        
    } elseif ($action === 'request_amendment') {
        // Update status to request amendment with requester info
        $updateStmt = $conn->prepare("UPDATE marine_requests 
                                      SET status = 'request amendment', 
                                          amendment_requested_by = ?,
                                          amendment_requested_at = NOW(), 
                                          amendment_notes = ? 
                                      WHERE marine_id = ?");
        $updateStmt->execute([$currentUserId, $notes, $id]);
        
        // Record activity
        $activityStmt = $conn->prepare("INSERT INTO activities (user_id, activity_type, description, reference_id) VALUES (?, 'amendment_request', 'Amendment requested for marine request', ?)");
        $activityStmt->execute([$currentUserId, $id]);
        
        header('Location: endorsements.php?message=amendment_requested');
        exit;
    }
}

// Fetch related data
$crewStmt = $conn->prepare("SELECT * FROM marine_crew_details WHERE marine_id = ?");
$crewStmt->execute([$id]);
$crewData = $crewStmt->fetchAll(PDO::FETCH_ASSOC);

$otherServicesStmt = $conn->prepare("SELECT * FROM marine_other_services WHERE marine_id = ?");
$otherServicesStmt->execute([$id]);
$otherServices = $otherServicesStmt->fetchAll(PDO::FETCH_ASSOC);

$fuelWaterStmt = $conn->prepare("SELECT * FROM marine_fuel_water_services WHERE marine_id = ?");
$fuelWaterStmt->execute([$id]);
$fuelWaterServices = $fuelWaterStmt->fetchAll(PDO::FETCH_ASSOC);

$generalWorksStmt = $conn->prepare("SELECT * FROM marine_general_works WHERE marine_id = ?");
$generalWorksStmt->execute([$id]);
$generalWorks = $generalWorksStmt->fetchAll(PDO::FETCH_ASSOC);

$currentPage = 'endorsements.php';
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Endorse Marine Request</title>
    <script src="assets/js/tailwindcss.js"></script>
    <link href="assets/css/fonts.css" rel="stylesheet" />
    <link href="assets/css/material-icons.css" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#343A40",
                        "background-light": "#F8F9FA",
                        "background-dark": "#111921",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        /* Button hover effects */
.group:hover .group-hover\:translate-x-1 {
    transform: translateX(0.25rem);
}

.group:hover .group-hover\:-translate-x-0\.5 {
    transform: translateX(-0.125rem);
}

/* Smooth transitions */
.transform {
    transition-property: transform, opacity, box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Pulse animation for endorse button */
@keyframes gentle-pulse {
    0%, 100% {
        box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.4);
    }
    50% {
        box-shadow: 0 20px 30px -5px rgba(34, 197, 94, 0.6);
    }
}

#endorse-btn {
    animation: gentle-pulse 3s infinite;
}

#endorse-btn:hover {
    animation: none;
}
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        /* --- Base SVG Setup --- */
        .cool-mode-icon path {
            stroke-dasharray: 100;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 0s;
        }

        /* --- Wrapper Animation (Elastic Expand) --- */
        .cool-mode-wrapper {
            background-color: #16a34a !important;
            /* Force Green-600 */
            transform: scale(0);
            animation: scaleElastic 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            position: relative;
            z-index: 10;
        }

        @keyframes scaleElastic {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* --- Pulse Effect (Ripple Behind) --- */
        .cool-mode-wrapper::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background-color: rgba(74, 222, 128, 0.6);
            /* Green-400 opacity 0.6 */
            z-index: -1;
            animation: pulseRing 2s infinite;
        }

        @keyframes pulseRing {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }

            70% {
                transform: scale(1.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        /* --- Icon Animation (White Tick, Slow Start / Fast Finish) --- */
        .cool-mode-icon {
            color: white !important;
        }

        .cool-mode-icon path {
            /* Precise length for timing calculations (~20px) */
            stroke-dasharray: 20;
            stroke-dashoffset: 20;

            /* Delay 0.3s (starts as circle pops), Duration 0.4s */
            animation: drawVariableSpeed 0.4s linear 0.3s forwards;
        }

        @keyframes drawVariableSpeed {
            0% {
                stroke-dashoffset: 20;
            }

            60% {
                /* Slow Start: 60% of time to draw the short first leg */
                stroke-dashoffset: 14;
            }

            100% {
                /* Fast Finish: Remaining 40% of time to draw the long leg */
                stroke-dashoffset: 0;
            }
        }

        .detail-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03), 0 2px 6px rgba(0, 0, 0, 0.02);
            padding: 24px;
        }

        /* UPDATED: Larger status pill from marinedetail.php */
        .status-pill {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending-endorsement {
            background-color: #FCE7F3;
            color: #9D174D;
        }

        .status-pill-lg {
            padding: 8px 20px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
        }

        /* Standardized typography from marinedetail.php */
        .marine-detail-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }

        .marine-detail-section h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .marine-detail-section p,
        .marine-detail-section span {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .marine-detail-section table th {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .marine-detail-section table td {
            font-size: 14px;
            font-weight: 500;
        }

        .detail-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 24px 0;
            border: none;
        }

        .font-bold {
            font-weight: 700;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #212529;
        }

        .modal-message {
            margin-bottom: 25px;
            color: #4B5563;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-confirm {
            background-color: #06740b;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .btn-confirm:hover {
            background-color: #1f530a;
        }

        .btn-cancel {
            background-color: #EF4444;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .btn-cancel:hover {
            background-color: #DC2626;
        }

        .btn-secondary {
            background-color: #6B7280;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #4B5563;
        }

        .datetime-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* BOD number highlight from marinedetail.php */
        .bod-number-highlight {
            font-weight: 700;
            color: #1e40af;
            background-color: #eff6ff;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Attendance badges from marinedetail.php */
        .attendance-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .attendance-badge-show {
            background-color: #dcfce7;
            color: #166534;
        }

        .attendance-badge-no-show {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .attendance-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
        }

        .attendance-dot-show {
            background-color: #16a34a;
        }

        .attendance-dot-no-show {
            background-color: #dc2626;
        }

        /* Tooltip styles from marinedetail.php */
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: help;
            border-bottom: 1px dotted #9ca3af;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            background-color: #1f2937;
            color: #f9fafb;
            text-align: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            position: absolute;
            z-index: 10;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            pointer-events: none;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        .tooltip .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1f2937 transparent transparent transparent;
        }
    </style>
    <script src="tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
        
        <header class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <img src="assets/images/KSB Logo.JPG" alt="KSB Logo" class="h-10 w-auto object-contain mr-1 hidden md:block">
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Kuala Terengganu Support Base</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profile-dropdown-btn"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors"
                        title="Profile">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                    </button>
                    <div id="profile-dropdown"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 hidden z-50">
                        <div class="py-2">
                            <a href="profile.php"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">person</span>
                                <span>Profile</span>
                            </a>
                            <a href="logout.php"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">logout</span>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="endorsements.php" class="hover:text-primary">Endorsements</a>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200 font-bold"><?php echo htmlspecialchars($request['bod_no'] ?: 'N/A'); ?></span>
                        </nav>
                    </div>

                    <!-- Page Heading - Updated to match marinedetail.php style -->
                    <div class="mb-8">
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Endorse Marine Request
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">
                            Detailed information for BOD No: <span class="bod-number-highlight"><?php echo htmlspecialchars($request['bod_no'] ?: 'N/A'); ?></span>
                        </p>
                        <div class="mt-2 flex items-center gap-4 flex-wrap">
                            <!-- Larger status pill from marinedetail.php -->
                            <span class="status-pill status-pill-lg status-pending-endorsement">
                                PENDING ENDORSEMENT
                            </span>
                        </div>
                    </div>

                    <!-- Details Card -->
                    <div class="w-full bg-white rounded-xl shadow-sm p-6 md:p-8 mb-8">
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
<!-- Section 1: Request Information - Card with Border Shadow -->
<section class="marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Request Information</h3>
        <span class="text-xs font-medium text-gray-500 bg-gradient-to-r from-blue-100 to-indigo-100 px-3 py-1 rounded-full border border-blue-200 shadow-sm">
            Details
        </span>
    </div>
    
    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 min-h-[340px] flex flex-col">
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Log No</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['log_no'] ?: 'N/A'); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1"><span class="font-bold">BOD No</span></span>
                <span class="text-sm font-bold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['bod_no'] ?: 'N/A'); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Company</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['requester_company'] ?: ($request['company'] ?: 'N/A')); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Vessel Name</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['vessel_name']); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">PO Number</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['po_number'] ?: 'N/A'); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Location</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">Kuala Terengganu</span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Assigned Agent</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['agent_name'] ?: ($request['agent_username'] ?: 'Not Assigned')); ?></span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Request By</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['requester_name'] ?: 'N/A'); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Schedule Information - Card with Border Shadow -->
<section class="marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Schedule Information</h3>
        <span class="text-xs font-medium text-gray-500 bg-gradient-to-r from-emerald-100 to-teal-100 px-3 py-1 rounded-full border border-emerald-200 shadow-sm">
            Timeline
        </span>
    </div>
    
    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200 min-h-[340px] flex flex-col">
        <div class="space-y-4">
            <?php
            $etaObj = $request['eta'] ? new DateTime($request['eta']) : null;
            $etdObj = $request['etd'] ? new DateTime($request['etd']) : null;
            $actualEtaObj = $request['actual_eta'] ? new DateTime($request['actual_eta']) : null;
            $actualEtdObj = $request['actual_etd'] ? new DateTime($request['actual_etd']) : null;
            ?>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Est. Arrival (ETA)</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">
                    <?php echo $etaObj ? $etaObj->format('d/m/Y H:i') : 'N/A'; ?>
                </span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Est. Departure (ETD)</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">
                    <?php echo $etdObj ? $etdObj->format('d/m/Y H:i') : 'N/A'; ?>
                </span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Actual Arrival (ETA)</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">
                    <?php echo $actualEtaObj ? $actualEtaObj->format('d/m/Y H:i') : 'N/A'; ?>
                </span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Actual Departure (ETD)</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">
                    <?php echo $actualEtdObj ? $actualEtdObj->format('d/m/Y H:i') : 'N/A'; ?>
                </span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Created At</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">
                    <?php echo $request['created_at'] ? date('d/m/Y H:i', strtotime($request['created_at'])) : 'N/A'; ?>
                </span>
            </div>
            <div class="grid grid-cols-3 gap-4 items-center">
                <span class="text-sm text-gray-600 col-span-1">Updated At</span>
                <span class="text-sm font-semibold text-gray-900 col-span-2">
                    <?php echo $request['updated_at'] ? date('d/m/Y H:i', strtotime($request['updated_at'])) : 'N/A'; ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Berth Remarks - Card with Border Shadow -->
<div class="md:col-span-2 marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Berth Remarks</h3>
        <span class="text-xs font-medium text-gray-500 bg-gradient-to-r from-gray-100 to-slate-100 px-3 py-1 rounded-full border border-gray-200 shadow-sm">
            Notes
        </span>
    </div>
    
    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
        <p class="text-gray-700"><?php echo htmlspecialchars($request['remarks'] ?: 'No remarks'); ?></p>
    </div>
</div>

<!-- Section 4: Crew Transfer Details - Modern Table with Shadow -->
<?php if (!empty($crewData)): ?>
<section class="md:col-span-2 marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Crew Transfer Details</h3>
        <div class="flex items-center gap-3">
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                Total: <?php echo count($crewData); ?> crew
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $request['crew_transfer_type'] === 'sign_on' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'; ?>">
                <?php echo htmlspecialchars($request['crew_transfer_type'] === 'sign_on' ? 'SIGN ON' : 'SIGN OFF'); ?>
            </span>
        </div>
    </div>
    
    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px]">
                <thead class="bg-gradient-to-r from-blue-100 to-indigo-100 border-b-2 border-blue-200">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No.</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">IC/Passport</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nationality</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Mobile</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Company</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Destination</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Passport Expiry</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Attendance</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No Show Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white/90">
                    <?php 
                    $counter = 1;
                    $show_count = 0;
                    $no_show_count = 0;
                    foreach ($crewData as $crew): 
                        // Count attendance for summary
                        if (isset($crew['attendance_status'])) {
                            if ($crew['attendance_status'] === 'show') {
                                $show_count++;
                            } elseif ($crew['attendance_status'] === 'no_show') {
                                $no_show_count++;
                            }
                        }
                        
                        // Alternate row colors
                        $rowBgClass = $counter % 2 === 0 ? 'bg-blue-50/20' : 'bg-white';
                    ?>
                        <tr class="<?php echo $rowBgClass; ?> hover:bg-blue-100/30 transition-colors duration-200">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo $counter++; ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        <?php
                                        if ($crew['name']) {
                                            $patronymics = ['bin', 'binti', 'ibn', 'ibni', 'bt', 'anak', 'a/l', 'a/p', 's/o', 'd/o'];
                                            $nameParts = array_filter(explode(' ', $crew['name']), function ($part) use ($patronymics) {
                                                return !in_array(strtolower($part), $patronymics);
                                            });
                                            if (count($nameParts) > 2) {
                                                $displayName = implode(' ', array_slice($nameParts, 1, 2));
                                            } else {
                                                $displayName = implode(' ', array_slice($nameParts, 0, 2));
                                            }
                                            echo htmlspecialchars($displayName);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($crew['passport_ic'] ?: '—'); ?></td>
                            <td class="px-4 py-3">
                                <?php if ($crew['nationality']): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="material-symbols-outlined text-xs">flag</span>
                                        <?php echo htmlspecialchars($crew['nationality']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($crew['mobile'] ?: '—'); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($crew['company'] ?: '—'); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($crew['destination'] ?: '—'); ?></td>
                            <td class="px-4 py-3">
                                <?php
                                if (isset($crew['expiry']) && $crew['expiry']):
                                    $expiryDate = new DateTime($crew['expiry']);
                                    $now = new DateTime();
                                    $isExpired = $expiryDate < $now;
                                ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium <?php echo $isExpired ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200'; ?> border">
                                        <span class="material-symbols-outlined text-xs"><?php echo $isExpired ? 'warning' : 'check_circle'; ?></span>
                                        <?php echo htmlspecialchars($expiryDate->format('d/m/Y')); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (isset($crew['attendance_status'])): ?>
                                    <?php if ($crew['attendance_status'] === 'show'): ?>
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300 shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-green-600 animate-pulse"></span>
                                            SHOW
                                        </span>
                                    <?php elseif ($crew['attendance_status'] === 'no_show'): ?>
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300 shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                                            NO SHOW
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">—</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (isset($crew['attendance_status']) && $crew['attendance_status'] === 'no_show' && !empty($crew['no_show_remarks'])): ?>
                                    <div class="relative group">
                                        <div class="flex items-center gap-1 bg-gray-50 rounded-lg p-1.5 border border-gray-200 max-w-xs">
                                            <span class="material-symbols-outlined text-xs text-gray-500">info</span>
                                            <span class="text-xs text-gray-700 truncate">
                                                <?php echo htmlspecialchars(substr($crew['no_show_remarks'], 0, 30)) . (strlen($crew['no_show_remarks']) > 30 ? '...' : ''); ?>
                                            </span>
                                        </div>
                                        <?php if (strlen($crew['no_show_remarks']) > 30): ?>
                                            <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-20">
                                                <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 max-w-md whitespace-normal shadow-xl">
                                                    <?php echo htmlspecialchars($crew['no_show_remarks']); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif (isset($crew['attendance_status']) && $crew['attendance_status'] === 'no_show'): ?>
                                    <span class="text-xs text-gray-400 italic">No remarks</span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Attendance Summary with Modern Design -->
        <?php if ($show_count > 0 || $no_show_count > 0): ?>
        <div class="mt-4 p-4 bg-gradient-to-r from-gray-50 to-white border-t border-gray-200 flex gap-6 items-center">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Attendance Summary</span>
            <div class="flex gap-4">
                <div class="flex items-center gap-2 bg-green-50 px-3 py-1.5 rounded-full border border-green-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-600 animate-pulse"></span>
                    <span class="text-sm font-medium text-green-800">Show: <span class="font-bold"><?php echo $show_count; ?></span></span>
                </div>
                <div class="flex items-center gap-2 bg-red-50 px-3 py-1.5 rounded-full border border-red-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                    <span class="text-sm font-medium text-red-800">No Show: <span class="font-bold"><?php echo $no_show_count; ?></span></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Section 5: Other Services Details - Modern Table with Shadow -->
<?php if (!empty($otherServices)): ?>
<section class="md:col-span-2 marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Other Services</h3>
        <span class="text-xs font-medium text-gray-500 bg-gradient-to-r from-amber-100 to-orange-100 px-3 py-1 rounded-full border border-amber-200 shadow-sm">
            <?php echo count($otherServices); ?> item<?php echo count($otherServices) > 1 ? 's' : ''; ?>
        </span>
    </div>
    
    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 bg-gradient-to-r from-amber-50 to-orange-50">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-amber-100 to-orange-100 border-b-2 border-amber-200">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-16">No.</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Service</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Quantity</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white/90">
                <?php foreach ($otherServices as $index => $service): ?>
                    <?php
                    $serviceName = str_replace('_', ' ', $service['service_type']);
                    $serviceName = ucwords(strtolower($serviceName));
                    
                    // Alternate row colors
                    $rowBgClass = $index % 2 === 0 ? 'bg-amber-50/20' : 'bg-white';
                    
                    // Icon based on specific service types
                    $icon = 'miscellaneous_services';
                    $iconBgColor = 'bg-amber-100';
                    $iconColor = 'text-amber-600';
                    
                    // Map specific services to appropriate icons
                    $serviceLower = strtolower($service['service_type']);
                    
                    if (strpos($serviceLower, 'packed meals') !== false || strpos($serviceLower, 'packed_meals') !== false) {
                        $icon = 'lunch_dining';
                        $iconBgColor = 'bg-green-100';
                        $iconColor = 'text-green-600';
                    }
                    else if (strpos($serviceLower, 'snack pack') !== false || strpos($serviceLower, 'snack_pack') !== false) {
                        $icon = 'bakery_dining';
                        $iconBgColor = 'bg-yellow-100';
                        $iconColor = 'text-yellow-600';
                    }
                    else if (strpos($serviceLower, 'baggage handling') !== false || strpos($serviceLower, 'baggage_handling') !== false) {
                        $icon = 'luggage';
                        $iconBgColor = 'bg-blue-100';
                        $iconColor = 'text-blue-600';
                    }
                    else if (strpos($serviceLower, 'bag tagging') !== false || strpos($serviceLower, 'bag_tagging') !== false) {
                        $icon = 'confirmation_number';
                        $iconBgColor = 'bg-purple-100';
                        $iconColor = 'text-purple-600';
                    }
                    else if (strpos($serviceLower, 'takeaway') !== false || strpos($serviceLower, 'take_away') !== false) {
                        $icon = 'takeout_dining';
                        $iconBgColor = 'bg-red-100';
                        $iconColor = 'text-red-600';
                    }
                    else if (strpos($serviceLower, 'baggage') !== false && strpos($serviceLower, 'handling') === false) {
                        $icon = 'baggage_claim';
                        $iconBgColor = 'bg-indigo-100';
                        $iconColor = 'text-indigo-600';
                    }
                    ?>
                    <tr class="<?php echo $rowBgClass; ?> hover:bg-amber-100/30 transition-colors duration-200">
                        <td class="px-5 py-4 text-sm font-medium text-gray-500"><?php echo $index + 1; ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg <?php echo $iconBgColor; ?> flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm <?php echo $iconColor; ?>">
                                        <?php echo $icon; ?>
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($serviceName); ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($service['quantity']): ?>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($service['quantity']); ?></span>
                                    <span class="text-xs text-gray-500">pcs</span>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($service['details']): ?>
                                <div class="relative group">
                                    <p class="text-sm text-gray-700 bg-white rounded-lg p-2 border border-gray-200 shadow-sm max-w-md">
                                        <?php 
                                        $details = htmlspecialchars($service['details']);
                                        echo strlen($details) > 80 ? substr($details, 0, 80) . '...' : $details;
                                        ?>
                                    </p>
                                    <?php if (strlen($service['details']) > 80): ?>
                                        <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-20">
                                            <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 max-w-md whitespace-normal shadow-xl">
                                                <?php echo htmlspecialchars($service['details']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-400 italic">No details</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<!-- Section 6: Fuel & Water Supply - Bright Table Design -->
<?php if (!empty($fuelWaterServices)): ?>
<section class="md:col-span-2 marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Fuel & Water Supply</h3>
        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
            <?php echo count($fuelWaterServices); ?> item<?php echo count($fuelWaterServices) > 1 ? 's' : ''; ?>
        </span>
    </div>
    
    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 bg-gradient-to-r from-white to-gray-50">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-amber-50 to-sky-50 border-b-2 border-amber-200">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-16">No.</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Service Type</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Quantity (Liters)</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Booking Time</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($fuelWaterServices as $index => $service): ?>
                    <?php 
                    $type = $service['service_type'];
                    $rowBgClass = $type === 'fuel' ? 'hover:bg-amber-50/30' : 'hover:bg-sky-50/30';
                    $iconColor = $type === 'fuel' ? 'text-amber-600' : 'text-sky-600';
                    $icon = $type === 'fuel' ? 'local_gas_station' : 'water_drop';
                    ?>
                    <tr class="<?php echo $rowBgClass; ?> transition-colors duration-200">
                        <td class="px-5 py-4 text-sm font-medium text-gray-500"><?php echo $index + 1; ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg <?php echo $type === 'fuel' ? 'bg-amber-100' : 'bg-sky-100'; ?> flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm <?php echo $iconColor; ?>">
                                        <?php echo $icon; ?>
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 capitalize"><?php echo htmlspecialchars($type); ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($service['quantity'] ?: '0'); ?></span>
                                <span class="text-xs text-gray-500">L</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($service['booking_time']): ?>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <span class="material-symbols-outlined text-xs">schedule</span>
                                    <?php echo substr($service['booking_time'], 0, 5); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($service['remarks']): ?>
                                <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-2 border border-gray-100 max-w-xs">
                                    <?php echo htmlspecialchars($service['remarks']); ?>
                                </p>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<!-- Section 7: General Works - Bright Table Design -->
<?php if (!empty($generalWorks)): ?>
<section class="md:col-span-2 marine-detail-section">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">General Works</h3>
        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
            <?php echo count($generalWorks); ?> item<?php echo count($generalWorks) > 1 ? 's' : ''; ?>
        </span>
    </div>
    
    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 bg-gradient-to-r from-indigo-50 to-purple-50">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-indigo-100 to-purple-100 border-b-2 border-indigo-200">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-16">No.</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Work Type</th>
                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white/80">
                <?php foreach ($generalWorks as $index => $work): ?>
                    <?php
                    $workType = str_replace('_', ' ', $work['work_type']);
                    $workType = ucwords(strtolower($workType));
                    
                    // Alternate row colors for better readability
                    $rowBgClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                    
                    // Icon based on work type
                    $icon = 'construction';
                    if (strpos($work['work_type'], 'electrical') !== false) $icon = 'bolt';
                    else if (strpos($work['work_type'], 'mechanical') !== false) $icon = 'settings';
                    else if (strpos($work['work_type'], 'cleaning') !== false) $icon = 'cleaning_services';
                    else if (strpos($work['work_type'], 'painting') !== false) $icon = 'format_paint';
                    else if (strpos($work['work_type'], 'welding') !== false) $icon = 'precision_manufacturing';
                    else if (strpos($work['work_type'], 'inspection') !== false) $icon = 'search';
                    ?>
                    <tr class="<?php echo $rowBgClass; ?> hover:bg-indigo-100/30 transition-colors duration-200">
                        <td class="px-5 py-4 text-sm font-medium text-gray-500"><?php echo $index + 1; ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm text-indigo-600">
                                        <?php echo $icon; ?>
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($workType); ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <?php if ($work['remarks']): ?>
                                <div class="relative group">
                                    <p class="text-sm text-gray-700 bg-white rounded-lg p-2 border border-gray-200 shadow-sm max-w-lg">
                                        <?php 
                                        $remarks = htmlspecialchars($work['remarks']);
                                        echo strlen($remarks) > 100 ? substr($remarks, 0, 100) . '...' : $remarks;
                                        ?>
                                    </p>
                                    <?php if (strlen($work['remarks']) > 100): ?>
                                        <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10">
                                            <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 max-w-md whitespace-normal shadow-xl">
                                                <?php echo htmlspecialchars($work['remarks']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-400 italic">No remarks</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

                        </div>
                    </div>

                    <!-- File Upload Section - PRESERVED original -->
                    <div class="mt-8 grid md:grid-cols-2 gap-6">
                        <!-- Upload File Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Supporting Documents</h3>
                            <div class="space-y-4">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg text-center hover:bg-gray-50 transition-colors cursor-pointer"
                                    id="drop-zone">
                                    <input type="file" id="file-upload" class="hidden"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" multiple>
                                    <label for="file-upload" class="cursor-pointer block w-full h-full p-6">
                                        <span
                                            class="material-symbols-outlined text-4xl text-gray-400 mb-2">cloud_upload</span>
                                        <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, Images, Office Docs (Max 10MB)</p>
                                    </label>
                                </div>

                                <!-- Selected Files Display -->
                                <div id="selected-files-container" class="hidden space-y-2">
                                    <!-- Files will be added here dynamically -->
                                </div>

                                <button id="submit-file-btn"
                                    class="w-full bg-[#212180] text-white rounded-lg py-2 px-4 text-sm font-medium hover:bg-[#212180]/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    disabled>
                                    Submit File
                                </button>
                            </div>
                        </div>

                        <!-- Uploaded Files Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Documents</h3>
                            <div id="uploaded-files-list" class="space-y-3 max-h-[300px] overflow-y-auto">
                                <!-- Files will be populated here -->
                                <div class="text-center text-gray-500 py-8">
                                    <p class="text-sm">Loading files...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Endorsement Buttons - Modern & Kemas -->
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-end items-center">
                        <!-- Left side - optional info text (boleh tambah jika nak) -->
                        <div class="flex-1 text-sm text-gray-500">
                            <span class="material-symbols-outlined text-base align-middle mr-1">info</span>
                            Please review all details before endorsing
                        </div>
                        
                        <!-- Right side - buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- Back to List Button - Light variant -->
                            <a href="endorsements.php" 
                            class="group inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white text-gray-700 rounded-xl border border-gray-300 font-medium text-sm hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md">
                                <span class="material-symbols-outlined text-base group-hover:-translate-x-0.5 transition-transform duration-200">arrow_back</span>
                                BACK TO LIST
                            </a>
                            
                            <!-- Request Amendment Button - Modern Red Gradient -->
                            <button type="button" id="request-amendment-btn"
                                    class="group inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl font-medium text-sm hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <span class="material-symbols-outlined text-base">edit_note</span>
                                REQUEST AMENDMENT
                                <span class="material-symbols-outlined text-base opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200">chevron_right</span>
                            </button>
                            
                            <!-- Endorse Button - Modern Green Gradient with Pulse Effect -->
                            <button type="button" id="endorse-btn" 
                                    class="group inline-flex items-center justify-center gap-2 px-8 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-medium text-sm hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 relative overflow-hidden">
                                <!-- Pulse effect ring -->
                                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 rounded-xl transition-opacity duration-300"></div>
                                <!-- Animated ring -->
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-green-400 to-emerald-400 rounded-xl opacity-0 group-hover:opacity-30 blur transition-opacity duration-300"></div>
                                
                                <span class="material-symbols-outlined text-base relative z-10">check_circle</span>
                                <span class="relative z-10">ENDORSE</span>
                                <span class="material-symbols-outlined text-base relative z-10 group-hover:translate-x-1 transition-transform duration-200">east</span>
                            </button>
                        </div>
                    </div>

                    <!-- Optional: Add subtle separator line above buttons -->
                    <div class="mt-8 mb-2 border-t border-gray-200"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- Endorse Confirmation Modal -->
    <div id="endorse-modal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Confirm Endorsement</h3>
            <p class="modal-message">Are you sure you want to endorse this marine request? This action cannot be undone.</p>
            <form method="POST" id="endorse-form">
                <input type="hidden" name="action" value="endorse">
                <div class="modal-buttons">
                    <button type="submit" class="btn-confirm">Yes, Endorse</button>
                    <button type="button" id="cancel-endorse-btn" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Amendment Modal -->
    <div id="amendment-modal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Request Amendment</h3>
            <p class="modal-message">Please provide details about what needs to be amended:</p>
            <form method="POST" id="amendment-form">
                <input type="hidden" name="action" value="request_amendment">
                <div class="mb-4">
                    <textarea name="amendment_notes" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Enter amendment details..." required></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-confirm">Submit Amendment Request</button>
                    <button type="button" id="cancel-amendment-btn" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Confirmation Modal -->
    <div id="upload-confirmation-modal" class="fixed inset-0 z-50 hidden overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-red-600">warning</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirm Upload</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to upload this file? You cannot delete it once submitted.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirm-upload-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">Submit</button>
                    <button type="button" id="cancel-upload-btn"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Success Modal -->
    <div id="upload-success-modal" class="fixed inset-0 z-50 hidden overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <!-- Wrapper Div (Green Circle Background) -->
                        <div class="cool-mode-wrapper mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- SVG Icon -->
                            <svg class="cool-mode-icon w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Upload Successful</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">File uploaded successfully.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="success-modal-close-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Time update
            function updateTime() {
                const date = new Date();
                document.getElementById('date').innerText = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase();
                document.getElementById('time').innerText = date.toLocaleTimeString('en-US', { hour12: false });
            }
            setInterval(updateTime, 1000);
            updateTime();

            // Modal elements
            const endorseModal = document.getElementById('endorse-modal');
            const amendmentModal = document.getElementById('amendment-modal');
            const endorseBtn = document.getElementById('endorse-btn');
            const requestAmendmentBtn = document.getElementById('request-amendment-btn');
            const cancelEndorseBtn = document.getElementById('cancel-endorse-btn');
            const cancelAmendmentBtn = document.getElementById('cancel-amendment-btn');

            // File upload elements
            const fileInput = document.getElementById('file-upload');
            const dropZone = document.getElementById('drop-zone');
            const selectedFilesContainer = document.getElementById('selected-files-container');
            const submitFileBtn = document.getElementById('submit-file-btn');
            const uploadedFilesList = document.getElementById('uploaded-files-list');

            const requestId = '<?php echo $id; ?>';
            const requestType = 'marine';
            let selectedFiles = [];

            const uploadModal = document.getElementById('upload-confirmation-modal');
            const confirmUploadBtn = document.getElementById('confirm-upload-btn');
            const cancelUploadBtn = document.getElementById('cancel-upload-btn');

            const successModal = document.getElementById('upload-success-modal');
            const successModalCloseBtn = document.getElementById('success-modal-close-btn');

            // Load existing files
            loadUploadedFiles();

            // Open Endorse Modal
            endorseBtn.addEventListener('click', () => {
                endorseModal.style.display = 'flex';
            });

            // Open Amendment Modal
            requestAmendmentBtn.addEventListener('click', () => {
                amendmentModal.style.display = 'flex';
            });

            // Close Endorse Modal
            cancelEndorseBtn.addEventListener('click', () => {
                endorseModal.style.display = 'none';
            });

            // Close Amendment Modal
            cancelAmendmentBtn.addEventListener('click', () => {
                amendmentModal.style.display = 'none';
            });

            // Close modals when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === endorseModal) {
                    endorseModal.style.display = 'none';
                }
                if (event.target === amendmentModal) {
                    amendmentModal.style.display = 'none';
                }
            });

            // File Selection
            fileInput.addEventListener('change', handleFileSelect);

            // Drag and Drop
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            });

            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                if (e.dataTransfer.files.length) {
                    handleFiles(e.dataTransfer.files);
                }
            });

            function handleFileSelect(e) {
                if (e.target.files.length) {
                    handleFiles(e.target.files);
                }
            }

            function handleFiles(files) {
                const newFiles = Array.from(files);
                const validFiles = [];

                newFiles.forEach(file => {
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`File ${file.name} is too large. Max 10MB.`);
                    } else {
                        // Check for duplicates in currently selected files
                        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                            validFiles.push(file);
                        }
                    }
                });

                selectedFiles = [...selectedFiles, ...validFiles];
                renderSelectedFiles();
                updateSubmitButton();

                // Reset input so same file can be selected again if removed
                fileInput.value = '';
            }

            function renderSelectedFiles() {
                if (selectedFiles.length === 0) {
                    selectedFilesContainer.classList.add('hidden');
                    selectedFilesContainer.innerHTML = '';
                    return;
                }

                selectedFilesContainer.classList.remove('hidden');
                selectedFilesContainer.innerHTML = selectedFiles.map((file, index) => `
                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <span class="material-symbols-outlined text-gray-500">description</span>
                        <span class="text-sm text-gray-700 truncate">${file.name}</span>
                        <span class="text-xs text-gray-400">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                    </div>
                    <button type="button" class="remove-file-btn text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50" data-index="${index}">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
            `).join('');

                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-file-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const index = parseInt(this.dataset.index);
                        removeFile(index);
                    });
                });
            }

            function removeFile(index) {
                selectedFiles.splice(index, 1);
                renderSelectedFiles();
                updateSubmitButton();
            }

            function updateSubmitButton() {
                submitFileBtn.disabled = selectedFiles.length === 0;
            }

            // Submit File Button Click
            submitFileBtn.addEventListener('click', () => {
                if (selectedFiles.length === 0) return;
                uploadModal.classList.remove('hidden');
            });

            // Modal Confirm Button
            confirmUploadBtn.addEventListener('click', () => {
                uploadModal.classList.add('hidden');
                uploadFiles();
            });

            // Modal Cancel Button
            cancelUploadBtn.addEventListener('click', () => {
                uploadModal.classList.add('hidden');
            });

            // Success Modal Close Button
            successModalCloseBtn.addEventListener('click', () => {
                successModal.classList.add('hidden');
            });

            // Actual Upload Function
            async function uploadFiles() {
                submitFileBtn.disabled = true;
                submitFileBtn.textContent = 'Uploading...';

                let successCount = 0;
                let failCount = 0;
                let errors = [];

                for (const file of selectedFiles) {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('request_id', requestId);
                    formData.append('request_type', requestType);

                    try {
                        const response = await fetch('api/upload_file.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        if (response.ok) {
                            successCount++;
                        } else {
                            failCount++;
                            errors.push(`${file.name}: ${data.error || 'Upload failed'}`);
                        }
                    } catch (error) {
                        failCount++;
                        errors.push(`${file.name}: ${error.message}`);
                    }
                }

                // Reset UI
                selectedFiles = [];
                renderSelectedFiles();
                updateSubmitButton();
                submitFileBtn.textContent = 'Submit File';
                loadUploadedFiles();

                if (failCount === 0) {
                    // All success
                    successModal.classList.remove('hidden');
                } else {
                    // Some failed
                    let message = `Uploaded ${successCount} files successfully.\nFailed to upload ${failCount} files:\n${errors.join('\n')}`;
                    alert(message);
                }
            }

            // Load Uploaded Files
            async function loadUploadedFiles() {
                try {
                    const response = await fetch(`api/get_uploaded_files.php?request_id=${requestId}&request_type=${requestType}`);
                    const data = await response.json();

                    if (data.success) {
                        renderFiles(data.documents);
                    }
                } catch (error) {
                    console.error('Error loading files:', error);
                    uploadedFilesList.innerHTML = '<p class="text-sm text-red-500 text-center">Failed to load files</p>';
                }
            }

            function renderFiles(files) {
                if (files.length === 0) {
                    uploadedFilesList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No files uploaded yet</p>';
                    return;
                }

                uploadedFilesList.innerHTML = files.map(file => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="bg-white p-2 rounded shadow-sm">
                            <span class="material-symbols-outlined text-blue-600 text-xl">description</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" title="${file.file_name}">${file.file_name}</p>
                            <p class="text-xs text-gray-500">By ${file.uploader_name} • ${new Date(file.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                    <a href="${file.file_path}" target="_blank" class="text-gray-400 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors" title="Download">
                        <span class="material-symbols-outlined">download</span>
                    </a>
                </div>
            `).join('');
            }

            // Profile dropdown functionality
            const profileBtn = document.getElementById('profile-dropdown-btn');
            const profileDropdown = document.getElementById('profile-dropdown');

            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        profileDropdown.classList.add('hidden');
                    }
                });
            }

            // Sidebar toggle states and scroll position saving
            const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

            function loadToggleStates() {
                toggles.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const state = localStorage.getItem(id);
                        if (state !== null) {
                            element.checked = state === 'true';
                        }
                    }
                });

                // Force history toggle to be checked and expanded for detail pages
                const historyToggle = document.getElementById('history-toggle');
                const historySubmenu = document.getElementById('history-submenu');
                const historyIcon = document.querySelector('.expand-icon-history-toggle');
                if (historyToggle && historySubmenu && historyIcon) {
                    historyToggle.checked = true;
                    historySubmenu.classList.remove('hidden');
                    historyIcon.classList.add('rotate-180');
                }
            }

            function saveToggleState(id) {
                const checkbox = document.getElementById(id);
                localStorage.setItem(id, checkbox.checked);
            }

            loadToggleStates();
            toggles.forEach(id => {
                const toggle = document.getElementById(id);
                if (toggle) {
                    toggle.addEventListener('change', () => saveToggleState(id));
                }
            });

            // Toggle submenu and icon
            function setupToggle(toggleId, submenuId, iconClass) {
                const toggle = document.getElementById(toggleId);
                const submenu = document.getElementById(submenuId);
                const icon = document.querySelector('.' + iconClass);
                if (toggle && submenu && icon) {
                    const updateToggle = () => {
                        if (toggle.checked) {
                            submenu.classList.remove('hidden');
                            icon.classList.add('rotate-180');
                        } else {
                            submenu.classList.add('hidden');
                            icon.classList.remove('rotate-180');
                        }
                    };
                    toggle.addEventListener('change', updateToggle);
                    updateToggle(); // initial
                }
            }

            setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
            setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
            setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

            // Save and restore sidebar scroll position
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                const scrollTop = localStorage.getItem('sidebarScrollTop');
                if (scrollTop) {
                    sidebar.scrollTop = parseInt(scrollTop);
                }
            }

            window.addEventListener('beforeunload', function () {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
                }
            });
        });
    </script>
</body>

</html>