<?php
require_once __DIR__ . '/../../../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Set current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Get marine_id from URL
$marine_id = $_GET['marine_id'] ?? '';

// Fetch marine request details
$marine_request = null;
$crew_members = [];
$other_services = [];
$fuel_water_services = [];
$general_works = [];

if ($marine_id) {
    // Fetch main request
    $stmt = $conn->prepare("
        SELECT mr.*, 
               u.company_name as user_company,
               u.username as requester_name,
               a.full_name as agent_name,
               v.vessel_name,
               c.company_name
        FROM marine_requests mr
        LEFT JOIN users u ON mr.user_id = u.user_id
        LEFT JOIN agents a ON mr.assigned_agent_id = a.agent_id
        LEFT JOIN vessels v ON mr.vessel_name = v.vessel_name
        LEFT JOIN companies c ON mr.company = c.company_name
        WHERE mr.marine_id = ?
    ");
    $stmt->execute([$marine_id]);
    $marine_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($marine_request) {
        // Fetch crew members
        $stmt = $conn->prepare("SELECT * FROM marine_crew_details WHERE marine_id = ? ORDER BY id");
        $stmt->execute([$marine_id]);
        $crew_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch other services
        $stmt = $conn->prepare("SELECT * FROM marine_other_services WHERE marine_id = ?");
        $stmt->execute([$marine_id]);
        $other_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch fuel & water services
        $stmt = $conn->prepare("SELECT * FROM marine_fuel_water_services WHERE marine_id = ?");
        $stmt->execute([$marine_id]);
        $fuel_water_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch general works
        $stmt = $conn->prepare("SELECT * FROM marine_general_works WHERE marine_id = ?");
        $stmt->execute([$marine_id]);
        $general_works = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch dropdown data
$agents = $conn->query("SELECT agent_id, full_name FROM agents WHERE status = 'active' ORDER BY full_name")->fetchAll();
$companies = $conn->query("SELECT company_name FROM companies ORDER BY company_name")->fetchAll();
$vessels = $conn->query("SELECT vessel_name FROM vessels ORDER BY vessel_name")->fetchAll();
$nationalities = $conn->query("SELECT name FROM nationalities ORDER BY name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save' || $action === 'complete') {
        try {
            $conn->beginTransaction();
            
            // VALIDATION RULES BASED ON ACTION
            $validationErrors = [];
            
            // For COMPLETE action
            if ($action === 'complete') {
                // Rule 1: Status must be 'assign' or 'in progress'
                $currentStatus = strtolower($marine_request['status']);
                if (!in_array($currentStatus, ['assign', 'assigned', 'in progress'])) {
                    $validationErrors[] = "Request must be in 'ASSIGNED' or 'IN PROGRESS' status before completion.";
                }
                
                // Rule 2: Log No must be filled (MANDATORY for completion)
                if (empty($_POST['log_no'])) {
                    $validationErrors[] = "Log number is REQUIRED before completion.";
                }
                
                // Rule 3: Actual ETA and ETD must be filled
                if (empty($_POST['actual_eta'])) {
                    $validationErrors[] = "Actual ETA is required before completion.";
                }
                if (empty($_POST['actual_etd'])) {
                    $validationErrors[] = "Actual ETD is required before completion.";
                }
            }
            
            // If validation errors, show them and stop processing
            if (!empty($validationErrors)) {
                throw new Exception(implode("<br>", $validationErrors));
            }
            
            // Update main request
            $updateData = [
                'assigned_agent_id' => $_POST['assigned_agent'] ?? null,
                'log_no' => $_POST['log_no'] ?? null,
                'company' => $_POST['company'] ?? null,
                'vessel_name' => $_POST['vessel_name'] ?? null,
                'po_number' => $_POST['po_number'] ?? null,
                'eta' => $_POST['eta'] ?? null,
                'etd' => $_POST['etd'] ?? null,
                'actual_eta' => $_POST['actual_eta'] ?? null,
                'actual_etd' => $_POST['actual_etd'] ?? null,
                'remarks' => $_POST['remarks'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Set status based on action
            if ($action === 'complete' && !empty($_POST['log_no'])) {
                $updateData['status'] = 'pending endorsement';
            } elseif ($action === 'save') {
                // NEW LOGIC: If current status is 'pending' or 'assign', auto change to 'in progress'
                $currentStatus = strtolower($marine_request['status']);
                if ($currentStatus === 'pending' || $currentStatus === 'assign' || $currentStatus === 'assigned') {
                    $updateData['status'] = 'in progress';
                } else {
                    $updateData['status'] = $marine_request['status']; // Keep existing status
                }
            }
            
            $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($updateData)));
            $stmt = $conn->prepare("UPDATE marine_requests SET $setClause WHERE marine_id = :marine_id");
            $updateData['marine_id'] = $marine_id;
            $stmt->execute($updateData);
            
            // Handle crew members with auto-capitalization
            if (isset($_POST['crew'])) {
                // Delete existing crew members
                $stmt = $conn->prepare("DELETE FROM marine_crew_details WHERE marine_id = ?");
                $stmt->execute([$marine_id]);
                
                // Insert new crew members
                foreach ($_POST['crew'] as $crew) {
                    if (!empty($crew['name'])) {
                        // Auto-capitalize all text fields
                        $name = strtoupper($crew['name'] ?? '');
                        $passport_ic = strtoupper($crew['passport_ic'] ?? '');
                        $nationality = strtoupper($crew['nationality'] ?? '');
                        $mobile = strtoupper($crew['mobile'] ?? '');
                        $company = strtoupper($crew['company'] ?? '');
                        $destination = strtoupper($crew['destination'] ?? '');
                        
                        $stmt = $conn->prepare("
                            INSERT INTO marine_crew_details 
                            (marine_id, name, passport_ic, nationality, expiry, mobile, company, destination, attendance_status, no_show_remarks) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $marine_id,
                            $name,
                            $passport_ic,
                            $nationality,
                            $crew['expiry'] ?? null,
                            $mobile,
                            $company,
                            $destination,
                            $crew['attendance_status'] ?? null,
                            $crew['no_show_remarks'] ?? null
                        ]);
                    }
                }
            }
            
            // Handle other services
            if (isset($_POST['other_services'])) {
                $stmt = $conn->prepare("DELETE FROM marine_other_services WHERE marine_id = ?");
                $stmt->execute([$marine_id]);
                
                foreach ($_POST['other_services'] as $service) {
                    if (!empty($service['service_type'])) {
                        $stmt = $conn->prepare("
                            INSERT INTO marine_other_services 
                            (marine_id, service_type, quantity, details) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $marine_id,
                            $service['service_type'],
                            $service['quantity'] ?? 0,
                            strtoupper($service['details'] ?? '')
                        ]);
                    }
                }
            }
            
            // Handle fuel & water
            if (isset($_POST['fuel_water'])) {
                $stmt = $conn->prepare("DELETE FROM marine_fuel_water_services WHERE marine_id = ?");
                $stmt->execute([$marine_id]);
                
                foreach ($_POST['fuel_water'] as $service) {
                    if (!empty($service['service_type'])) {
                        // Check if this is a completion action
                        $isCompletion = ($action === 'complete');
                        
                        // Determine actual quantity and actual booking time
                        // If actual fields are empty, use the booking values
                        $actualQuantity = !empty($service['actual_quantity']) ? $service['actual_quantity'] : 
                                         ($isCompletion ? ($service['quantity'] ?? 0) : null);
                        
                        $actualBookingTime = !empty($service['actual_booking_time']) ? $service['actual_booking_time'] : 
                                            ($isCompletion ? ($service['booking_time'] ?? null) : null);
                        
                        $stmt = $conn->prepare("
                            INSERT INTO marine_fuel_water_services 
                            (marine_id, service_type, quantity, booking_time, remarks, actual_quantity, actual_booking_time) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $marine_id,
                            $service['service_type'],
                            $service['quantity'] ?? 0,
                            $service['booking_time'] ?? null,
                            strtoupper($service['remarks'] ?? ''),
                            $actualQuantity,
                            $actualBookingTime
                        ]);
                    }
                }
            }       
            
            // Handle general works
            if (isset($_POST['general_works'])) {
                $stmt = $conn->prepare("DELETE FROM marine_general_works WHERE marine_id = ?");
                $stmt->execute([$marine_id]);
                
                foreach ($_POST['general_works'] as $work) {
                    if (!empty($work['work_type'])) {
                        $stmt = $conn->prepare("
                            INSERT INTO marine_general_works 
                            (marine_id, work_type, remarks) 
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([
                            $marine_id,
                            $work['work_type'],
                            strtoupper($work['remarks'] ?? '')
                        ]);
                    }
                }
            }
            
            $conn->commit();
            
            // Redirect with appropriate message
            $message = "";
            switch($action) {
                case 'complete':
                    $message = "Request completed successfully!";
                    break;
                default:
                    $message = "Request saved successfully!";
            }
            
            header("Location: marine_requests.php?success=1&message=" . urlencode($message));
            exit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle Assign Request button click (separate from form submission)
if (isset($_GET['action']) && $_GET['action'] === 'assign') {
    // Simply redirect to marine_planner.php with the marine_id
    header("Location: marine_planner.php?marine_id=" . urlencode($marine_id));
    exit();
}
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Edit Marine Request</title>
    <script src="../assets/js/tailwindcss.js"></script>
    <link href="../assets/css/fonts.css" rel="stylesheet" />
    <link href="../assets/css/material-icons.css" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#343A40",
                        "background-light": "#F8F9FA",
                        "background-dark": "#111921",
                        "accent-red": "#E53E3E",
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }

        .table-container {
            margin: 0;
            max-width: none;
            border-radius: 12px;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03), 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            color: #374151;
        }

        .table-container table thead {
            background-color: #212121;
            color: #ffffff;
        }

        .table-container table th {
            padding: 16px 24px;
            text-align: left;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #E5E7EB;
        }

        .table-container table td {
            padding: 16px 24px;
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
            font-weight: 500;
            border-bottom: 1px solid #F3F4F6;
        }

        .table-container table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-container table tbody tr:hover {
            background-color: #F9FAFB;
        }

        .status-pill {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background-color: #FEF3C7; color: #B45309; }
        .status-assign, .status-assigned { background-color: #DBEAFE; color: #1E40AF; }
        .status-in-progress { background-color: #FEF3C7; color: #B45309; }
        .status-pending-endorsement { background-color: #FCE7F3; color: #9D174D; }
        .status-endorsed { background-color: #D1FAE5; color: #065F46; }
        .status-request-amendment { background-color: #FEF3C7; color: #B45309; }
        .status-cancel { background-color: #FEE2E2; color: #991B1B; }
        .status-default { background-color: #F3F4F6; color: #374151; }

        .status-pill-lg {
            padding: 8px 20px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
        }
        .section-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #E5E7EB;
            padding: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 14px;
            color: #374151;
            background-color: #ffffff;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #212121;
            box-shadow: 0 0 0 3px rgba(33, 33, 33, 0.1);
        }

        .form-input[readonly], .form-input:disabled {
            background-color: #F9FAFB;
            color: #6B7280;
            cursor: not-allowed;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: #212121;
            color: white;
        }

        .btn-primary:hover {
            background-color: #424242;
        }

        .btn-success {
            background-color: #10B981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-warning {
            background-color: #F59E0B;
            color: white;
        }

        .btn-warning:hover {
            background-color: #D97706;
        }

        .btn-info {
            background-color: #3B82F6;
            color: white;
        }

        .btn-info:hover {
            background-color: #2563EB;
        }

        .btn-secondary {
            background-color: #6B7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4B5563;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #D1D5DB;
            color: #374151;
        }

        .btn-outline:hover {
            background-color: #F9FAFB;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #E5E7EB;
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #F3F4F6;
            border: 2px solid #E5E7EB;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
        }

        .step.active .step-circle {
            background-color: #212121;
            border-color: #212121;
            color: white;
        }

        .step.completed .step-circle {
            background-color: #10B981;
            border-color: #10B981;
            color: white;
        }

        .step-label {
            font-size: 12px;
            font-weight: 500;
            color: #6B7280;
        }

        .step.active .step-label {
            color: #212121;
        }

        .step.completed .step-label {
            color: #10B981;
        }

        .section-content {
            display: none;
        }

        .section-content.active {
            display: block;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #E5E7EB;
            margin-top: 32px;
        }

        .crew-row, .service-row, .work-row {
            background-color: #F9FAFB;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid #E5E7EB;
        }

        .grid-cols-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }

        .overflow-x-auto {
            overflow-x: auto;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .table-fixed-layout {
            min-width: 1100px;
        }

        .table-fixed-layout th {
            padding: 12px 8px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            background-color: #f8fafc;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .table-input {
            width: 100%;
            padding: 6px 8px;
            font-size: 13px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.2s;
            text-transform: uppercase;
        }

        .table-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .col-no { width: 40px; }
        .col-name { width: 180px; }
        .col-id { width: 130px; }
        .col-nat { width: 130px; }
        .col-date { width: 140px; }
        .col-mobile { width: 130px; }
        .col-comp { width: 150px; }
        .col-dest { width: 150px; }
        .col-status { width: 100px; }
        .col-action { width: 50px; }
        .col-remarks { width: 150px; }

        .form-radio {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 1rem;
            height: 1rem;
            border: 2px solid #cbd5e1;
            border-radius: 50%;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-radio:checked {
            border-color: currentColor;
            background-color: currentColor;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
        }

        .form-radio:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(33, 33, 33, 0.1);
        }

        .workflow-buttons {
            display: flex;
            gap: 10px;
            margin-left: 20px;
        }

        .no-show-remarks {
            display: none;
            margin-top: 8px;
        }
        
        .no-show-remarks.active {
            display: block;
        }
        
        .log-no-required {
            border-color: #EF4444 !important;
        }
        
        .log-no-required:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }
    </style>
    <script src="../tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../Components/Layout/AdminSidebar.php'; ?>

        <!-- Header Bar -->
        <header class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Kuala Terengganu Support Base (Administrator)</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button type="button" id="profile-dropdown-btn" class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors" title="Profile">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($admin['username']); ?></span>
                    </button>
                    <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 hidden z-50">
                        <div class="py-2">
                            <a href="profile.php" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">person</span>
                                <span>Profile</span>
                            </a>
                            <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-lg">logout</span>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <main class="relative flex-1 overflow-y-auto p-4 md:p-8">
                <div class="w-full">
                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <span>Marine Requests</span>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200">Edit Request</span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Edit Marine Request</h2>
                                <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Marine ID: <?php echo htmlspecialchars($marine_id); ?></p>
                            </div>
                            
                            <!-- Workflow Action Buttons -->
                            <?php if ($marine_request): ?>
                            <div class="workflow-buttons">
                                <!-- ASSIGN Button - Only show if status is PENDING -->
                                <?php if (strtolower($marine_request['status']) === 'pending'): ?>
                                <a href="?marine_id=<?php echo urlencode($marine_id); ?>&action=assign" class="btn btn-info flex items-center gap-2">
                                    <span class="material-symbols-outlined">assignment_ind</span>
                                    Assign Request
                                </a>
                                <?php endif; ?>
                                
                                <!-- COMPLETE Button - Only show if status is ASSIGNED or IN PROGRESS -->
                                <?php if (in_array(strtolower($marine_request['status']), ['assign', 'assigned', 'in progress'])): ?>
                                <button type="button" onclick="setWorkflowAction('complete')" class="btn btn-success flex items-center gap-2">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    Complete Request
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($marine_request): ?>
                            <div class="mt-2 flex items-center gap-4 flex-wrap">
                                <span class="status-pill status-pill-lg status-<?php echo str_replace(' ', '-', strtolower($marine_request['status'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst($marine_request['status'])); ?>
                                </span>
                                
                                <?php if (strtoupper($marine_request['status']) === 'ASSIGNED' && !empty($marine_request['berth_assigned_at'])): ?>
                                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-700">
                                        <span class="material-symbols-outlined text-base text-gray-600 dark:text-gray-400">schedule</span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                            Berth Assigned: <?php echo date('d/m/Y H:i', strtotime($marine_request['berth_assigned_at'])); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($marine_request['berth_id'])): ?>
                                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                                        <span class="material-symbols-outlined text-base text-gray-600 dark:text-gray-400">location_on</span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                            Berth: <?php echo htmlspecialchars($marine_request['berth_id']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-700"><?php echo isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Request updated successfully!'; ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($marine_request): ?>
                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step completed" data-step="1">
                                <div class="step-circle">1</div>
                                <div class="step-label">Request & Schedule</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-circle">2</div>
                                <div class="step-label">Crew & Services</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-circle">3</div>
                                <div class="step-label">Fuel & Water</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-circle">4</div>
                                <div class="step-label">General Works</div>
                            </div>
                        </div>

                        <form method="POST" id="marineEditForm">
                            <input type="hidden" name="action" id="formAction" value="save">
                            
                            <!-- Section 1: Request & Schedule -->
                            <div class="section-content active" data-section="1">
                                <div class="section-container">
                                    <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <span class="material-symbols-outlined">request_quote</span>
                                        Request Information
                                    </h3>
                                    
                                    <div class="grid-cols-2">
                                        <div class="form-group">
                                            <label class="form-label">Assigned Agent</label>
                                            <select name="assigned_agent" class="form-input">
                                                <option value="">-- Select Agent --</option>
                                                <?php foreach ($agents as $agent): ?>
                                                    <option value="<?php echo $agent['agent_id']; ?>" <?php echo ($marine_request['assigned_agent_id'] == $agent['agent_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($agent['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Log No <span class="text-red-500">*</span></label>
                                            <input type="text" name="log_no" id="log_no" value="<?php echo htmlspecialchars($marine_request['log_no'] ?? ''); ?>" class="form-input <?php echo (strtolower($marine_request['status']) === 'in progress' || strtolower($marine_request['status']) === 'assign') ? 'log-no-required' : ''; ?>" placeholder="Required for completion">
                                            <p class="text-xs text-gray-500 mt-1">Required when completing the request</p>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Company</label>
                                            <select name="company" class="form-input">
                                                <option value="">-- Select Company --</option>
                                                <?php foreach ($companies as $company): ?>
                                                    <option value="<?php echo $company['company_name']; ?>" <?php echo ($marine_request['company'] == $company['company_name']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($company['company_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Vessel Name</label>
                                            <select name="vessel_name" class="form-input">
                                                <option value="">-- Select Vessel --</option>
                                                <?php foreach ($vessels as $vessel): ?>
                                                    <option value="<?php echo $vessel['vessel_name']; ?>" <?php echo ($marine_request['vessel_name'] == $vessel['vessel_name']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($vessel['vessel_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">PO Number</label>
                                            <input type="text" name="po_number" value="<?php echo htmlspecialchars($marine_request['po_number'] ?? ''); ?>" class="form-input" oninput="this.value = this.value.toUpperCase()">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Location</label>
                                            <input type="text" value="KTSB" class="form-input" readonly>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Request By</label>
                                            <input type="text" value="<?php echo htmlspecialchars($marine_request['requester_name'] ?? 'N/A'); ?>" class="form-input" readonly>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Status</label>
                                            <input type="text" value="<?php echo htmlspecialchars(ucfirst($marine_request['status'])); ?>" class="form-input" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="section-container">
                                    <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <span class="material-symbols-outlined">schedule</span>
                                        Schedule Details
                                    </h3>
                                    
                                    <div class="grid-cols-2">
                                        <div class="form-group">
                                            <label class="form-label">Est. Arrival (ETA)</label>
                                            <input type="datetime-local" name="eta" value="<?php echo date('Y-m-d\TH:i', strtotime($marine_request['eta'])); ?>" class="form-input">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Est. Departure (ETD)</label>
                                            <input type="datetime-local" name="etd" value="<?php echo date('Y-m-d\TH:i', strtotime($marine_request['etd'])); ?>" class="form-input">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Created At</label>
                                            <input type="text" value="<?php echo date('d/m/Y H:i', strtotime($marine_request['created_at'])); ?>" class="form-input" readonly>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Updated At</label>
                                            <input type="text" value="<?php echo date('d/m/Y H:i', strtotime($marine_request['updated_at'])); ?>" class="form-input" readonly>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Actual Arrival (ETA) <span class="text-red-500">*</span></label>
                                            <input type="datetime-local" name="actual_eta" id="actual_eta" value="<?php echo $marine_request['actual_eta'] ? date('Y-m-d\TH:i', strtotime($marine_request['actual_eta'])) : ''; ?>" class="form-input <?php echo (strtolower($marine_request['status']) === 'in progress' || strtolower($marine_request['status']) === 'assign') ? 'log-no-required' : ''; ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Actual Departure (ETD) <span class="text-red-500">*</span></label>
                                            <input type="datetime-local" name="actual_etd" id="actual_etd" value="<?php echo $marine_request['actual_etd'] ? date('Y-m-d\TH:i', strtotime($marine_request['actual_etd'])) : ''; ?>" class="form-input <?php echo (strtolower($marine_request['status']) === 'in progress' || strtolower($marine_request['status']) === 'assign') ? 'log-no-required' : ''; ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Berth Remarks</label>
                                            <textarea name="remarks" class="form-input" rows="3" oninput="this.value = this.value.toUpperCase()"><?php echo htmlspecialchars($marine_request['remarks'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2: Crew & Services -->
                            <div class="section-content" data-section="2">
                                <div class="section-container">
                                    <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <span class="material-symbols-outlined">groups</span>
                                        Crew Transfer Details
                                    </h3>

                                    <div class="mb-4 max-w-xs">
                                        <label class="form-label">Type</label>
                                        <input type="text" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $marine_request['crew_transfer_type']))); ?>" class="form-input" readonly>
                                    </div>

                                    <div class="table-responsive-container mb-6 shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 table-fixed-layout">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="col-no">No</th>
                                                        <th class="col-name">Name</th>
                                                        <th class="col-id">Passport/IC</th>
                                                        <th class="col-nat">Nationality</th>
                                                        <th class="col-date">Passport Expiry Date</th>
                                                        <th class="col-mobile">Mobile</th>
                                                        <th class="col-comp">Company</th>
                                                        <th class="col-dest">Destination</th>
                                                        <th class="col-status">Attendance</th>
                                                        <th class="col-remarks">No Show Remarks</th>
                                                        <th class="col-action"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="crewTableBody" class="bg-white divide-y divide-gray-200">
                                                    <?php foreach ($crew_members as $index => $crew): ?>
                                                    <tr class="crew-row hover:bg-gray-50" data-crew-index="<?php echo $index; ?>">
                                                        <td class="px-3 py-3 text-sm text-center text-gray-900"><?php echo $index + 1; ?></td>
                                                        <td class="px-2 py-2"><input type="text" name="crew[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($crew['name']); ?>" class="table-input auto-uppercase" required></td>
                                                        <td class="px-2 py-2"><input type="text" name="crew[<?php echo $index; ?>][passport_ic]" value="<?php echo htmlspecialchars($crew['passport_ic']); ?>" class="table-input bg-gray-50 auto-uppercase"></td>
                                                        <td class="px-2 py-2">
                                                            <select name="crew[<?php echo $index; ?>][nationality]" class="table-input auto-uppercase-select">
                                                                <option value="">--</option>
                                                                <?php foreach ($nationalities as $nationality): ?>
                                                                <option value="<?php echo htmlspecialchars($nationality['name']); ?>" <?php echo ($crew['nationality'] == $nationality['name']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($nationality['name']); ?>
                                                                </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td class="px-2 py-2"><input type="date" name="crew[<?php echo $index; ?>][expiry]" value="<?php echo htmlspecialchars($crew['expiry'] ?? ''); ?>" class="table-input"></td>
                                                        <td class="px-2 py-2"><input type="text" name="crew[<?php echo $index; ?>][mobile]" value="<?php echo htmlspecialchars($crew['mobile'] ?? ''); ?>" class="table-input auto-uppercase"></td>
                                                        <td class="px-2 py-2"><input type="text" name="crew[<?php echo $index; ?>][company]" value="<?php echo htmlspecialchars($crew['company'] ?? ''); ?>" class="table-input auto-uppercase"></td>
                                                        <td class="px-2 py-2"><input type="text" name="crew[<?php echo $index; ?>][destination]" value="<?php echo htmlspecialchars($crew['destination'] ?? ''); ?>" class="table-input auto-uppercase"></td>
                                                        <td class="px-2 py-2">
                                                            <div class="flex flex-col gap-1">
                                                                <label class="inline-flex items-center cursor-pointer">
                                                                    <input type="radio" name="crew[<?php echo $index; ?>][attendance_status]" value="show" <?php echo (isset($crew['attendance_status']) && $crew['attendance_status'] == 'show') ? 'checked' : ''; ?> class="form-radio text-green-600 attendance-radio" onchange="toggleNoShowRemarks(this, <?php echo $index; ?>)">
                                                                    <span class="ml-1 text-[11px]">Show</span>
                                                                </label>
                                                                <label class="inline-flex items-center cursor-pointer">
                                                                    <input type="radio" name="crew[<?php echo $index; ?>][attendance_status]" value="no_show" <?php echo (isset($crew['attendance_status']) && $crew['attendance_status'] == 'no_show') ? 'checked' : ''; ?> class="form-radio text-red-600 attendance-radio" onchange="toggleNoShowRemarks(this, <?php echo $index; ?>)">
                                                                    <span class="ml-1 text-[11px]">No Show</span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <div class="no-show-remarks <?php echo (isset($crew['attendance_status']) && $crew['attendance_status'] == 'no_show') ? 'active' : ''; ?>" id="remarks-<?php echo $index; ?>">
                                                                <input type="text" name="crew[<?php echo $index; ?>][no_show_remarks]" value="<?php echo htmlspecialchars($crew['no_show_remarks'] ?? ''); ?>" class="table-input auto-uppercase" placeholder="Reason for no show">
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-2 text-center">
                                                            <button type="button" onclick="removeCrew(this)" class="text-red-400 hover:text-red-600 transition-colors">
                                                                <span class="material-symbols-outlined text-lg">delete</span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <button type="button" onclick="addCrewRow()" class="btn btn-outline border-dashed border-2 flex items-center gap-2 hover:bg-gray-50">
                                        <span class="material-symbols-outlined">add</span>
                                        Add Crew Member
                                    </button>
                                    <input type="hidden" id="crewCount" value="<?php echo count($crew_members); ?>">
                                </div>

                                <div class="section-container">
                                    <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <span class="material-symbols-outlined">service_toolbox</span>
                                        Other Services
                                    </h3>

                                    <div id="otherServicesContainer">
                                        <?php foreach ($other_services as $index => $service): ?>
                                            <div class="service-row">
                                                <div class="flex justify-between items-center mb-3">
                                                    <span class="font-medium">Service <?php echo $index + 1; ?></span>
                                                    <button type="button" class="remove-service text-red-600 hover:text-red-800" onclick="removeService(this)">
                                                        <span class="material-symbols-outlined text-sm">delete</span>
                                                    </button>
                                                </div>
                                                <div class="grid-cols-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Service Name</label>
                                                        <input type="text" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $service['service_type']))); ?>" class="form-input" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Quantity</label>
                                                        <input type="number" name="other_services[<?php echo $index; ?>][quantity]" value="<?php echo $service['quantity']; ?>" class="form-input" min="0">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="other_services[<?php echo $index; ?>][service_type]" value="<?php echo $service['service_type']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Service Selector -->
                                    <div id="serviceSelector" class="hidden mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1">
                                                <label class="form-label">Select Service</label>
                                                <select id="serviceDropdown" class="form-input">
                                                    <option value="">-- Select Service --</option>
                                                </select>
                                            </div>
                                            <div class="flex gap-2 mt-6">
                                                <button type="button" onclick="addSelectedService()" class="btn btn-primary">
                                                    <span class="material-symbols-outlined text-sm mr-1">add</span>
                                                    Add
                                                </button>
                                                <button type="button" onclick="cancelAddService()" class="btn btn-secondary">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" onclick="showServiceSelector()" class="btn btn-outline flex items-center gap-2 mt-4">
                                        <span class="material-symbols-outlined">add</span>
                                        Add Other Service
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Section 3: Fuel & Water -->
                            <div class="section-content" data-section="3">
                                <div class="section-container">
                                    <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <span class="material-symbols-outlined">local_gas_station</span>
                                        Fuel & Water Supply
                                    </h3>
                                    
                                    <div class="table-responsive-container mb-6 shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="overflow-x-auto">                                          
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity (L)</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Quantity (L)</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Time</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Booking Time</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remark</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="fuelWaterTableBody" class="bg-white divide-y divide-gray-200">
                                                    <?php 
                                                    $fuelWaterIndex = 0;
                                                    $hasFuel = false;
                                                    $hasWater = false;
                                                    
                                                    foreach ($fuel_water_services as $service): 
                                                        if ($service['service_type'] == 'fuel') $hasFuel = true;
                                                        if ($service['service_type'] == 'water') $hasWater = true;
                                                    ?>
                                                    <tr class="fuel-water-row hover:bg-gray-50" data-fw-index="<?php echo $fuelWaterIndex; ?>">
                                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo $fuelWaterIndex + 1; ?></td>
                                                        <td class="px-4 py-3">
                                                            <select name="fuel_water[<?php echo $fuelWaterIndex; ?>][service_type]" class="table-input w-28 service-type-select">
                                                                <option value="fuel" <?php echo ($service['service_type'] == 'fuel') ? 'selected' : ''; ?>>Fuel</option>
                                                                <option value="water" <?php echo ($service['service_type'] == 'water') ? 'selected' : ''; ?>>Water</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="number" step="0.01" name="fuel_water[<?php echo $fuelWaterIndex; ?>][quantity]" value="<?php echo htmlspecialchars($service['quantity']); ?>" class="table-input w-24" placeholder="Qty">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="number" step="0.01" name="fuel_water[<?php echo $fuelWaterIndex; ?>][actual_quantity]" 
                                                                   value="<?php echo htmlspecialchars($service['actual_quantity'] ?? ''); ?>" 
                                                                   class="table-input w-24 bg-yellow-50" placeholder="Actual">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="time" name="fuel_water[<?php echo $fuelWaterIndex; ?>][booking_time]" 
                                                                   value="<?php echo $service['booking_time'] ? substr($service['booking_time'], 0, 5) : ''; ?>" 
                                                                   class="table-input w-28">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="time" name="fuel_water[<?php echo $fuelWaterIndex; ?>][actual_booking_time]" 
                                                                   value="<?php echo $service['actual_booking_time'] ? substr($service['actual_booking_time'], 0, 5) : ''; ?>" 
                                                                   class="table-input w-28 bg-yellow-50">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <textarea name="fuel_water[<?php echo $fuelWaterIndex; ?>][remarks]" class="table-input auto-uppercase w-40" rows="1" placeholder="Remark"><?php echo htmlspecialchars($service['remarks'] ?? ''); ?></textarea>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <button type="button" onclick="removeFuelWaterRow(this)" class="text-red-400 hover:text-red-600 transition-colors">
                                                                <span class="material-symbols-outlined text-lg">delete</span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                    $fuelWaterIndex++;
                                                    endforeach; 
                                                    
                                                    // If no fuel/water services exist, show at least one empty row
                                                    if ($fuelWaterIndex == 0):
                                                    ?>
                                                    <tr class="fuel-water-row hover:bg-gray-50" data-fw-index="0">
                                                        <td class="px-4 py-3 text-sm text-gray-900">1</td>
                                                        <td class="px-4 py-3">
                                                            <select name="fuel_water[0][service_type]" class="table-input w-28 service-type-select">
                                                                <option value="fuel">Fuel</option>
                                                                <option value="water">Water</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="number" step="0.01" name="fuel_water[0][quantity]" value="" class="table-input w-24" placeholder="Qty">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="number" step="0.01" name="fuel_water[0][actual_quantity]" value="" class="table-input w-24 bg-yellow-50" placeholder="Actual">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="time" name="fuel_water[0][booking_time]" value="" class="table-input w-28">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <input type="time" name="fuel_water[0][actual_booking_time]" value="" class="table-input w-28 bg-yellow-50">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <textarea name="fuel_water[0][remarks]" class="table-input auto-uppercase w-40" rows="1" placeholder="Remark"></textarea>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <button type="button" onclick="removeFuelWaterRow(this)" class="text-red-400 hover:text-red-600 transition-colors">
                                                                <span class="material-symbols-outlined text-lg">delete</span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="flex gap-3 mt-4">
                                        <button type="button" onclick="addFuelWaterRow()" class="btn btn-outline border-dashed border-2 flex items-center gap-2 hover:bg-gray-50 <?php echo ($hasFuel && $hasWater) ? 'hidden' : ''; ?>" id="addFuelWaterBtn">
                                            <span class="material-symbols-outlined">add</span>
                                            Add Fuel/Water Row
                                        </button>
                                    </div>
                                     <!-- PRINT PDF BUTTON - Using Dompdf version -->
                                    <?php if (!empty($fuel_water_services)): ?>
                                    <a href="api/generate_fw_pdf.php?marine_id=<?php echo urlencode($marine_id); ?>" 
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
                                        <span class="material-symbols-outlined text-sm">print</span>
                                        <span class="text-sm font-medium">Print Job Ticket</span>
                                        <span class="material-symbols-outlined text-sm">download</span>
                                    </a>
                                    <?php endif; ?> 
                                </div>
                            </div>
                            
                            <!-- Section 4: General Works -->
                            <div class="section-content" data-section="4">
                                <div class="section-container">
                                    <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200 mb-6 flex items-center gap-2">
                                        <span class="material-symbols-outlined">construction</span>
                                        General Works
                                    </h3>
                                    
                                    <div id="generalWorksContainer">
                                        <?php foreach ($general_works as $index => $work): ?>
                                            <div class="work-row">
                                                <div class="flex justify-between items-center mb-3">
                                                    <span class="font-medium">Work #<?php echo $index + 1; ?></span>
                                                    <button type="button" class="remove-work text-red-600 hover:text-red-800" onclick="removeWork(this)">
                                                        <span class="material-symbols-outlined text-sm">delete</span>
                                                    </button>
                                                </div>
                                                <div class="grid-cols-2">
                                                    <div class="form-group">
                                                        <label class="form-label">Work Type</label>
                                                        <input type="text" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $work['work_type']))); ?>" class="form-input" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Remarks</label>
                                                        <textarea name="general_works[<?php echo $index; ?>][remarks]" class="form-input auto-uppercase" rows="2"><?php echo htmlspecialchars($work['remarks'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="general_works[<?php echo $index; ?>][work_type]" value="<?php echo $work['work_type']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Work Selector -->
                                    <div id="workSelector" class="hidden mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1">
                                                <label class="form-label">Select Work Type</label>
                                                <select id="workDropdown" class="form-input">
                                                    <option value="">-- Select Work Type --</option>
                                                </select>
                                            </div>
                                            <div class="flex gap-2 mt-6">
                                                <button type="button" onclick="addSelectedWork()" class="btn btn-primary">
                                                    <span class="material-symbols-outlined text-sm mr-1">add</span>
                                                    Add
                                                </button>
                                                <button type="button" onclick="cancelAddWork()" class="btn btn-secondary">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" onclick="showWorkSelector()" class="btn btn-outline flex items-center gap-2 mt-4">
                                        <span class="material-symbols-outlined">add</span>
                                        Add General Works
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <div>
                                    <a href="marine_requests.php" class="btn btn-outline">
                                        <span class="material-symbols-outlined align-middle mr-2">arrow_back</span>
                                        Back to Marine Requests
                                    </a>
                                    <button type="button" onclick="prevSection()" class="btn btn-secondary hidden" id="prevBtn">
                                        <span class="material-symbols-outlined align-middle mr-2">arrow_back</span>
                                        Previous
                                    </button>
                                </div>

                                <div class="flex gap-3">
                                    <button type="button" onclick="nextSection()" class="btn btn-primary" id="nextBtn">
                                        Next
                                        <span class="material-symbols-outlined align-middle ml-2">arrow_forward</span>
                                    </button>

                                    <button type="submit" onclick="setAction('save')" class="btn btn-secondary">
                                        <span class="material-symbols-outlined align-middle mr-2">save</span>
                                        Save as Draft
                                    </button>
                                    
                                    <!-- NEW: Complete Request button beside Save as Draft -->
                                    <?php if ($marine_request && in_array(strtolower($marine_request['status']), ['assign', 'assigned', 'in progress'])): ?>
                                    <button type="button" onclick="setWorkflowAction('complete')" class="btn btn-success flex items-center gap-2">
                                        <span class="material-symbols-outlined align-middle mr-2">check_circle</span>
                                        Complete Request
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="section-container text-center py-8">
                            <span class="material-symbols-outlined text-4xl text-gray-400">error</span>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">Marine request not found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto shadow-xl">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-2xl text-blue-600">info</span>
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Confirm Action</h3>
            </div>
            <p class="text-gray-600 mb-6" id="modalMessage">Are you sure you want to perform this action?</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button onclick="submitWorkflowAction()" class="btn" id="modalConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let currentSection = 1;
        let crewIndex = <?php echo count($crew_members); ?>;
        let serviceIndex = <?php echo count($other_services); ?>;
        let workIndex = <?php echo count($general_works); ?>;
        let pendingAction = null;
        
        const serviceTypes = <?php echo json_encode([
            'sign_on' => ['packed_meals', 'snack_pack', 'baggage_handling', 'bag_tagging'],
            'sign_off' => ['takeaway', 'baggage_handling']
        ]); ?>;
        
        const workTypes = [
            'discharge', 'loading', 'inspection', 'maintenance', 'standby',
            'touch & go', 'mooring', 'unmooring', 'fire fighter',
            'pneumatic_rubber_fender', 'gangway_6_meter', 'gangway_10_meter',
            'gangway_15_meter', 'crew_change'
        ];

        // Auto-uppercase function for inputs
        function setupAutoUppercase() {
            document.querySelectorAll('.auto-uppercase').forEach(input => {
                input.addEventListener('input', function(e) {
                    this.value = this.value.toUpperCase();
                });
            });
            
            document.querySelectorAll('.auto-uppercase-select').forEach(select => {
                select.addEventListener('change', function(e) {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        selectedOption.value = selectedOption.value.toUpperCase();
                    }
                });
            });
        }

        // Toggle no-show remarks field
        function toggleNoShowRemarks(radio, index) {
            const remarksDiv = document.getElementById(`remarks-${index}`);
            if (radio.value === 'no_show' && radio.checked) {
                remarksDiv.classList.add('active');
            } else if (radio.value === 'show' && radio.checked) {
                remarksDiv.classList.remove('active');
            }
        }

        function updateStepIndicator() {
            document.querySelectorAll('.step').forEach(step => {
                const stepNum = parseInt(step.dataset.step);
                step.classList.remove('active', 'completed');
                if (stepNum < currentSection) {
                    step.classList.add('completed');
                } else if (stepNum === currentSection) {
                    step.classList.add('active');
                }
            });
        }

        function showSection(sectionNum) {
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.remove('active');
            });
            
            const targetSection = document.querySelector(`[data-section="${sectionNum}"]`);
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            if (sectionNum === 1) {
                prevBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
            }
            
            if (sectionNum === 4) {
                nextBtn.classList.add('hidden');
            } else {
                nextBtn.classList.remove('hidden');
            }
            
            updateStepIndicator();
        }

        function nextSection() {
            if (currentSection < 4) {
                currentSection++;
                showSection(currentSection);
            }
        }

        function prevSection() {
            if (currentSection > 1) {
                currentSection--;
                showSection(currentSection);
            }
        }

        function addCrewRow() {
            const tbody = document.getElementById('crewTableBody');
            const newIndex = crewIndex;
            
            const row = document.createElement('tr');
            row.className = 'crew-row hover:bg-gray-50';
            row.setAttribute('data-crew-index', newIndex);
            row.innerHTML = `
                <td class="px-3 py-3 text-sm text-center text-gray-900">${newIndex + 1}</td>
                <td class="px-2 py-2">
                    <input type="text" name="crew[${newIndex}][name]" class="table-input auto-uppercase" required>
                </td>
                <td class="px-2 py-2">
                    <input type="text" name="crew[${newIndex}][passport_ic]" class="table-input bg-gray-50 auto-uppercase" placeholder="Passport or IC number">
                </td>
                <td class="px-2 py-2">
                    <select name="crew[${newIndex}][nationality]" class="table-input auto-uppercase-select">
                        <option value="">--</option>
                        <?php foreach ($nationalities as $nationality): ?>
                        <option value="<?php echo htmlspecialchars($nationality['name']); ?>">
                            <?php echo htmlspecialchars($nationality['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="px-2 py-2">
                    <input type="date" name="crew[${newIndex}][expiry]" class="table-input">
                </td>
                <td class="px-2 py-2">
                    <input type="text" name="crew[${newIndex}][mobile]" class="table-input auto-uppercase" placeholder="e.g., +6012-3456789">
                </td>
                <td class="px-2 py-2">
                    <input type="text" name="crew[${newIndex}][company]" class="table-input auto-uppercase">
                </td>
                <td class="px-2 py-2">
                    <input type="text" name="crew[${newIndex}][destination]" class="table-input auto-uppercase">
                </td>
                <td class="px-2 py-2">
                    <div class="flex flex-col gap-1">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="crew[${newIndex}][attendance_status]" value="show" class="form-radio text-green-600 attendance-radio" onchange="toggleNoShowRemarks(this, ${newIndex})" checked>
                            <span class="ml-1 text-[11px]">Show</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="crew[${newIndex}][attendance_status]" value="no_show" class="form-radio text-red-600 attendance-radio" onchange="toggleNoShowRemarks(this, ${newIndex})">
                            <span class="ml-1 text-[11px]">No Show</span>
                        </label>
                    </div>
                </td>
                <td class="px-2 py-2">
                    <div class="no-show-remarks" id="remarks-${newIndex}">
                        <input type="text" name="crew[${newIndex}][no_show_remarks]" class="table-input auto-uppercase" placeholder="Reason for no show">
                    </div>
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" onclick="removeCrew(this)" class="text-red-400 hover:text-red-600 transition-colors">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            setupAutoUppercase();
            crewIndex++;
            updateRowNumbers();
        }

        function removeCrew(button) {
            const row = button.closest('tr');
            row.remove();
            updateRowNumbers();
        }

        function updateRowNumbers() {
            const rows = document.querySelectorAll('#crewTableBody tr');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
            });
        }

        function showServiceSelector() {
            const transferType = '<?php echo $marine_request['crew_transfer_type'] ?? 'sign_on'; ?>';
            const availableServices = serviceTypes[transferType] || [];

            const existingServices = Array.from(document.querySelectorAll('input[name^="other_services"][name$="[service_type]"]'))
                .map(input => input.value);

            const newServices = availableServices.filter(service => !existingServices.includes(service));

            if (newServices.length === 0) {
                alert('All available services have been added.');
                return;
            }

            const dropdown = document.getElementById('serviceDropdown');
            dropdown.innerHTML = '<option value="">-- Select Service --</option>';

            newServices.forEach(service => {
                const displayName = service.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const option = document.createElement('option');
                option.value = service;
                option.textContent = displayName;
                dropdown.appendChild(option);
            });

            document.getElementById('serviceSelector').classList.remove('hidden');
        }

        function addSelectedService() {
            const dropdown = document.getElementById('serviceDropdown');
            const selectedService = dropdown.value;

            if (!selectedService) {
                alert('Please select a service.');
                return;
            }

            const container = document.getElementById('otherServicesContainer');
            const serviceRow = document.createElement('div');
            serviceRow.className = 'service-row';

            const displayName = selectedService.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            serviceRow.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <span class="font-medium">Service #${serviceIndex + 1}</span>
                    <button type="button" class="remove-service text-red-600 hover:text-red-800" onclick="removeService(this)">
                        <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                </div>
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Service Name</label>
                        <input type="text" value="${displayName}" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="other_services[${serviceIndex}][quantity]" value="0" class="form-input" min="0" required>
                    </div>
                </div>
                <input type="hidden" name="other_services[${serviceIndex}][service_type]" value="${selectedService}">
            `;
            container.appendChild(serviceRow);
            serviceIndex++;

            document.getElementById('serviceSelector').classList.add('hidden');
            dropdown.value = '';
        }

        function cancelAddService() {
            document.getElementById('serviceSelector').classList.add('hidden');
            document.getElementById('serviceDropdown').value = '';
        }

        function removeService(button) {
            const serviceRow = button.closest('.service-row');
            serviceRow.remove();
        }

        function showWorkSelector() {
            const existingWorks = Array.from(document.querySelectorAll('input[name^="general_works"][name$="[work_type]"]'))
                .map(input => input.value);

            const newWorks = workTypes.filter(work => !existingWorks.includes(work));

            if (newWorks.length === 0) {
                alert('All available work types have been added.');
                return;
            }

            const dropdown = document.getElementById('workDropdown');
            dropdown.innerHTML = '<option value="">-- Select Work Type --</option>';

            newWorks.forEach(work => {
                const displayName = work.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const option = document.createElement('option');
                option.value = work;
                option.textContent = displayName;
                dropdown.appendChild(option);
            });

            document.getElementById('workSelector').classList.remove('hidden');
        }

        function addSelectedWork() {
            const dropdown = document.getElementById('workDropdown');
            const selectedWork = dropdown.value;

            if (!selectedWork) {
                alert('Please select a work type.');
                return;
            }

            const container = document.getElementById('generalWorksContainer');
            const workRow = document.createElement('div');
            workRow.className = 'work-row';

            const displayName = selectedWork.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            workRow.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <span class="font-medium">Work #${workIndex + 1}</span>
                    <button type="button" class="remove-work text-red-600 hover:text-red-800" onclick="removeWork(this)">
                        <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                </div>
                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Work Type</label>
                        <input type="text" value="${displayName}" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea name="general_works[${workIndex}][remarks]" class="form-input auto-uppercase" rows="2"></textarea>
                    </div>
                </div>
                <input type="hidden" name="general_works[${workIndex}][work_type]" value="${selectedWork}">
            `;
            container.appendChild(workRow);
            workIndex++;

            document.getElementById('workSelector').classList.add('hidden');
            dropdown.value = '';
        }

        function cancelAddWork() {
            document.getElementById('workSelector').classList.add('hidden');
            document.getElementById('workDropdown').value = '';
        }

        function removeWork(button) {
            const workRow = button.closest('.work-row');
            workRow.remove();
        }

        function setAction(action) {
            document.getElementById('formAction').value = action;
            return true;
        }

        // WORKFLOW ACTIONS - Only for Complete now
        function setWorkflowAction(action) {
            if (action === 'assign') {
                // This should never be called now since we use direct link
                return;
            }
            
            pendingAction = action;
            
            const modal = document.getElementById('confirmModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalConfirmBtn = document.getElementById('modalConfirmBtn');
            
            switch(action) {
                case 'complete':
                    // Check validation before showing modal
                    const currentStatus = '<?php echo strtolower($marine_request['status']); ?>';
                    const logNo = document.querySelector('input[name="log_no"]').value.trim();
                    const actualEta = document.querySelector('input[name="actual_eta"]').value;
                    const actualEtd = document.querySelector('input[name="actual_etd"]').value;
                    
                    let validationErrors = [];
                    
                    if (!in_array(currentStatus, ['assign', 'assigned', 'in progress'])) {
                        validationErrors.push("Request must be in 'ASSIGNED' or 'IN PROGRESS' status before completion.");
                    }
                    if (!logNo) {
                        validationErrors.push("Log number is REQUIRED before completion.");
                    }
                    if (!actualEta) {
                        validationErrors.push("Actual ETA is required before completion.");
                    }
                    if (!actualEtd) {
                        validationErrors.push("Actual ETD is required before completion.");
                    }
                    
                    if (validationErrors.length > 0) {
                        alert("Cannot complete request:\n\n" + validationErrors.join("\n"));
                        return;
                    }
                    
                    // Auto-populate actual fuel/water fields if they're empty
                    autoPopulateFuelWaterActuals();
                    
                    modalTitle.textContent = 'Confirm Completion';
                    modalMessage.textContent = 'Are you sure you want to complete this request? The status will change to "PENDING ENDORSEMENT".';
                    modalConfirmBtn.className = 'btn btn-success';
                    modalConfirmBtn.textContent = 'Confirm Complete';
                    break;
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Function to auto-populate actual fuel/water fields
        function autoPopulateFuelWaterActuals() {
            const rows = document.querySelectorAll('#fuelWaterTableBody tr');
            
            rows.forEach(row => {
                const quantityInput = row.querySelector('input[name*="[quantity]"]');
                const actualQuantityInput = row.querySelector('input[name*="[actual_quantity]"]');
                const bookingTimeInput = row.querySelector('input[name*="[booking_time]"]');
                const actualBookingTimeInput = row.querySelector('input[name*="[actual_booking_time]"]');
                
                // Auto-populate actual quantity if empty
                if (actualQuantityInput && quantityInput && !actualQuantityInput.value && quantityInput.value) {
                    actualQuantityInput.value = quantityInput.value;
                }
                
                // Auto-populate actual booking time if empty
                if (actualBookingTimeInput && bookingTimeInput && !actualBookingTimeInput.value && bookingTimeInput.value) {
                    actualBookingTimeInput.value = bookingTimeInput.value;
                }
            });
        }

        function in_array(needle, haystack) {
            return haystack.includes(needle);
        }

        function closeModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingAction = null;
        }

        function submitWorkflowAction() {
            if (pendingAction) {
                setAction(pendingAction);
                document.getElementById('marineEditForm').submit();
            }
            closeModal();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            updateStepIndicator();
            showSection(1);
            setupAutoUppercase();
            
            // Highlight required fields for completion
            const logNoField = document.getElementById('log_no');
            const actualEtaField = document.getElementById('actual_eta');
            const actualEtdField = document.getElementById('actual_etd');
            
            if (logNoField && actualEtaField && actualEtdField) {
                const status = '<?php echo strtolower($marine_request['status']); ?>';
                if (status === 'in progress' || status === 'assign' || status === 'assigned') {
                    logNoField.placeholder = 'REQUIRED for completion';
                    actualEtaField.placeholder = 'REQUIRED for completion';
                    actualEtdField.placeholder = 'REQUIRED for completion';
                }
            }
            
            // Profile dropdown
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
            }
            
            // Update time
            function updateTime() {
                const date = new Date();
                document.getElementById('date').innerText = date.toLocaleDateString('en-GB', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric' 
                }).toUpperCase();
                document.getElementById('time').innerText = date.toLocaleTimeString('en-US', { hour12: false });
            }
            setInterval(updateTime, 1000);
            updateTime();
            
            // Initialize fuel/water check
            checkFuelWaterSelection();
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('confirmModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Fuel & Water table functions
        let fuelWaterIndex = <?php echo count($fuel_water_services) ?: 0; ?>;

        function addFuelWaterRow() {
            const tbody = document.getElementById('fuelWaterTableBody');
            const newIndex = fuelWaterIndex;
            
            const row = document.createElement('tr');
            row.className = 'fuel-water-row hover:bg-gray-50';
            row.setAttribute('data-fw-index', newIndex);
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-900">${newIndex + 1}</td>
                <td class="px-4 py-3">
                    <select name="fuel_water[${newIndex}][service_type]" class="table-input w-28 service-type-select" onchange="checkFuelWaterSelection()">
                        <option value="fuel">Fuel</option>
                        <option value="water">Water</option>
                    </select>
                </td>
                <td class="px-4 py-3">
                    <input type="number" step="0.01" name="fuel_water[${newIndex}][quantity]" value="" class="table-input w-24" placeholder="Qty">
                </td>
                <td class="px-4 py-3">
                    <input type="number" step="0.01" name="fuel_water[${newIndex}][actual_quantity]" value="" class="table-input w-24 bg-yellow-50" placeholder="Actual">
                </td>
                <td class="px-4 py-3">
                    <input type="time" name="fuel_water[${newIndex}][booking_time]" value="" class="table-input w-28">
                </td>
                <td class="px-4 py-3">
                    <input type="time" name="fuel_water[${newIndex}][actual_booking_time]" value="" class="table-input w-28 bg-yellow-50">
                </td>
                <td class="px-4 py-3">
                    <textarea name="fuel_water[${newIndex}][remarks]" class="table-input auto-uppercase w-40" rows="1" placeholder="Remark"></textarea>
                </td>
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="removeFuelWaterRow(this)" class="text-red-400 hover:text-red-600 transition-colors">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            setupAutoUppercase();
            fuelWaterIndex++;
            updateFuelWaterRowNumbers();
            checkFuelWaterSelection();
        }

        function removeFuelWaterRow(button) {
            const row = button.closest('tr');
            row.remove();
            updateFuelWaterRowNumbers();
            checkFuelWaterSelection();
        }

        function updateFuelWaterRowNumbers() {
            const rows = document.querySelectorAll('#fuelWaterTableBody tr');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
                row.setAttribute('data-fw-index', index);
            });
        }

        function checkFuelWaterSelection() {
            const rows = document.querySelectorAll('#fuelWaterTableBody tr');
            let hasFuel = false;
            let hasWater = false;
            
            rows.forEach(row => {
                const select = row.querySelector('.service-type-select');
                if (select) {
                    if (select.value === 'fuel') hasFuel = true;
                    if (select.value === 'water') hasWater = true;
                }
            });
            
            const addButton = document.getElementById('addFuelWaterBtn');
            if (addButton) {
                if (hasFuel && hasWater) {
                    addButton.classList.add('hidden');
                } else {
                    addButton.classList.remove('hidden');
                }
            }
        }
    </script>
</body>
</html>