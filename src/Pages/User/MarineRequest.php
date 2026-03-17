<?php
// Protected index page - requires authentication and appropriate role
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Check if user has permission to access this page (company, admin, and agent users can access)
if ($currentUser['user_type'] !== 'user' && $currentUser['role'] !== 'admin' && $currentUser['role'] !== 'agent') {
    header('Location: fuel-water-history.php');
    exit;
}

// Get DB Connection and Fetch Vessels
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, vessel_name FROM vessels ORDER BY vessel_name ASC");
$vessels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link rel="icon" href="assets/images/KTSB Logo.jpeg" type="image/png"/>
<title>Berth Request Form</title>
    <script src="assets/js/tailwindcss.js"></script>
    <script src="assets/js/xlsx.full.min.js"></script>
    <link href="assets/css/fonts.css" rel="stylesheet"/>
    <link href="assets/css/material-icons.css" rel="stylesheet"/>
    <script src="tab-session.js"></script>
    <link href="assets/css/vt323.css" rel="stylesheet"/>
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
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
        },
      }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24
        }

        /* Retro dot matrix styling for progress indicator */
        .retro-progress .retro-step-number {
            font-family: 'VT323', monospace;
            font-size: 2.5rem;
            font-weight: bold;
            color: #000000;
            text-shadow: none;
        }

        .retro-progress .retro-step-text-secondary {
            margin-top: -0.5rem;
        }

        .retro-progress .retro-step-number.active {
            color: #E53E3E;
        }

        .retro-progress .retro-step-text {
            font-family: 'VT323', monospace;
            font-size: 1.25rem;
            font-weight: bold;
            color: #333333;
            text-shadow: none;
            letter-spacing: 0.1em;
        }

        .retro-progress .retro-step-text.active {
            color: #E53E3E;
        }

        .retro-progress .retro-step-text-secondary {
            font-family: 'VT323', monospace;
            font-size: 1.25rem;
            font-weight: bold;
            color: #333333;
            text-shadow: none;
            letter-spacing: 0.1em;
        }

        .retro-progress .retro-step-text-secondary.active {
            color: #E53E3E;
        }

        /* --- Base SVG Setup --- */
        .cool-mode-icon path {
            stroke-dasharray: 100; 
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 0s;
        }

        /* --- Wrapper Animation (Elastic Expand) --- */
        .cool-mode-wrapper {
            background-color: #16a34a !important; /* Force Green-600 */
            transform: scale(0); 
            animation: scaleElastic 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            position: relative; 
            z-index: 10;
        }

        @keyframes scaleElastic {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* --- Pulse Effect (Ripple Behind) --- */
        .cool-mode-wrapper::before {
            content: '';
            position: absolute;
            inset: -4px; 
            border-radius: 50%;
            background-color: rgba(74, 222, 128, 0.6); /* Green-400 opacity 0.6 */
            z-index: -1;
            animation: pulseRing 2s infinite;
        }

        @keyframes pulseRing {
            0% { transform: scale(1); opacity: 0.7; }
            70% { transform: scale(1.5); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
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

        /* Dark mode adjustments */
        .dark .retro-progress .retro-step-number {
            color: #666666;
        }

        .dark .retro-progress .retro-step-number.active {
            color: #E53E3E;
        }

        .dark .retro-progress .retro-step-text {
            color: #999999;
        }

        .dark .retro-progress .retro-step-text.active {
            color: #E53E3E;
        }

        .dark .retro-progress .retro-step-text-secondary {
            color: #999999;
        }

        .dark .retro-progress .retro-step-text-secondary.active {
            color: #E53E3E;
        }

        /* Exact styling to match marine-detail.php */
        .detail-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03), 0 2px 6px rgba(0, 0, 0, 0.02);
            padding: 24px;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Standardized typography for marine detail page */
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

        .marine-detail-section p, .marine-detail-section span {
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

        .status-pending {
            background-color: #FEF3C7;
            color: #B45309;
        }

        .status-completed {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-approved {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-rejected {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-default {
            background-color: #F3F4F6;
            color: #374151;
        }
        /* ========================================= */
        /* ESSENTIAL CSS ANIMATION         */
        /* ========================================= */

        /* --- Base Transitions --- */
        #success-icon, #user-plus-icon { 
            transition: all 0.3s ease; 
        }
        #success-icon path { 
            stroke-dasharray: 100; 
            stroke-dashoffset: 0; 
            transition: stroke-dashoffset 0s; 
        }

        /* --- 1. Wrapper Animation (Blue #212180 -> Green) --- */
        .person-mode-wrapper {
            position: relative; 
            z-index: 10;
            /* Start Blue, End Green */
            animation: bgMorph 1.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes bgMorph {
            0% { transform: scale(0); background-color: #212180; }
            20% { transform: scale(1); background-color: #212180; }
            60% { transform: scale(1); background-color: #212180; } /* Hold Blue */
            70% { transform: scale(0.8); background-color: #212180; } /* Anticipation Shrink */
            85% { transform: scale(1.1); background-color: #16a34a; /* Pop to Green */ }
            100% { transform: scale(1); background-color: #16a34a; }
        }

        /* --- Pulse Ring --- */
        .person-mode-wrapper::before {
            content: ''; 
            position: absolute; 
            inset: -4px; 
            border-radius: 50%; 
            z-index: -1;
            animation: pulseMorph 2s infinite;
        }

        @keyframes pulseMorph {
            0% { transform: scale(1); opacity: 0.6; background-color: #5c5cbe; }
            45% { background-color: #5c5cbe; }
            55% { background-color: #86efac; }
            70% { transform: scale(1.5); opacity: 0; background-color: #86efac; }
            100% { transform: scale(1); opacity: 0; }
        }

        /* --- 2. User Plus Icon (Exit) --- */
        .person-mode-user {
            display: block; 
            color: white;
            animation: userExit 1.4s ease forwards;
        }

        @keyframes userExit {
            0% { opacity: 0; transform: scale(0.5); }
            20% { opacity: 1; transform: scale(1); }
            60% { opacity: 1; transform: scale(1); } /* Stay visible for a bit */
            70% { opacity: 0; transform: scale(0); } /* Pop out */
            100% { opacity: 0; transform: scale(0); }
        }

        /* --- 3. Check Icon (Entry & Draw) --- */
        .person-mode-check {
            color: white !important; 
            opacity: 0; 
            transform: scale(0);
            /* Pop in at 1.1s */
            animation: checkPopIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) 1.1s forwards;
        }

        @keyframes checkPopIn {
            0% { opacity: 0; transform: scale(0); }
            100% { opacity: 1; transform: scale(1); }
        }

        .person-mode-check path {
            stroke-dasharray: 20; 
            stroke-dashoffset: 20;
            /* Start drawing at 1.4s */
            animation: drawVariableSpeed 0.4s linear 1.4s forwards;
        }
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
<div class="relative flex h-screen w-full">
<!-- Collapsible SideNavBar -->
<?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
<!-- Header Bar -->
<header class="fixed top-0 left-0 right-0 z-10 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
<div class="flex items-center gap-4">
<input class="peer hidden" id="nav-toggle" type="checkbox"/>
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
<button id="profile-dropdown-btn" class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors" title="Profile">
<span class="material-symbols-outlined text-xl">person</span>
<span class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['username']); ?></span>
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
<!-- Form Content -->
<main class="flex-1 overflow-y-auto p-4 md:p-8 pb-40">
<div class="mx-auto max-w-7xl">
<!-- Multi-step Form Container -->
<div id="marine-form-container">
                    <!-- Page Title -->
<div class="mb-6">
<h1 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Berth Request Form</h1>
</div>

<!-- Step Indicator -->
<div class="mb-8 retro-progress">
<div class="flex items-center justify-center">
<div class="flex items-center">
<span class="retro-step-number active">01</span>
<div class="flex flex-col ml-3">
<span class="retro-step-text active uppercase">Berth</span>
<span class="retro-step-text-secondary active uppercase">Request</span>
</div>
</div>

<div class="flex-1 mx-4">
<div class="h-0.5 bg-gray-400 dark:bg-gray-600"></div>
</div>

<div class="flex items-center">
<span class="retro-step-number">02</span>
<div class="flex flex-col ml-3">
<span class="retro-step-text uppercase">Crew</span>
<span class="retro-step-text-secondary uppercase">Transfer</span>
</div>
</div>

<div class="flex-1 mx-4">
<div class="h-0.5 bg-gray-400 dark:bg-gray-600"></div>
</div>

<div class="flex items-center">
<span class="retro-step-number">03</span>
<div class="flex flex-col ml-3">
<span class="retro-step-text uppercase">Fuel</span>
<span class="retro-step-text-secondary uppercase">Water</span>
</div>
</div>

<div class="flex-1 mx-4">
<div class="h-0.5 bg-gray-400 dark:bg-gray-600"></div>
</div>

<div class="flex items-center">
<span class="retro-step-number">04</span>
<div class="flex flex-col ml-3">
<span class="retro-step-text uppercase">General</span>
<span class="retro-step-text-secondary uppercase">Works</span>
</div>
</div>

<div class="flex-1 mx-4">
<div class="h-0.5 bg-gray-400 dark:bg-gray-600"></div>
</div>

<div class="flex items-center">
<span class="retro-step-number">05</span>
<div class="flex flex-col ml-3">
<span class="retro-step-text uppercase">Review</span>
<span class="retro-step-text-secondary uppercase">Details</span>
</div>
</div>
</div>
</div>

<!-- Step 1: Berth Request Form -->
<div id="step-1" class="step-content">
<form id="berth-request-form" class="space-y-6 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Vessel Name</p>
<select id="vessel-name" class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
    <option value="" disabled selected>Select Vessel</option>
    <?php foreach ($vessels as $vessel): ?>
        <option value="<?php echo htmlspecialchars($vessel['vessel_name']); ?>">
            <?php echo htmlspecialchars($vessel['vessel_name']); ?>
        </option>
    <?php endforeach; ?>
</select>
</label>
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">PO Number</p>
<input id="po-number" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="e.g., PO12345" type="text" required/>
</label>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <!-- ETA Field -->
    <label class="flex flex-col">
        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">ETA</p>
        <div class="flex gap-2">
            <input id="eta" class="form-input flex-1 rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date" required/>
            <select id="eta-time" class="form-select flex-1 rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" required>
                <option value="" disabled selected>Time</option>
            </select>
        </div>
    </label>
    <!-- ETD Field -->
    <label class="flex flex-col">
        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">ETD</p>
        <div class="flex gap-2">
            <input id="etd" class="form-input flex-1 rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date" required/>
            <select id="etd-time" class="form-select flex-1 rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" required>
                <option value="" disabled selected>Time</option>
            </select>
        </div>
    </label>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <label class="flex flex-col">
        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Remarks or Special Instructions</p>
        <textarea id="remarks" class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="Add any additional notes..." rows="4"></textarea>
    </label>
    <div id="assign-agent-container" class="hidden flex-col">
        <label class="flex flex-col">
            <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Assign to Agent</p>
            <select id="assigned-agent" class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
                <option value="">Unassigned</option>
            </select>
        </label>
    </div>
</div>
<!-- Form Actions -->
<div class="flex items-center justify-end gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
<button type="button" id="reset-btn" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Reset</button>
<button type="button" id="save-continue-btn" class="rounded-lg bg-[#212180] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212180]">Continue to Crew Transfer</button>
</div>
</form>
</div>

<!-- Step 2: Crew Transfer Form (hidden initially) -->
<div id="step-2" class="step-content hidden">
<!-- Crew Transfer form will be loaded here -->
</div>

<!-- Step 3: Fuel & Water Form (hidden initially) -->
<div id="step-3" class="step-content hidden">
<!-- Fuel & Water form will be loaded here -->
</div>

<!-- Step 4: General Works Form (hidden initially) -->
<div id="step-4" class="step-content hidden">
<!-- General Works form will be loaded here -->
</div>

<!-- Step 5: Review Details (hidden initially) -->
<div id="step-5" class="step-content hidden">
<!-- Review form will be loaded here -->
</div>
<!-- Edit Crew Modal -->
<div id="edit-crew-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
<div class="flex items-center justify-center min-h-screen p-4">
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
<div class="p-6 space-y-4">
<h3 class="text-lg font-bold text-[#212529] dark:text-gray-200">Edit Crew Member</h3>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Name</span>
<input id="edit-crew-name" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">IC/Passport</span>
<input id="edit-crew-ic" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Company</span>
<input id="edit-crew-company" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Destination</span>
<input id="edit-crew-destination" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
</div>
<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Mobile Number</span>
<input id="edit-crew-mobile" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Nationality</span>
<input id="edit-crew-nationality" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
<label class="flex flex-col">
<span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Passport Expiry</span>
<input id="edit-crew-passport-expiry" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
</label>
</div>
<div class="flex justify-end gap-2 pt-4">
<button id="cancel-edit" class="rounded-lg px-4 py-2 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Cancel</button>
<button id="save-edit" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-opacity-90">Save</button>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
    </div>
    <script>
function updateTime() {
    const date = new Date();
    document.getElementById('date').innerText = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}).toUpperCase();
    document.getElementById('time').innerText = date.toLocaleTimeString('en-US', {hour12: false});
}
setInterval(updateTime, 1000);
updateTime(); // initial

// Persist toggle states globally across all pages
const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

function loadToggleStates() {
    toggles.forEach(id => {
        const state = localStorage.getItem(id);
        const element = document.getElementById(id);
        if (state !== null && element) {
            element.checked = state === 'true';
        }
    });
}

function saveToggleState(id) {
    const checkbox = document.getElementById(id);
    if (checkbox) {
        localStorage.setItem(id, checkbox.checked);
    }
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

setupToggle('crew-transfer-toggle', 'crew-transfer-submenu', 'expand-icon-crew-transfer-toggle');
setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

// Initialize forms and step navigation
let currentStep = 1;

// Global data storage for the marine request
let pendingDeletionIndexes = [];
let marineData = {
    berthRequest: {},
    crewTransfer: {},
    fuelWaterRequests: [],
    generalWorks: []
};

// Function to show the specified step
function showStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));

    // Show target step
    document.getElementById(`step-${stepNumber}`).classList.remove('hidden');

    // Update step indicator
    updateStepIndicator(stepNumber);

    // Update page title and heading based on step
    updatePageTitleAndHeading(stepNumber);

    currentStep = stepNumber;
}

function updatePageTitleAndHeading(stepNumber) {
    const pageTitle = document.querySelector('#marine-form-container h1');
    if (stepNumber === 1) {
        document.title = 'Berth Request Form';
        if (pageTitle) pageTitle.textContent = 'Berth Request Form';
    } else if (stepNumber === 2) {
        document.title = 'Crew Transfer Form';
        if (pageTitle) pageTitle.textContent = 'Crew Transfer Form';
    } else if (stepNumber === 3) {
        document.title = 'Fuel & Water Request Form';
        if (pageTitle) pageTitle.textContent = 'Fuel & Water Request Form';
    } else if (stepNumber === 4) {
        document.title = 'General Works Form';
        if (pageTitle) pageTitle.textContent = 'General Works Form';
    } else if (stepNumber === 5) {
        document.title = 'Review Details Form';
        if (pageTitle) pageTitle.textContent = 'Review Details Form';
    }
}

function updateStepIndicator(activeStep) {
    // Update step numbers
    document.querySelectorAll('.retro-step-number').forEach((stepNum, index) => {
        const stepNumber = index + 1;
        if (stepNumber === activeStep) {
            stepNum.classList.add('active');
        } else {
            stepNum.classList.remove('active');
        }
    });

    // Update step text
    document.querySelectorAll('.retro-step-text, .retro-step-text-secondary').forEach((stepText, index) => {
        const stepNumber = Math.floor(index / 2) + 1;
        if (stepNumber === activeStep) {
            stepText.classList.add('active');
        } else {
            stepText.classList.remove('active');
        }
    });
}

// Initialize form event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Button event listeners
    const resetBtn = document.getElementById('reset-btn');
    const saveContinueBtn = document.getElementById('save-continue-btn');

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            document.getElementById('berth-request-form').reset();
        });
    }

    // Date and Time Validation Logic
    // Date and Time Validation Logic
    const etaDateInput = document.getElementById('eta');
    const etaTimeInput = document.getElementById('eta-time');
    const etdDateInput = document.getElementById('etd');
    const etdTimeInput = document.getElementById('etd-time');

    function generateTimeOptions(selectElement) {
        // Clear existing options except the first one
        while (selectElement.options.length > 1) {
            selectElement.remove(1);
        }
        for (let i = 0; i < 24; i++) {
            const hour = String(i).padStart(2, '0');
            const time = `${hour}:00`;
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            selectElement.appendChild(option);
        }
    }

    // Initialize time options
    if (etaTimeInput) generateTimeOptions(etaTimeInput);
    if (etdTimeInput) generateTimeOptions(etdTimeInput);

    function updateMinDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayDate = `${year}-${month}-${day}`;
        const currentHour = now.getHours();
        const currentMinutes = now.getMinutes();

        // 1. Update ETA Min Date
        if (etaDateInput) etaDateInput.min = todayDate;

        // 2. Update ETA Time Options
        if (etaDateInput && etaTimeInput) {
            const options = etaTimeInput.options;
            if (etaDateInput.value === todayDate) {
                for (let i = 1; i < options.length; i++) {
                    const hour = parseInt(options[i].value.split(':')[0]);
                    // Strict check: if hour < currentHour, it's past.
                    // If hour == currentHour, it's past if minutes > 0.
                    if (hour < currentHour || (hour === currentHour && currentMinutes > 0)) {
                        options[i].disabled = true;
                        // options[i].style.display = 'none'; // Keep visible but disabled as requested
                        options[i].style.color = '#999'; // Visual gray out
                    } else {
                        options[i].disabled = false;
                        options[i].style.display = '';
                        options[i].style.color = '';
                    }
                }
                // If selected time is now disabled, reset it
                if (etaTimeInput.value) {
                    const selectedHour = parseInt(etaTimeInput.value.split(':')[0]);
                    if (selectedHour < currentHour || (selectedHour === currentHour && currentMinutes > 0)) {
                        etaTimeInput.value = '';
                    }
                }
            } else {
                // Enable all options if future date
                for (let i = 1; i < options.length; i++) {
                    options[i].disabled = false;
                    options[i].style.display = '';
                    options[i].style.color = '';
                }
            }
        }

        // 3. Update ETD Min Date
        if (etdDateInput) {
            if (etaDateInput.value) {
                etdDateInput.min = etaDateInput.value;
            } else {
                etdDateInput.min = todayDate;
            }
        }

        // 4. Update ETD Time Options
        if (etdDateInput && etdTimeInput) {
            const options = etdTimeInput.options;
            let minHour = -1;

            // Determine strict minimum hour based on context
            if (etdDateInput.value === todayDate) {
                minHour = currentHour;
            }

            // If ETD is same day as ETA, ETD must be > ETA
            if (etaDateInput.value && etaTimeInput.value && etdDateInput.value === etaDateInput.value) {
                const etaHour = parseInt(etaTimeInput.value.split(':')[0]);
                if (etaHour >= minHour) {
                    minHour = etaHour; 
                }
            }

            for (let i = 1; i < options.length; i++) {
                const hour = parseInt(options[i].value.split(':')[0]);
                let isDisabled = false;
                
                // Check against Today/Current Time
                if (etdDateInput.value === todayDate) {
                     if (hour < currentHour || (hour === currentHour && currentMinutes > 0)) isDisabled = true;
                }

                // Check against ETA if same day
                if (etaDateInput.value && etaTimeInput.value && etdDateInput.value === etaDateInput.value) {
                    const etaHour = parseInt(etaTimeInput.value.split(':')[0]);
                    // ETD must be > ETA. So if hour <= etaHour, disable.
                    if (hour <= etaHour) isDisabled = true;
                }

                if (isDisabled) {
                    options[i].disabled = true;
                    options[i].style.color = '#999';
                } else {
                    options[i].disabled = false;
                    options[i].style.color = '';
                }
            }
            
            // Validate current selection
            if (etdTimeInput.value) {
                const selectedHour = parseInt(etdTimeInput.value.split(':')[0]);
                let isInvalid = false;
                if (etdDateInput.value === todayDate) {
                    if (selectedHour < currentHour || (selectedHour === currentHour && currentMinutes > 0)) isInvalid = true;
                }
                if (etaDateInput.value && etaTimeInput.value && etdDateInput.value === etaDateInput.value) {
                    const etaHour = parseInt(etaTimeInput.value.split(':')[0]);
                    if (selectedHour <= etaHour) isInvalid = true;
                }
                
                if (isInvalid) {
                    etdTimeInput.value = '';
                }
            }
        }
    }



    function validateDateTime() {
        updateMinDateTime();
        // Additional validation if needed, but UI constraints should handle most.
    }

    if (etaDateInput) {
        etaDateInput.addEventListener('change', validateDateTime);
        etaDateInput.addEventListener('focus', updateMinDateTime);
    }
    if (etaTimeInput) {
        etaTimeInput.addEventListener('change', validateDateTime);
        etaTimeInput.addEventListener('focus', updateMinDateTime);
    }

    if (etdDateInput) {
        etdDateInput.addEventListener('change', validateDateTime);
        etdDateInput.addEventListener('focus', updateMinDateTime);
    }
    if (etdTimeInput) {
        etdTimeInput.addEventListener('change', validateDateTime);
        etdTimeInput.addEventListener('focus', updateMinDateTime);
    }
    
    // Initial call
    updateMinDateTime();

    if (saveContinueBtn) {
        saveContinueBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent any form submission

            const isValid = validateBerthRequestForm();


            if (isValid) {
                saveBerthRequestData();
                loadCrewTransferForm();
                showStep(2);
            } else {
                // Form validation failed
            }
        });
    }

    // Also ensure the button is visible and clickable
    const step1Container = document.getElementById('step-1');
    if (step1Container && step1Container.classList.contains('hidden') === false) {
        // Step 1 is visible
    }
});

function loadCrewTransferForm() {
    const step2Container = document.getElementById('step-2');

    step2Container.innerHTML = `
        <div class="space-y-6 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
            <div class="space-y-4">
                <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Crew Transfer Type</h3>
                <div class="pt-2">
                    <div class="flex gap-6">
                        <label class="flex items-center">
                            <input type="radio" id="crew-sign-on" name="crew-transfer-type" value="sign_on" class="form-radio text-primary focus:ring-primary border-gray-300">
                            <span class="ml-2 text-sm font-medium text-[#212529] dark:text-gray-300">Sign On</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" id="crew-sign-off" name="crew-transfer-type" value="sign_off" class="form-radio text-primary focus:ring-primary border-gray-300">
                            <span class="ml-2 text-sm font-medium text-[#212529] dark:text-gray-300">Sign Off</span>
                        </label>
                    </div>
                </div>
            </div>

            <div id="crew-other-services-section" class="space-y-4" style="display: none;">
                <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Other Services</h3>
                <div class="pt-2 space-y-4" id="other-services-fields">
                    <!-- Sign On Other Services -->
                    <div id="sign-on-services" style="display: none;">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <label class="flex flex-col">
                                <div class="flex items-center gap-3 mb-2">
                                    <input id="packed-meals-checkbox" type="checkbox" class="form-checkbox rounded"/>
                                    <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Packed Meals</span>
                                </div>
                                <input id="packed-meals" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-gray-100 px-3 py-2 text-sm text-gray-500 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" type="number" min="0" disabled/>
                            </label>
                            <label class="flex flex-col">
                                <div class="flex items-center gap-3 mb-2">
                                    <input id="snack-pack-checkbox" type="checkbox" class="form-checkbox rounded"/>
                                    <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Snack Pack</span>
                                </div>
                                <input id="snack-pack" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-gray-100 px-3 py-2 text-sm text-gray-500 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" type="number" min="0" disabled/>
                            </label>
                            <label class="flex flex-col">
                                <div class="flex items-center gap-3 mb-2">
                                    <input id="baggage-checkbox" type="checkbox" class="form-checkbox rounded"/>
                                    <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Baggage Handling</span>
                                </div>
                                <input id="baggage" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-gray-100 px-3 py-2 text-sm text-gray-500 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" type="number" min="0" disabled/>
                            </label>
                            <label class="flex flex-col">
                                <div class="flex items-center gap-3 mb-2">
                                    <input id="bag-tagging-checkbox" type="checkbox" class="form-checkbox rounded"/>
                                    <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Bag Tagging</span>
                                </div>
                                <input id="bag-tagging" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-gray-100 px-3 py-2 text-sm text-gray-500 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" type="number" min="0" disabled/>
                            </label>
                        </div>
                    </div>
                    <!-- Sign Off Other Services -->
                    <div id="sign-off-services" style="display: none;">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <label class="flex flex-col">
                                <div class="flex items-center gap-3 mb-2">
                                    <input id="takeaway-checkbox" type="checkbox" class="form-checkbox rounded"/>
                                    <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Takeaway</span>
                                </div>
                                <input id="takeaway" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-gray-100 px-3 py-2 text-sm text-gray-500 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" type="number" min="0" disabled/>
                            </label>
                            <label class="flex flex-col">
                                <div class="flex items-center gap-3 mb-2">
                                    <input id="baggage-handling-checkbox" type="checkbox" class="form-checkbox rounded"/>
                                    <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Baggage Handling</span>
                                </div>
                                <input id="baggage-handling" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-gray-100 px-3 py-2 text-sm text-gray-500 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" type="number" min="0" disabled/>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="crew-form-section" class="space-y-4" style="display: none;">
                <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Crew Details</h3>
                <div class="pt-2 space-y-4" id="crew-form">
                    <!-- Crew form will be loaded here -->
                </div>
                <div class="flex gap-2">
                    <button id="download-template" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background-color: #008700;" onmouseover="this.style.backgroundColor='#006600'" onmouseout="this.style.backgroundColor='#008700'">Download Template</button>
                    <button id="upload-excel-crew" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background-color: #008700;" onmouseover="this.style.backgroundColor='#006600'" onmouseout="this.style.backgroundColor='#008700'">Upload Excel</button>
                    <input type="file" id="excel-file-input" accept=".xlsx,.xls" class="hidden">
                    <button id="add-crew-member" class="rounded-lg bg-[#212121] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90">Add Crew</button>
                </div>
                <div class="space-y-4" id="crew-list-section" style="display: none;">
                    <h4 class="border-b border-[#DEE2E6] pb-2 text-base font-semibold text-[#212529] dark:border-gray-700 dark:text-gray-200">Crew Member List</h4>
                    <div id="crew-list"></div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
                <button type="button" id="back-to-berth-crew" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Back</button>
                <div class="flex gap-4">
                    <button type="button" id="skip-crew" class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90">Skip Crew Transfer</button>
<button type="button" id="continue-to-fuel-water" class="rounded-lg bg-[#212180] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90">Continue to Fuel/Water</button>
                </div>
            </div>
        </div>
    `;

    // Add event listeners
    setupCrewTransferListeners();
}

function setupCrewTransferListeners() {
    const signOnRadio = document.getElementById('crew-sign-on');
    const signOffRadio = document.getElementById('crew-sign-off');
    const crewOtherServicesSection = document.getElementById('crew-other-services-section');
    const crewFormSection = document.getElementById('crew-form-section');

    function toggleCrewSections() {
        const selectedType = signOnRadio.checked ? 'sign_on' : signOffRadio.checked ? 'sign_off' : null;

        if (selectedType) {
            // Show other services section
            crewOtherServicesSection.style.display = 'block';

            // Show the appropriate service fields
            document.getElementById('sign-on-services').style.display = selectedType === 'sign_on' ? 'block' : 'none';
            document.getElementById('sign-off-services').style.display = selectedType === 'sign_off' ? 'block' : 'none';

            // Show crew form section
            crewFormSection.style.display = 'block';
            loadCrewForm(selectedType);

            // Setup service checkbox event listeners
            setupServiceCheckboxListeners();
        } else {
            // Hide both sections
            crewOtherServicesSection.style.display = 'none';
            crewFormSection.style.display = 'none';
        }
    }

    function setupServiceCheckboxListeners() {


        // Sign-on services checkboxes
        setupServiceCheckbox('packed-meals-checkbox', 'packed-meals');
        setupServiceCheckbox('snack-pack-checkbox', 'snack-pack');
        setupServiceCheckbox('baggage-checkbox', 'baggage');
        setupServiceCheckbox('bag-tagging-checkbox', 'bag-tagging');

        // Sign-off services checkboxes
        setupServiceCheckbox('takeaway-checkbox', 'takeaway');
        setupServiceCheckbox('baggage-handling-checkbox', 'baggage-handling');
    }

function setupServiceCheckbox(checkboxId, inputId) {
    const checkbox = document.getElementById(checkboxId);
    const input = document.getElementById(inputId);

    if (checkbox && input) {
        const updateField = () => {
            if (checkbox.checked) {
                input.disabled = false;
                input.removeAttribute('disabled');
                input.style.cursor = 'text';
                input.style.opacity = '1';
                
                // Ensure it stays as number type when checked
                input.type = 'number';
                
                input.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-gray-800', 'dark:text-gray-400');
                input.classList.add('bg-background-light', 'text-[#212529]', 'dark:bg-gray-900/50', 'dark:text-gray-200');
            } else {
                input.disabled = true;
                input.setAttribute('disabled', 'disabled');
                input.value = '';
                input.style.cursor = 'not-allowed';
                input.style.opacity = '0.6';
                
                // Keep it as number type even when disabled
                input.type = 'number';
                
                input.classList.remove('bg-background-light', 'text-[#212529]', 'dark:bg-gray-900/50', 'dark:text-gray-200');
                input.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-gray-800', 'dark:text-gray-400');
            }
        };

        checkbox.addEventListener('change', updateField);
        updateField(); // initial state
    }
}

    signOnRadio.addEventListener('change', toggleCrewSections);
    signOffRadio.addEventListener('change', toggleCrewSections);

    // Initial call in case one is already checked (page reload)
    toggleCrewSections();

    document.getElementById('back-to-berth-crew').addEventListener('click', function() {
        showStep(1);
    });

    document.getElementById('skip-crew').addEventListener('click', function() {
        marineData.crewTransfer = {};
        loadFuelWaterForm();
        showStep(3);
    });

    document.getElementById('continue-to-fuel-water').addEventListener('click', function() {
        const selectedType = document.querySelector('input[name="crew-transfer-type"]:checked');
        if (!selectedType) {
            alert('Please select a crew transfer type.');
            return;
        }

        saveCrewTransferData();
        loadFuelWaterForm();
        showStep(3);
    });
}

function loadCrewForm(type) {
    const crewForm = document.getElementById('crew-form');
    crewForm.innerHTML = `
        <!-- Row 1: Name (Full Width) -->
        <div class="flex flex-col">
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Name</span>
                <input id="crew-name" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
            </label>
        </div>

        <!-- Row 2: IC/Passport and Passport Expiry -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">IC/Passport</span>
                <input id="crew-ic" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
            </label>
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Passport Expiry</span>
                <input id="crew-passport-expiry" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
            </label>
        </div>

        <!-- Row 3: Mobile Number and Nationality -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Mobile Number</span>
                <input id="crew-mobile" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
            </label>
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Nationality</span>
                <select id="crew-nationality" class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
                    <option value="">Select Nationality</option>
                    <option value="Afghanistan">Afghanistan</option>
                    <option value="Albania">Albania</option>
                    <option value="Algeria">Algeria</option>
                    <option value="American Samoa">American Samoa</option>
                    <option value="Andorra">Andorra</option>
                    <option value="Angola">Angola</option>
                    <option value="Anguilla">Anguilla</option>
                    <option value="Antarctica">Antarctica</option>
                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                    <option value="Argentina">Argentina</option>
                    <option value="Armenia">Armenia</option>
                    <option value="Aruba">Aruba</option>
                    <option value="Australia">Australia</option>
                    <option value="Austria">Austria</option>
                    <option value="Azerbaijan">Azerbaijan</option>
                    <option value="Bahamas">Bahamas</option>
                    <option value="Bahrain">Bahrain</option>
                    <option value="Bangladesh">Bangladesh</option>
                    <option value="Barbados">Barbados</option>
                    <option value="Belarus">Belarus</option>
                    <option value="Belgium">Belgium</option>
                    <option value="Belize">Belize</option>
                    <option value="Benin">Benin</option>
                    <option value="Bermuda">Bermuda</option>
                    <option value="Bhutan">Bhutan</option>
                    <option value="Bolivia">Bolivia</option>
                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                    <option value="Botswana">Botswana</option>
                    <option value="Brazil">Brazil</option>
                    <option value="Brunei">Brunei</option>
                    <option value="Bulgaria">Bulgaria</option>
                    <option value="Burkina Faso">Burkina Faso</option>
                    <option value="Burundi">Burundi</option>
                    <option value="Cambodia">Cambodia</option>
                    <option value="Cameroon">Cameroon</option>
                    <option value="Canada">Canada</option>
                    <option value="Cape Verde">Cape Verde</option>
                    <option value="Cayman Islands">Cayman Islands</option>
                    <option value="Central African Republic">Central African Republic</option>
                    <option value="Chad">Chad</option>
                    <option value="Chile">Chile</option>
                    <option value="China">China</option>
                    <option value="Christmas Island">Christmas Island</option>
                    <option value="Colombia">Colombia</option>
                    <option value="Comoros">Comoros</option>
                    <option value="Congo">Congo</option>
                    <option value="Costa Rica">Costa Rica</option>
                    <option value="Croatia">Croatia</option>
                    <option value="Cuba">Cuba</option>
                    <option value="Cyprus">Cyprus</option>
                    <option value="Czech Republic">Czech Republic</option>
                    <option value="Denmark">Denmark</option>
                    <option value="Djibouti">Djibouti</option>
                    <option value="Dominica">Dominica</option>
                    <option value="Dominican Republic">Dominican Republic</option>
                    <option value="East Timor">East Timor</option>
                    <option value="Ecuador">Ecuador</option>
                    <option value="Egypt">Egypt</option>
                    <option value="El Salvador">El Salvador</option>
                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                    <option value="Eritrea">Eritrea</option>
                    <option value="Estonia">Estonia</option>
                    <option value="Ethiopia">Ethiopia</option>
                    <option value="Falkland Islands">Falkland Islands</option>
                    <option value="Faroe Islands">Faroe Islands</option>
                    <option value="Fiji">Fiji</option>
                    <option value="Finland">Finland</option>
                    <option value="France">France</option>
                    <option value="French Guiana">French Guiana</option>
                    <option value="French Polynesia">French Polynesia</option>
                    <option value="French Southern Territories">French Southern Territories</option>
                    <option value="Gabon">Gabon</option>
                    <option value="Gambia">Gambia</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Germany">Germany</option>
                    <option value="Ghana">Ghana</option>
                    <option value="Gibraltar">Gibraltar</option>
                    <option value="Greece">Greece</option>
                    <option value="Greenland">Greenland</option>
                    <option value="Grenada">Grenada</option>
                    <option value="Guadeloupe">Guadeloupe</option>
                    <option value="Guam">Guam</option>
                    <option value="Guatemala">Guatemala</option>
                    <option value="Guinea">Guinea</option>
                    <option value="Guinea-Bissau">Guinea-Bissau</option>
                    <option value="Guyana">Guyana</option>
                    <option value="Haiti">Haiti</option>
                    <option value="Honduras">Honduras</option>
                    <option value="Hong Kong">Hong Kong</option>
                    <option value="Hungary">Hungary</option>
                    <option value="Iceland">Iceland</option>
                    <option value="India">India</option>
                    <option value="Indonesia">Indonesia</option>
                    <option value="Iran">Iran</option>
                    <option value="Iraq">Iraq</option>
                    <option value="Ireland">Ireland</option>
                    <option value="Israel">Israel</option>
                    <option value="Italy">Italy</option>
                    <option value="Jamaica">Jamaica</option>
                    <option value="Japan">Japan</option>
                    <option value="Jordan">Jordan</option>
                    <option value="Kazakhstan">Kazakhstan</option>
                    <option value="Kenya">Kenya</option>
                    <option value="Kiribati">Kiribati</option>
                    <option value="Korea, North">Korea, North</option>
                    <option value="Korea, South">Korea, South</option>
                    <option value="Kuwait">Kuwait</option>
                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                    <option value="Laos">Laos</option>
                    <option value="Latvia">Latvia</option>
                    <option value="Lebanon">Lebanon</option>
                    <option value="Lesotho">Lesotho</option>
                    <option value="Liberia">Liberia</option>
                    <option value="Libya">Libya</option>
                    <option value="Liechtenstein">Liechtenstein</option>
                    <option value="Lithuania">Lithuania</option>
                    <option value="Luxembourg">Luxembourg</option>
                    <option value="Macau">Macau</option>
                    <option value="Madagascar">Madagascar</option>
                    <option value="Malawi">Malawi</option>
                    <option value="Malaysia">Malaysia</option>
                    <option value="Maldives">Maldives</option>
                    <option value="Mali">Mali</option>
                    <option value="Malta">Malta</option>
                    <option value="Marshall Islands">Marshall Islands</option>
                    <option value="Martinique">Martinique</option>
                    <option value="Mauritania">Mauritania</option>
                    <option value="Mauritius">Mauritius</option>
                    <option value="Mayotte">Mayotte</option>
                    <option value="Mexico">Mexico</option>
                    <option value="Micronesia">Micronesia</option>
                    <option value="Moldova">Moldova</option>
                    <option value="Monaco">Monaco</option>
                    <option value="Mongolia">Mongolia</option>
                    <option value="Montserrat">Montserrat</option>
                    <option value="Morocco">Morocco</option>
                    <option value="Mozambique">Mozambique</option>
                    <option value="Namibia">Namibia</option>
                    <option value="Nauru">Nauru</option>
                    <option value="Nepal">Nepal</option>
                    <option value="Netherlands">Netherlands</option>
                    <option value="Netherlands Antilles">Netherlands Antilles</option>
                    <option value="New Caledonia">New Caledonia</option>
                    <option value="New Zealand">New Zealand</option>
                    <option value="Nicaragua">Nicaragua</option>
                    <option value="Niger">Niger</option>
                    <option value="Nigeria">Nigeria</option>
                    <option value="Niue">Niue</option>
                    <option value="Norfolk Island">Norfolk Island</option>
                    <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                    <option value="Norway">Norway</option>
                    <option value="Oman">Oman</option>
                    <option value="Pakistan">Pakistan</option>
                    <option value="Palau">Palau</option>
                    <option value="Panama">Panama</option>
                    <option value="Papua New Guinea">Papua New Guinea</option>
                    <option value="Paraguay">Paraguay</option>
                    <option value="Peru">Peru</option>
                    <option value="Philippines">Philippines</option>
                    <option value="Pitcairn">Pitcairn</option>
                    <option value="Poland">Poland</option>
                    <option value="Portugal">Portugal</option>
                    <option value="Puerto Rico">Puerto Rico</option>
                    <option value="Qatar">Qatar</option>
                    <option value="Reunion">Reunion</option>
                    <option value="Romania">Romania</option>
                    <option value="Russia">Russia</option>
                    <option value="Rwanda">Rwanda</option>
                    <option value="Saint Helena">Saint Helena</option>
                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                    <option value="Saint Lucia">Saint Lucia</option>
                    <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                    <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                    <option value="Samoa">Samoa</option>
                    <option value="San Marino">San Marino</option>
                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                    <option value="Saudi Arabia">Saudi Arabia</option>
                    <option value="Senegal">Senegal</option>
                    <option value="Serbia and Montenegro">Serbia and Montenegro</option>
                    <option value="Seychelles">Seychelles</option>
                    <option value="Sierra Leone">Sierra Leone</option>
                    <option value="Singapore">Singapore</option>
                    <option value="Slovak Republic">Slovak Republic</option>
                    <option value="Slovenia">Slovenia</option>
                    <option value="Solomon Islands">Solomon Islands</option>
                    <option value="Somalia">Somalia</option>
                    <option value="South Africa">South Africa</option>
                    <option value="Spain">Spain</option>
                    <option value="Sri Lanka">Sri Lanka</option>
                    <option value="Sudan">Sudan</option>
                    <option value="Suriname">Suriname</option>
                    <option value="Swaziland">Swaziland</option>
                    <option value="Sweden">Sweden</option>
                    <option value="Switzerland">Switzerland</option>
                    <option value="Syria">Syria</option>
                    <option value="Taiwan">Taiwan</option>
                    <option value="Tajikistan">Tajikistan</option>
                    <option value="Tanzania">Tanzania</option>
                    <option value="Thailand">Thailand</option>
                    <option value="The Former Yugoslav Republic of Macedonia">The Former Yugoslav Republic of Macedonia</option>
                    <option value="Togo">Togo</option>
                    <option value="Tokelau">Tokelau</option>
                    <option value="Tonga">Tonga</option>
                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                    <option value="Tunisia">Tunisia</option>
                    <option value="Turkey">Turkey</option>
                    <option value="Turkmenistan">Turkmenistan</option>
                    <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                    <option value="Tuvalu">Tuvalu</option>
                    <option value="Uganda">Uganda</option>
                    <option value="Ukraine">Ukraine</option>
                    <option value="United Arab Emirates">United Arab Emirates</option>
                    <option value="United Kingdom">United Kingdom</option>
                    <option value="United States">United States</option>
                    <option value="Uruguay">Uruguay</option>
                    <option value="Uzbekistan">Uzbekistan</option>
                    <option value="Vanuatu">Vanuatu</option>
                    <option value="Vatican City">Vatican City</option>
                    <option value="Venezuela">Venezuela</option>
                    <option value="Vietnam">Vietnam</option>
                    <option value="Virgin Islands (British)">Virgin Islands (British)</option>
                    <option value="Virgin Islands (U.S.)">Virgin Islands (U.S.)</option>
                    <option value="Wallis and Futuna Islands">Wallis and Futuna Islands</option>
                    <option value="Western Sahara">Western Sahara</option>
                    <option value="Yemen">Yemen</option>
                    <option value="Zambia">Zambia</option>
                    <option value="Zimbabwe">Zimbabwe</option>
                </select>
            </label>
        </div>

        <!-- Row 4: Company and Destination -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Company</span>
                <input id="crew-company" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
            </label>
            <label class="flex flex-col">
                <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Destination</span>
                <input id="crew-destination" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
            </label>
        </div>
    `;

    // Only add Excel upload event listeners once per session to prevent duplicates
    if (!window.excelListenersAttached) {
        // Download template functionality
        document.getElementById('download-template').addEventListener('click', function() {
            downloadCrewTemplate();
        });

        // Excel upload functionality
        document.getElementById('upload-excel-crew').addEventListener('click', function() {
            document.getElementById('excel-file-input').click();
        });

        document.getElementById('excel-file-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handleExcelUpload(file);
            }
        });

        window.excelListenersAttached = true;
    }

document.getElementById('add-crew-member').addEventListener('click', function() {
    // Debug: Check if fields exist
    console.log('Name field:', document.getElementById('crew-name'));
    console.log('IC field:', document.getElementById('crew-ic'));
    console.log('Name value:', document.getElementById('crew-name')?.value);
    console.log('IC value:', document.getElementById('crew-ic')?.value);

    const nameInput = document.getElementById('crew-name');
    const icInput = document.getElementById('crew-ic');
    
    const nameValue = nameInput ? nameInput.value.trim() : '';
    const icValue = icInput ? icInput.value.trim() : '';
    
    // More robust validation
    if (!nameValue) {
        alert('Please enter crew member name.');
        nameInput?.focus();
        return;
    }
    
    if (!icValue) {
        alert('Please enter IC/Passport number.');
        icInput?.focus();
        return;
    }
    
    const crewData = {
        name: nameValue,
        ic: icValue,
        mobile: document.getElementById('crew-mobile')?.value.trim() || '',
        nationality: document.getElementById('crew-nationality')?.value.trim() || '',
        passportExpiry: document.getElementById('crew-passport-expiry')?.value || '',
        company: document.getElementById('crew-company')?.value.trim() || '',
        destination: document.getElementById('crew-destination')?.value.trim() || ''
    };

    if (!marineData.crewTransfer.crewList) {
        marineData.crewTransfer.crewList = [];
    }
    
    marineData.crewTransfer.crewList.push(crewData);
    renderCrewList();
    clearCrewForm();
});
}

function clearCrewForm() {
    document.getElementById('crew-name').value = '';
    document.getElementById('crew-ic').value = '';
    document.getElementById('crew-mobile').value = '';
    document.getElementById('crew-nationality').value = '';
    document.getElementById('crew-passport-expiry').value = '';
    document.getElementById('crew-company').value = '';
    document.getElementById('crew-destination').value = '';
}

function renderCrewList() {
    const crewListSection = document.getElementById('crew-list-section');
    const crewList = document.getElementById('crew-list');

    if (!marineData.crewTransfer.crewList || marineData.crewTransfer.crewList.length === 0) {
        crewListSection.style.display = 'none';
        return;
    }

    crewListSection.style.display = 'block';

    let tableHTML = `
        <div class="overflow-x-auto border border-[#DEE2E6] rounded-lg dark:border-gray-700">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <input type="checkbox" id="select-all-crew" class="form-checkbox rounded">
                        </th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID/Passport</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nationality</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Destination</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Passport Expiry</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mobile</th>
                        <th class="px-1 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">`;

    marineData.crewTransfer.crewList.forEach((crew, index) => {
        // Format passport expiry date
        const expiryDate = crew.passportExpiry ? new Date(crew.passportExpiry).toLocaleDateString('en-US', {year: 'numeric', month: 'short'}) : 'N/A';

        // Filter patronyms and handle name display
        const fullName = crew.name || '';
        const patronymics = ['bin', 'binti', 'ibn', 'ibni', 'bt', 'anak', 'a/l', 'a/p', 's/o', 'd/o'];
        const nameParts = fullName.trim().split(/\s+/).filter(part => !patronymics.includes(part.toLowerCase()));
        const displayName = nameParts.length > 2 ? nameParts.slice(1, 3).join(' ') : nameParts.slice(0, 2).join(' ') || 'N/A';

        const rowClass = index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900';

        tableHTML += `
                    <tr class="${rowClass}">
                        <td class="px-1 py-4 whitespace-nowrap">
                            <input type="checkbox" class="row-crew-checkbox form-checkbox rounded" data-index="${index}">
                        </td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">${index + 1}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">${displayName}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.ic || 'N/A'}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.nationality || 'N/A'}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.company || 'N/A'}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.destination || 'N/A'}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${expiryDate}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.mobile || 'N/A'}</td>
                        <td class="px-1 py-4 whitespace-nowrap text-sm">
                            <button class="edit-crew-btn px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 font-medium" data-index="${index}">Edit</button>
                        </td>
                    </tr>`;
    });

    tableHTML += `
                </tbody>
            </table>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-b-lg">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        <span id="selected-count">0</span> of <span id="total-count">${marineData.crewTransfer.crewList.length}</span> selected
                    </div>
                    <div class="flex gap-2">
                        <button id="delete-selected" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed" disabled>Delete Selected</button>
                    </div>
                </div>
            </div>
        </div>`;

    crewList.innerHTML = tableHTML;

    // Add event listeners after rendering
    setupCrewCheckboxEventListeners();
}

function setupCrewCheckboxEventListeners() {
    const selectAllCheckbox = document.getElementById('select-all-crew');
    const deleteSelectedBtn = document.getElementById('delete-selected');
    const selectedCountEl = document.getElementById('selected-count');
    const totalCountEl = document.getElementById('total-count');

    // Update counts initially
    updateCrewCounts();

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.row-crew-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        updateCrewCounts();
        updateSelectAllState();
    });

    // Individual checkbox change
    document.querySelectorAll('.row-crew-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateCrewCounts();
        });
    });

    // Delete selected functionality
    deleteSelectedBtn.addEventListener('click', function() {
        const selectedIndexes = [];
        document.querySelectorAll('.row-crew-checkbox:checked').forEach(checkbox => {
            const index = parseInt(checkbox.getAttribute('data-index'));
            if (!isNaN(index)) {
                selectedIndexes.push(index);
            }
        });

        if (selectedIndexes.length === 0) {
            alert('Please select crew members to delete.');
            return;
        }

        // Store for confirmation
        pendingDeletionIndexes = selectedIndexes;
        
        // Show Custom Modal
        const deleteModal = document.getElementById('delete-confirmation-modal');
        const deleteModalTitle = document.getElementById('delete-modal-title');
        const deleteModalDesc = document.getElementById('delete-modal-description');
        
        // Update text
        deleteModalTitle.textContent = "Delete Crew?";
        deleteModalDesc.textContent = `Are you sure you want to delete ${selectedIndexes.length} crew member(s)? This action cannot be undone.`;
        
        deleteModal.classList.remove('hidden');
    });

    // Edit crew functionality
    document.querySelectorAll('.edit-crew-btn').forEach(button => {
        button.addEventListener('click', function() {
            editingIndex = parseInt(this.getAttribute('data-index'));
            openEditModal();
        });
    });

    function updateSelectAllState() {
        const totalCheckboxes = document.querySelectorAll('.row-crew-checkbox').length;
        const checkedCheckboxes = document.querySelectorAll('.row-crew-checkbox:checked').length;
        selectAllCheckbox.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
        selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
    }

    function updateCrewCounts() {
        const selectedCount = document.querySelectorAll('.row-crew-checkbox:checked').length;
        const totalCount = marineData.crewTransfer.crewList ? marineData.crewTransfer.crewList.length : 0;

        selectedCountEl.textContent = selectedCount;
        totalCountEl.textContent = totalCount;

        // Enable/disable delete button
        deleteSelectedBtn.disabled = selectedCount === 0;
    }
}

function removeCrewMember(index) {
    marineData.crewTransfer.crewList.splice(index, 1);
    renderCrewList();
}
function saveCrewTransferData() {
    const selectedType = document.querySelector('input[name="crew-transfer-type"]:checked').value;
    marineData.crewTransfer.type = selectedType;

    // Collect other services data
    const otherServices = [];
    if (selectedType === 'sign_on') {
        // Sign on services
        if (document.getElementById('packed-meals-checkbox').checked) {
            otherServices.push({
                service: 'packed_meals',
                quantity: parseInt(document.getElementById('packed-meals').value) || null
            });
        }
        if (document.getElementById('snack-pack-checkbox').checked) {
            otherServices.push({
                service: 'snack_pack',
                quantity: parseInt(document.getElementById('snack-pack').value) || null
            });
        }
        if (document.getElementById('baggage-checkbox').checked) {
            otherServices.push({
                service: 'baggage_handling', // Changed from 'baggage' to 'baggage_handling'
                quantity: parseInt(document.getElementById('baggage').value) || null // Changed from 'details' to 'quantity'
            });
        }
        if (document.getElementById('bag-tagging-checkbox').checked) {
            otherServices.push({
                service: 'bag_tagging',
                quantity: parseInt(document.getElementById('bag-tagging').value) || null
            });
        }
    } else if (selectedType === 'sign_off') {
        // Sign off services
        if (document.getElementById('takeaway-checkbox').checked) {
            otherServices.push({
                service: 'takeaway',
                quantity: parseInt(document.getElementById('takeaway').value) || null
            });
        }
        if (document.getElementById('baggage-handling-checkbox').checked) {
            otherServices.push({
                service: 'baggage_handling',
                quantity: parseInt(document.getElementById('baggage-handling').value) || null
            });
        }
    }

    marineData.crewTransfer.otherServices = otherServices;
    // crewList is already saved in marineData.crewTransfer.crewList
}

function saveBerthRequestData() {
    const agentSelect = document.getElementById('assigned-agent');
    marineData.berthRequest = {
        vesselName: document.getElementById('vessel-name').value.trim(),
        poNumber: document.getElementById('po-number').value.trim(),
        eta: (document.getElementById('eta').value && document.getElementById('eta-time').value) ? 
             `${document.getElementById('eta').value}T${document.getElementById('eta-time').value}` : '',
        etd: (document.getElementById('etd').value && document.getElementById('etd-time').value) ? 
             `${document.getElementById('etd').value}T${document.getElementById('etd-time').value}` : '',
        remarks: document.getElementById('remarks').value.trim(),
        assignedAgentId: agentSelect ? agentSelect.value : null,
        assignedAgentName: agentSelect && agentSelect.selectedIndex > 0 ? agentSelect.options[agentSelect.selectedIndex].text : 'Unassigned'
    };
}

function validateBerthRequestForm() {
    const vesselName = document.getElementById('vessel-name').value.trim();
    const poNumber = document.getElementById('po-number').value.trim();
    const eta = (document.getElementById('eta').value && document.getElementById('eta-time').value) ? 
                `${document.getElementById('eta').value}T${document.getElementById('eta-time').value}` : '';
    const etd = (document.getElementById('etd').value && document.getElementById('etd-time').value) ? 
                `${document.getElementById('etd').value}T${document.getElementById('etd-time').value}` : '';

    if (!vesselName || !poNumber || !eta || !etd) {
        alert('Please fill in all required fields.');
        return false;
    }

    return true;
}


function loadFuelWaterForm() {
    const step3Container = document.getElementById('step-3');

    step3Container.innerHTML = `
        <form id="marine-fuel-water-form" class="space-y-8 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
            <!-- Supply Items Selection -->
            <div class="space-y-4">
                <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Supply Items</h3>
                <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fuel Supply Card -->
                    <div class="border border-[#DEE2E6] rounded-lg p-6 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-3 mb-4">
                            <input type="checkbox" id="select-fuel" class="form-checkbox w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="text-base font-semibold text-[#212529] dark:text-gray-300">Fuel Supply</label>
                        </div>
                        <div class="space-y-4">
                            <label class="block">
                                <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Booking Time</span>
                                <input id="fuel-booking-time" class="mt-1 form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed" type="time" disabled/>
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Quantity</span>
                                <input id="fuel-quantity" class="mt-1 form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed" type="number" placeholder="Enter quantity in liters" disabled/>
                            </label>
                            <div class="hidden" id="fuel-remarks-container">
                                <label class="block text-sm font-medium text-[#212529] dark:text-gray-300 mb-1">Remarks</label>
                                <textarea id="fuel-remarks" class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary resize-none disabled:opacity-50 disabled:cursor-not-allowed" rows="2" placeholder="Add any specific instructions..." disabled></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Water Supply Card -->
                    <div class="border border-[#DEE2E6] rounded-lg p-6 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-3 mb-4">
                            <input type="checkbox" id="select-water" class="form-checkbox w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="text-base font-semibold text-[#212529] dark:text-gray-300">Water Supply</label>
                        </div>
                        <div class="space-y-4">
                            <label class="block">
                                <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Booking Time</span>
                                <input id="water-booking-time" class="mt-1 form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed" type="time" disabled/>
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-[#212529] dark:text-gray-300">Quantity</span>
                                <input id="water-quantity" class="mt-1 form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed" type="number" placeholder="Enter quantity in liters" disabled/>
                            </label>
                            <div class="hidden" id="water-remarks-container">
                                <label class="block text-sm font-medium text-[#212529] dark:text-gray-300 mb-1">Remarks</label>
                                <textarea id="water-remarks" class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary resize-none disabled:opacity-50 disabled:cursor-not-allowed" rows="2" placeholder="Add any specific instructions..." disabled></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
                <button type="button" id="back-to-berth" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Back</button>
                <div class="flex gap-4">
                    <button type="button" id="fw-reset" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Reset</button>
                    <button type="button" id="fw-preview" class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121]">Skip Fuel Water</button>
<button type="button" id="fw-save-submit" class="rounded-lg bg-[#212180] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212180]">Continue to General Works</button>
                </div>
            </div>
        </form>
    `;

    // Add event listeners for checkboxes
    setupFuelWaterFormListeners();
}

function setupFuelWaterFormListeners() {
    // Supply item checkbox functionality
    function setupSupplyCheckbox(checkboxId, timeInputId, quantityInputId, remarksContainerId, remarksTextareaId) {
        const checkbox = document.getElementById(checkboxId);
        const timeInput = document.getElementById(timeInputId);
        const quantityInput = document.getElementById(quantityInputId);
        const remarksContainer = document.getElementById(remarksContainerId);
        const remarksTextarea = document.getElementById(remarksTextareaId);

        if (checkbox && timeInput && quantityInput && remarksContainer && remarksTextarea) {
            const updateFields = () => {
                const isChecked = checkbox.checked;
                timeInput.disabled = !isChecked;
                quantityInput.disabled = !isChecked;
                remarksTextarea.disabled = !isChecked;

                if (isChecked) {
                    remarksContainer.classList.remove('hidden');
                } else {
                    remarksContainer.classList.add('hidden');
                    timeInput.value = '';
                    quantityInput.value = '';
                    remarksTextarea.value = '';
                }
            };

            checkbox.addEventListener('change', updateFields);
            updateFields(); // initial state
        }
    }

    // Setup checkboxes for fuel and water supplies
    setupSupplyCheckbox('select-fuel', 'fuel-booking-time', 'fuel-quantity', 'fuel-remarks-container', 'fuel-remarks');
    setupSupplyCheckbox('select-water', 'water-booking-time', 'water-quantity', 'water-remarks-container', 'water-remarks');

    // Button event listeners
    document.getElementById('back-to-berth').addEventListener('click', function() {
        showStep(2);
    });

    document.getElementById('fw-reset').addEventListener('click', function() {
        document.getElementById('marine-fuel-water-form').reset();
        // Reset hidden remarks fields
        document.getElementById('fuel-remarks-container').classList.add('hidden');
        document.getElementById('water-remarks-container').classList.add('hidden');
        document.getElementById('fuel-remarks').value = '';
        document.getElementById('water-remarks').value = '';
    });

    document.getElementById('fw-save-submit').addEventListener('click', function() {
        if (saveFuelWaterData()) {
            // Save fuel/water data and go to general works
            loadGeneralWorksForm();
            showStep(4);
        }
    });

    document.getElementById('fw-preview').addEventListener('click', function() {
        // Skip fuel/water selection entirely - clear any data
        marineData.fuelWaterRequests = [];
        loadGeneralWorksForm();
        showStep(4);
    });
}

async function submitFuelWaterRequest() {
    try {
        const tabId = sessionStorage.getItem('ktsb_tab_id');
        const url = tabId ? `api/fuel_water_request.php?tab_id=${tabId}` : 'api/fuel_water_request.php';
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                berthRequest: marineData.berthRequest,
                fuelWaterRequests: marineData.fuelWaterRequests,
                fuelWaterGeneral: marineData.fuelWaterGeneral
            })
        });
        const result = await response.json();
        if (!result.success) {
            throw new Error('Error submitting fuel & water request: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        throw new Error('An error occurred while submitting the fuel & water request.');
    }
}

function saveFuelWaterData(skipValidation = false) {
    const fuelWaterRequests = [];

    // Check fuel selection
    if (document.getElementById('select-fuel').checked) {
        const fuelTime = document.getElementById('fuel-booking-time').value;
        const fuelQuantity = document.getElementById('fuel-quantity').value;
        const fuelRemarks = document.getElementById('fuel-remarks').value.trim();

        if (!fuelTime || !fuelQuantity) {
            if (!skipValidation) {
                alert('Please fill in booking time and quantity for fuel.');
                return false;
            }
        } else {
            fuelWaterRequests.push({
                type: 'fuel',
                bookingTime: fuelTime,
                quantity: fuelQuantity,
                remarks: fuelRemarks || null
            });
        }
    }

    // Check water selection
    if (document.getElementById('select-water').checked) {
        const waterTime = document.getElementById('water-booking-time').value;
        const waterQuantity = document.getElementById('water-quantity').value;
        const waterRemarks = document.getElementById('water-remarks').value.trim();

        if (!waterTime || !waterQuantity) {
            if (!skipValidation) {
                alert('Please fill in booking time and quantity for water.');
                return false;
            }
        } else {
            fuelWaterRequests.push({
                type: 'water',
                bookingTime: waterTime,
                quantity: waterQuantity,
                remarks: waterRemarks || null
            });
        }
    }

    if (fuelWaterRequests.length === 0 && !skipValidation) {
        alert('Please select at least one supply item (fuel or water).');
        return false;
    }

    marineData.fuelWaterRequests = fuelWaterRequests;

    return true;
}

function loadReviewForm() {
    const step5Container = document.getElementById('step-5');
    const parts = [];

    // Container start
    parts.push('<div class="w-full bg-white rounded-xl shadow-sm p-6 md:p-8">');
    parts.push('<div class="grid md:grid-cols-2 gap-x-12 gap-y-8">');

    // Section 1: Request Information
    parts.push('<section class="marine-detail-section">');
    parts.push('<h3>Request Information</h3>');
    parts.push('<div class="space-y-4">');
    
    const requestInfo = [
        { label: 'Log No', value: 'Pending Review' },
        { label: 'Company', value: marineData.berthRequest.company || 'N/A' },
        { label: 'Vessel Name', value: marineData.berthRequest.vesselName || 'N/A' },
        { label: 'PO Number', value: marineData.berthRequest.poNumber || 'N/A' },
        { label: 'Location', value: 'Kuala Terengganu' },
        { label: 'Assigned Agent', value: marineData.berthRequest.assignedAgentName || 'Unassigned' },
    ];

    requestInfo.forEach(item => {
        parts.push('<div class="grid grid-cols-3 gap-4 items-center">');
        parts.push(`<span class="text-sm text-gray-600 col-span-1">${item.label}</span>`);
        if (item.label === 'Log No') {
             parts.push(`<span class="text-sm font-semibold text-gray-900 col-span-2">${item.value}</span>`);
        } else if (item.label === 'Status') { // Not in list above but in original
             parts.push('<div class="col-span-2"><span class="status-pill status-default">Under Review</span></div>');
        } else {
             parts.push(`<span class="text-sm font-semibold text-gray-900 col-span-2">${item.value}</span>`);
        }
        parts.push('</div>');
    });
    
    // Status row separately to match original structure exactly if needed, or just add to list
    parts.push('<div class="grid grid-cols-3 gap-4 items-center">');
    parts.push('<span class="text-sm text-gray-600 col-span-1">Status</span>');
    parts.push('<div class="col-span-2"><span class="status-pill status-default">Under Review</span></div>');
    parts.push('</div>');

    parts.push('</div>'); // End space-y-4
    parts.push('</section>');

    // Section 2: Schedule Information
    parts.push('<section class="marine-detail-section">');
    parts.push('<h3>Schedule Information</h3>');
    parts.push('<div class="space-y-4">');
    
    const etaDate = marineData.berthRequest.eta ? new Date(marineData.berthRequest.eta).toLocaleDateString() : 'N/A';
    const etaTime = marineData.berthRequest.eta ? new Date(marineData.berthRequest.eta).toLocaleTimeString() : 'N/A';
    const etdDate = marineData.berthRequest.etd ? new Date(marineData.berthRequest.etd).toLocaleDateString() : 'N/A';
    const etdTime = marineData.berthRequest.etd ? new Date(marineData.berthRequest.etd).toLocaleTimeString() : 'N/A';
    const createdAt = new Date().toLocaleString();

    const scheduleInfo = [
        { label: 'Est Arrival Date', value: etaDate },
        { label: 'ETA', value: etaTime },
        { label: 'Est Depart Date', value: etdDate },
        { label: 'ETD', value: etdTime },
        { label: 'Created At', value: createdAt }
    ];

    scheduleInfo.forEach(item => {
        parts.push('<div class="grid grid-cols-3 gap-4 items-center">');
        parts.push(`<span class="text-sm text-gray-600 col-span-1">${item.label}</span>`);
        parts.push(`<span class="text-sm font-semibold text-gray-900 col-span-2">${item.value}</span>`);
        parts.push('</div>');
    });

    parts.push('</div>'); // End space-y-4
    parts.push('</section>');

    // Section 3: Berth Remarks
    parts.push('<div class="md:col-span-2 marine-detail-section">');
    parts.push('<div class="border border-black rounded-lg p-4">');
    parts.push('<h4>Berth Remarks</h4>');
    parts.push(`<p>${marineData.berthRequest.remarks || 'No remarks'}</p>`);
    parts.push('</div>');
    parts.push('</div>');

    // Section 4: Crew Transfer Details
    if (marineData.crewTransfer.type && marineData.crewTransfer.crewList && marineData.crewTransfer.crewList.length > 0) {
        const transferType = marineData.crewTransfer.type === 'sign_on' ? 'Sign On' : 'Sign Off';
        const crewCount = marineData.crewTransfer.crewList.length;

        parts.push('<section class="md:col-span-2 marine-detail-section">');
        parts.push('<h3>Crew Transfer Details</h3>');
        parts.push('<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">');
        parts.push(`<h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">${transferType} Crew</h4>`);
        parts.push(`<p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Total crew members: ${crewCount}</p>`);
        
        parts.push('<div class="overflow-x-auto">');
        parts.push('<table class="w-full">');
        parts.push('<thead class="bg-gray-100 dark:bg-gray-600"><tr class="text-xs">');
        ['No', 'Name', 'IC/Passport', 'Nationality', 'Company', 'Destination', 'Passport Expiry'].forEach(h => {
            parts.push(`<th class="px-3 py-2 text-left">${h}</th>`);
        });
        parts.push('</tr></thead><tbody>');

        marineData.crewTransfer.crewList.forEach((crew, index) => {
            const patronymics = ['bin', 'binti', 'ibn', 'ibni', 'bt', 'anak', 'a/l', 'a/p', 's/o', 'd/o'];
            let displayName = 'N/A';
            if (crew.name) {
                const nameParts = crew.name.split(' ').filter(part => !patronymics.includes(part.toLowerCase()));
                displayName = nameParts.length > 2 ? nameParts.slice(1, 3).join(' ') : nameParts.slice(0, 2).join(' ');
            }
            const expiry = crew.passportExpiry ? new Date(crew.passportExpiry).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'N/A';

            parts.push('<tr class="border-b border-gray-200 dark:border-gray-600">');
            parts.push(`<td class="px-3 py-2">${index + 1}</td>`);
            parts.push(`<td class="px-3 py-2">${displayName}</td>`);
            parts.push(`<td class="px-3 py-2">${crew.ic || 'N/A'}</td>`);
            parts.push(`<td class="px-3 py-2">${crew.nationality || 'N/A'}</td>`);
            parts.push(`<td class="px-3 py-2">${crew.company || 'N/A'}</td>`);
            parts.push(`<td class="px-3 py-2">${crew.destination || 'N/A'}</td>`);
            parts.push(`<td class="px-3 py-2">${expiry}</td>`);
            parts.push('</tr>');
        });

        parts.push('</tbody></table></div>');
        parts.push('</div>'); // End rounded-lg
        parts.push('</section>');
    }

    // Section 5: Other Services Details
    if (marineData.crewTransfer.otherServices && marineData.crewTransfer.otherServices.length > 0) {
        parts.push('<section class="md:col-span-2 marine-detail-section">');
        parts.push('<h3>Other Services (Crew Transfer)</h3>');
        parts.push('<div class="flex flex-wrap gap-4">');

        marineData.crewTransfer.otherServices.forEach(service => {
            let serviceName = service.service.replace(/_/g, ' ');
            serviceName = serviceName.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
            
            parts.push('<div class="border border-black rounded-lg p-4 flex-1 min-w-[200px]">');
            parts.push(`<h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">${serviceName}</h5>`);
            if (service.quantity !== null && service.quantity !== undefined && service.quantity !== '') {
                parts.push(`<p class="text-sm text-gray-600 dark:text-gray-400">Quantity: ${service.quantity}</p>`);
            }
            if (service.details) {
                parts.push(`<p class="text-sm text-gray-600 dark:text-gray-400">Details: ${service.details}</p>`);
            }
            parts.push('</div>');
        });

        parts.push('</div>');
        parts.push('</section>');
    }

    // Section 6: Fuel & Water Details
    if (marineData.fuelWaterRequests && marineData.fuelWaterRequests.length > 0) {
        parts.push('<section class="marine-detail-section">');
        parts.push('<h3>Fuel & Water Requests</h3>');
        parts.push('<div class="space-y-4">');

        marineData.fuelWaterRequests.forEach(request => {
            parts.push('<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">');
            parts.push(`<h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 capitalize mb-2">${request.type} Supply</h4>`);
            parts.push('<div class="space-y-1 text-xs">');
            if (request.bookingTime) parts.push(`<p><span class="text-gray-600 dark:text-gray-400">Booking Time:</span> ${request.bookingTime}</p>`);
            parts.push(`<p><span class="text-gray-600 dark:text-gray-400">Quantity:</span> ${request.quantity || 'N/A'} L</p>`);
            if (request.remarks) parts.push(`<p><span class="text-gray-600 dark:text-gray-400">Remarks:</span> ${request.remarks}</p>`);
            parts.push('</div></div>');
        });

        parts.push('</div>');
        parts.push('</section>');
    }

    // Section 7: General Works Details
    if (marineData.generalWorks && marineData.generalWorks.length > 0) {
        parts.push('<section class="marine-detail-section">');
        parts.push('<h3>General Works</h3>');
        parts.push('<div class="space-y-3">');

        marineData.generalWorks.forEach(work => {
            parts.push('<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">');
            parts.push(`<h4 class="text-sm font-semibold text-green-900 dark:text-green-100 capitalize">${work.work}</h4>`);
            if (work.remarks) parts.push(`<p class="text-xs text-gray-600 dark:text-gray-400 mt-2">${work.remarks}</p>`);
            parts.push('</div>');
        });

        parts.push('</div>');
        parts.push('</section>');
    }

    // Close main grid and container
    parts.push('</div>'); // End grid
    
    // Form Actions
    parts.push('<div class="mt-6 flex items-center justify-between gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">');
    parts.push('<button type="button" id="back-to-general-works" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Back</button>');
    parts.push('<button type="button" id="final-submit" class="rounded-lg bg-[#212180] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212180]">Submit All Requests</button>');
    parts.push('</div>');
    
    parts.push('</div>'); // End main container

    step5Container.innerHTML = parts.join('');

    // Add event listeners
    document.getElementById('back-to-general-works').addEventListener('click', function() {
        showStep(4);
    });

    document.getElementById('final-submit').addEventListener('click', async function() {
        await submitAllRequests();
    });
}

function loadGeneralWorksForm() {
    const step4Container = document.getElementById('step-4');

    step4Container.innerHTML = `
        <div class="space-y-6 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
            <div class="space-y-4">
                <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">General Works Selection</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Select the general works required and add any specific remarks for each work.</p>

                <div class="pt-2 space-y-4" id="general-works-list">
                    <!-- General works checkboxes will be loaded here -->
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
                <button type="button" id="back-to-fuel-water-gw" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Back</button>
                <button type="button" id="continue-to-review" class="rounded-lg bg-[#212180] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90" onclick="handleContinueToReview()">Continue to Review</button>
            </div>
        </div>
    `;

    // Load the general works checkboxes
    loadGeneralWorksCheckboxes();

    // Set up click handler using direct onclick
    const continueButton = document.getElementById('continue-to-review');
    if (continueButton) {
        continueButton.onclick = function() {
            console.log('Continue to review clicked');
            saveGeneralWorksData();
            loadReviewForm();
            showStep(5);
        };
    }

    const backButton = document.getElementById('back-to-fuel-water-gw');
    if (backButton) {
        backButton.onclick = function() {
            showStep(3);
        };
    }
}

function loadGeneralWorksCheckboxes() {
    const worksContainer = document.getElementById('general-works-list');

    const generalWorksList = [
        'discharge',
        'loading',
        'inspection',
        'maintenance',
        'standby',
        'touch & go',
        'mooring',
        'unmooring',
        'fire fighter',
        'pneumatic rubber fender',
        'gangway 6 meter',
        'gangway 10 meter',
        'gangway 15 meter',
        'crew change'
    ];

    let html = '';
    generalWorksList.forEach(work => {
        const workId = work.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
        html += `
            <div class="border border-[#DEE2E6] rounded-lg p-4 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <input type="checkbox" id="work-${workId}" class="form-checkbox w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded mt-0.5">
                    <div class="flex-1">
                        <label for="work-${workId}" class="text-sm font-medium text-[#212529] dark:text-gray-300 capitalize cursor-pointer">
                            ${work.replace(/&/g, '&')}
                        </label>
                        <div class="mt-2" id="remarks-${workId}-container" style="display: none;">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Remarks for ${work.replace(/&/g, '&')}</label>
                            <textarea id="remarks-${workId}" class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary resize vertical" rows="2" placeholder="Add any specific instructions..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    worksContainer.innerHTML = html;

    // Add event listeners for checkboxes to show/hide remarks
    generalWorksList.forEach(work => {
        const workId = work.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
        const checkbox = document.getElementById(`work-${workId}`);
        const remarksContainer = document.getElementById(`remarks-${workId}-container`);

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                remarksContainer.style.display = 'block';
            } else {
                remarksContainer.style.display = 'none';
                document.getElementById(`remarks-${workId}`).value = '';
            }
        });
    });
}

function setupGeneralWorksListeners() {
    const backButton = document.getElementById('back-to-fuel-water-gw');
    const continueButton = document.getElementById('continue-to-review');

    if (backButton) {
        backButton.addEventListener('click', function() {
            showStep(3);
        });
    }

    if (continueButton) {
        continueButton.addEventListener('click', function() {
            saveGeneralWorksData();
            loadReviewForm();
            showStep(5);
        });
    } else {
        console.error('Continue to review button not found');
    }
}

function saveGeneralWorksData() {
    const generalWorks = [];

    // Get all checkboxes and collect selected works with remarks
    const checkboxes = document.querySelectorAll('#general-works-list input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const workId = checkbox.id.replace('work-', '');
            const originalWork = workId.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const remarks = document.getElementById(`remarks-${workId}`).value.trim();

            generalWorks.push({
                work: originalWork,
                remarks: remarks || null
            });
        }
    });

    marineData.generalWorks = generalWorks;
}

async function submitAllRequests() {
    try {
        // Prepare all data for submission
        const submissionData = {
            vesselName: marineData.berthRequest.vesselName,
            poNumber: marineData.berthRequest.poNumber,
            eta: marineData.berthRequest.eta,
            etd: marineData.berthRequest.etd,
            company: marineData.berthRequest.company,
            remarks: marineData.berthRequest.remarks,
            assignedAgentId: marineData.berthRequest.assignedAgentId,
            crewTransferType: marineData.crewTransfer.type || null,
            crewData: marineData.crewTransfer.crewList || null,
            otherServicesData: marineData.crewTransfer.otherServices || null,
            fuelWaterData: marineData.fuelWaterRequests.length > 0 ? marineData.fuelWaterRequests : null,
            generalWorksData: marineData.generalWorks || null
        };

        const tabId = sessionStorage.getItem('ktsb_tab_id');
        const url = tabId ? `api/marine_request.php?tab_id=${tabId}` : 'api/marine_request.php';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(submissionData)
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error('Error submitting marine request: ' + result.message);
        }

        // Show success modal
        const successModal = document.getElementById('request-success-modal');
        const successRequestId = document.getElementById('success-request-id');
        
        // Assuming the API returns the request ID in result.id
        successRequestId.textContent = result.id || 'N/A';
        successModal.classList.remove('hidden');
        
        // Trigger animation by re-adding the class or handling display
        // Since we just removed 'hidden', the animation should start if it's CSS based on presence
        // But for the specific animation requested, it runs on load or when the element appears.
        // The CSS animation 'scaleElastic' runs once. If the modal was previously shown and hidden, 
        // we might need to reset the animation.
        const wrapper = successModal.querySelector('.cool-mode-wrapper');
        if (wrapper) {
            wrapper.style.animation = 'none';
            wrapper.offsetHeight; /* trigger reflow */
            wrapper.style.animation = 'scaleElastic 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards';
        }
        const iconPath = successModal.querySelector('.cool-mode-icon path');
        if (iconPath) {
            iconPath.style.animation = 'none';
            iconPath.offsetHeight; /* trigger reflow */
            iconPath.style.animation = 'drawVariableSpeed 0.4s linear 0.3s forwards';
        }

    } catch (error) {
        console.error('Error during final submission:', error);
        alert('An error occurred during final submission: ' + error.message);
    }
}

// Success Modal Logic
document.addEventListener('DOMContentLoaded', function() {
    const successModal = document.getElementById('request-success-modal');
    const successBtn = document.getElementById('request-success-btn');

    if (successBtn) {
        successBtn.addEventListener('click', function() {
            successModal.classList.add('hidden');
            // Reset forms and go back to step 1
            document.getElementById('berth-request-form').reset();
            marineData = { berthRequest: {}, crewTransfer: {}, fuelWaterRequests: [], generalWorks: [] };
            showStep(1);
        });
    }
});

// Save sidebar scroll position without interfering with navigation
window.addEventListener('beforeunload', function(e) {
    // Save sidebar position
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
    }

    // NOTE: Removed auto-logout on unload to prevent navigation issues
    // Auto-logout should only happen on explicit user action or session timeout
});

// Continuous save of sidebar scroll position
const sidebar = document.getElementById('sidebar');
if (sidebar) {
    sidebar.addEventListener('scroll', () => {
        localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
    });
}





// Restore sidebar scroll position with multiple timing mechanisms
function restoreSidebarScroll() {
    const sidebar = document.getElementById('sidebar');
    const urlParams = new URLSearchParams(window.location.search);
    const isFreshLogin = urlParams.get('loggedin') === '1';

    if (isFreshLogin) {
        // Scroll to top on fresh login and clean URL
        if (sidebar) {
            sidebar.scrollTop = 0;
            requestAnimationFrame(() => {
                sidebar.scrollTop = 0;
            });
        }
        // Remove loggedin parameter from URL
        const url = new URL(window.location);
        url.searchParams.delete('loggedin');
        window.history.replaceState({}, document.title, url.pathname + url.search);
    } else {
        // Restore saved scroll position
        const scrollTop = localStorage.getItem('sidebarScrollTop');
        if (sidebar && scrollTop) {
            sidebar.scrollTop = parseInt(scrollTop);
            // Also try with requestAnimationFrame for extra reliability
            requestAnimationFrame(() => {
                sidebar.scrollTop = parseInt(scrollTop);
            });
        }
    }
}

// Try multiple timing approaches
document.addEventListener('DOMContentLoaded', function() {
    // First attempt
    setTimeout(restoreSidebarScroll, 0);
});

window.addEventListener('load', function() {
    // Second attempt with longer delay
    setTimeout(restoreSidebarScroll, 100);
});

// Third attempt after a longer delay
setTimeout(restoreSidebarScroll, 500);

function openEditModal() {
    const modal = document.getElementById('edit-crew-modal');
    const crew = marineData.crewTransfer.crewList[editingIndex];

    document.getElementById('edit-crew-name').value = crew.name || '';
    document.getElementById('edit-crew-ic').value = crew.ic || '';
    document.getElementById('edit-crew-company').value = crew.company || '';
    document.getElementById('edit-crew-destination').value = crew.destination || '';
    document.getElementById('edit-crew-mobile').value = crew.mobile || '';
    document.getElementById('edit-crew-nationality').value = crew.nationality || '';
    document.getElementById('edit-crew-passport-expiry').value = crew.passportExpiry || '';

    modal.classList.remove('hidden');

    // Add event listeners for modal buttons (only once)
    document.getElementById('cancel-edit').addEventListener('click', function() {
        modal.classList.add('hidden');
    });

    document.getElementById('save-edit').addEventListener('click', function() {
        const updatedCrew = {
            name: document.getElementById('edit-crew-name').value.trim(),
            ic: document.getElementById('edit-crew-ic').value.trim(),
            company: document.getElementById('edit-crew-company').value.trim(),
            destination: document.getElementById('edit-crew-destination').value.trim(),
            mobile: document.getElementById('edit-crew-mobile').value.trim(),
            nationality: document.getElementById('edit-crew-nationality').value.trim(),
            passportExpiry: document.getElementById('edit-crew-passport-expiry').value
        };

        if (!updatedCrew.name || !updatedCrew.ic) {
            alert('Name and IC/Passport are required.');
            return;
        }

        marineData.crewTransfer.crewList[editingIndex] = updatedCrew;
        renderCrewList();
        modal.classList.add('hidden');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

    // Close modal when pressing Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modal.classList.add('hidden');
        }
    });
}

// Excel file upload and processing
function handleExcelUpload(file) {
    // Validate file type
    const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    if (!allowedTypes.includes(file.type)) {
        alert('Please upload a valid Excel file (.xlsx or .xls)');
        return;
    }

    // Validate file size (max 10MB)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('File size must be less than 10MB');
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });

            // Get the first worksheet
            const worksheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[worksheetName];

            // Convert to JSON
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            console.log('Excel data loaded, rows length:', jsonData.length);
            console.log('First few rows:', jsonData.slice(0, 5));

            if (jsonData.length < 5) {
                alert('Excel file must have at least 5 rows: 3 title rows + header row + data rows. Please download the template for the correct format.');
                return;
            }

            // Process the Excel data
            const processedCrew = processExcelData(jsonData);

            if (processedCrew.length === 0) {
                alert('No valid crew data found in the Excel file. The file must have headers on row 4 (after 3 title rows). Please download the template for the correct format.');
                return;
            }

            // Add processed crew to the list
            addExcelCrewToList(processedCrew);

            // Reset file input
            document.getElementById('excel-file-input').value = '';

        } catch (error) {
            console.error('Error processing Excel file:', error);
            alert('Error processing Excel file: ' + error.message + '. Please ensure you\'re using the correct template format.');
        }
    };

    reader.onerror = function() {
        alert('Error reading the Excel file');
    };

    reader.readAsArrayBuffer(file);
}

function processExcelData(jsonData) {
    console.log('=== HEADER HUNTING STRATEGY ===');

    // Simplified header detection: The Excel template has 3 lines of title text, so headers start on Row 4 (Index 3)
    const headerRowIndex = 3;

    if (headerRowIndex >= jsonData.length) {
        console.error('Error: Header row (index 3) not found in the Excel file');
        alert('Error: Header row not found. The Excel file must have headers on row 4 (after 3 title rows).');
        return [];
    }

    console.log(`Using Row ${headerRowIndex} as header row:`, jsonData[headerRowIndex]);

    // Create Map: Once that specific row is found, capture the column index for every required field
    let nameIndex = -1;
    let icIndex = -1;
    let mobileIndex = -1;
    let nationalityIndex = -1;
    let passportIndex = -1;
    let destIndex = -1;
    let companyIndex = -1;
    let passportExpiryIndex = -1;

    const row = jsonData[headerRowIndex];

    // Loop through each cell in the header row and find column indices
    row.forEach((cell, columnIndex) => {
        if (cell) {
            const cellValue = cell.toString().toLowerCase().trim();

            // Check for expiry FIRST to avoid conflicts with passport in "Passport Expiry"
            if (cellValue.includes('expiry') || cellValue.includes('exp')) {
                passportExpiryIndex = columnIndex;
            }
            // Check for IC/Passport SECOND
            else if (cellValue.includes('ic') || cellValue.includes('passport') ||
                cellValue.includes('id') ||
                (cellValue.includes('identification') && !cellValue.includes('mobile')) ||
                (cellValue.includes('no') && (cellValue.includes('ic') || cellValue.includes('passport'))) ||
                (cellValue.includes('details') && (cellValue.includes('ic') || cellValue.includes('passport')))) {
                if (icIndex === -1) icIndex = columnIndex;
            }
            else if (cellValue.includes('name')) {
                if (nameIndex === -1) nameIndex = columnIndex;
            }
            else if (cellValue.includes('mobile') || cellValue.includes('phone') ||
                     (cellValue.includes('contact') && !cellValue.includes('customer'))) {
                if (mobileIndex === -1) mobileIndex = columnIndex;
            }
            else if (cellValue.includes('nationality') || cellValue.includes('country')) {
                nationalityIndex = columnIndex;
            }
            else if (cellValue.includes('destination') || cellValue.includes('port')) {
                destIndex = columnIndex;
            }
            else if (cellValue.includes('company') || cellValue.includes('employer')) {
                companyIndex = columnIndex;
            }
        }
    });

    // Required indices validation
    if (nameIndex === -1) {
        alert('Error: Could not find "Name" column in the header row');
        return [];
    }
    if (icIndex === -1) {
        alert('Error: Could not find IC/Passport column in the header row');
        return [];
    }

    // Data Extraction
    const dataRows = jsonData.slice(headerRowIndex + 1);
    const processedCrew = [];

    dataRows.forEach((row, index) => {
        // Skip empty rows
        if (!row || row.every(cell => !cell || cell.toString().trim() === '')) {
            return;
        }

        const crewMember = {};

        // Use discovered indices strictly
        crewMember.name = (nameIndex !== -1 && row[nameIndex]) ? row[nameIndex].toString().trim() : '';
        crewMember.ic = (icIndex !== -1 && row[icIndex]) ? row[icIndex].toString().trim() : '';
        crewMember.mobile = (mobileIndex !== -1 && row[mobileIndex]) ? row[mobileIndex].toString().trim() : '';
        crewMember.nationality = (nationalityIndex !== -1 && row[nationalityIndex]) ? row[nationalityIndex].toString().trim() : '';
        crewMember.destination = (destIndex !== -1 && row[destIndex]) ? row[destIndex].toString().trim() : '';
        crewMember.company = (companyIndex !== -1 && row[companyIndex]) ? row[companyIndex].toString().trim() : '';

        if (passportExpiryIndex !== -1 && row[passportExpiryIndex] !== undefined && row[passportExpiryIndex] !== null) {
            const originalValue = row[passportExpiryIndex];
            let expiryValue;

            if (typeof originalValue === 'number') {
                // Convert Excel Serial Date to JS Date
                const jsDate = new Date((originalValue - 25569) * 86400 * 1000);
                expiryValue = jsDate.toISOString().split('T')[0];
            } else {
                // Handle as string
                expiryValue = originalValue.toString().trim();
                // Handle MM/YYYY format specifically
                const mmYyyyMatch = expiryValue.match(/^(\d{1,2})\/(\d{4})$/);
                if (mmYyyyMatch) {
                    const month = parseInt(mmYyyyMatch[1]) - 1;
                    const year = parseInt(mmYyyyMatch[2]);
                    const dateValue = new Date(year, month, 1);
                    if (!isNaN(dateValue.getTime())) {
                        expiryValue = dateValue.toISOString().split('T')[0];
                    }
                } else {
                    const dateValue = new Date(expiryValue);
                    if (!isNaN(dateValue.getTime())) {
                        expiryValue = dateValue.toISOString().split('T')[0];
                    }
                }
            }
            crewMember.passportExpiry = expiryValue;
        }

        // Validate required fields
        if (crewMember.name && crewMember.name.trim() !== '') {
            processedCrew.push(crewMember);
        }
    });

    return processedCrew;
}

// --- Upload Success Modal Logic ---
// --- Upload Success Modal Logic ---

function showUploadSuccessModal(totalAdded, duplicateCount) {
    const uploadModal = document.getElementById('upload-success-modal');
    const iconWrapper = document.getElementById('icon-wrapper');
    const successIcon = document.getElementById('success-icon');
    const userPlusIcon = document.getElementById('user-plus-icon');
    const uploadModalTitle = document.getElementById('upload-modal-title');
    const uploadModalDesc = document.getElementById('upload-modal-description');

    // 1. UPDATE MODAL TEXT
    if (uploadModalTitle) uploadModalTitle.innerText = "Add Crew Successful";
    
    let descHtml = `Successfully added <strong>${totalAdded}</strong> crew to the list.`;
    if (duplicateCount > 0) {
        descHtml += `<br><span class="text-xs text-gray-400">(${duplicateCount} duplicates skipped)</span>`;
    }
    if (uploadModalDesc) uploadModalDesc.innerHTML = descHtml;

    // 2. TRIGGER ANIMATION
    resetUploadAnimation();
    
    // Apply animation classes
    if (iconWrapper) iconWrapper.classList.add('person-mode-wrapper');
    if (userPlusIcon) {
        userPlusIcon.classList.remove('hidden');
        userPlusIcon.classList.add('person-mode-user');
    }
    if (successIcon) successIcon.classList.add('person-mode-check');

    // Show Modal
    if (uploadModal) uploadModal.classList.remove('hidden');
}

function closeUploadSuccessModal() {
    const uploadModal = document.getElementById('upload-success-modal');
    if (uploadModal) uploadModal.classList.add('hidden');
}

function resetUploadAnimation() {
    const iconWrapper = document.getElementById('icon-wrapper');
    const successIcon = document.getElementById('success-icon');
    const userPlusIcon = document.getElementById('user-plus-icon');

    // Clean up classes to allow re-triggering
    if (iconWrapper) iconWrapper.classList.remove('person-mode-wrapper');
    if (userPlusIcon) {
        userPlusIcon.classList.remove('person-mode-user');
        userPlusIcon.classList.add('hidden');
    }
    if (successIcon) successIcon.classList.remove('person-mode-check');
    
    // Force reflow
    if (iconWrapper) void iconWrapper.offsetWidth;
}

// Function to add processed Excel crew to the marine data
function addExcelCrewToList(excelCrew) {
    // Initialize crew list if it doesn't exist
    if (!marineData.crewTransfer.crewList) {
        marineData.crewTransfer.crewList = [];
    }

    // Add the crew members from Excel
    marineData.crewTransfer.crewList.push(...excelCrew);

    // Update the crew list display
    renderCrewList();

    // Reset file input
    document.getElementById('excel-file-input').value = '';

    // Show custom success modal
    showUploadSuccessModal(excelCrew.length, 0);
}

// Download Excel template
function downloadCrewTemplate() {
    // Create professional template data
    const templateData = [
        // Header section
        ['KUALA TERENGGANU PORT SUPPORT BASE'],
        ['Marine Crew Transfer Template'],
        [''],

        // Column headers with required indicators
        ['*Name', '*IC/Passport', 'Mobile Number', 'Nationality', 'Passport Expiry (MM/YYYY)', 'Company', 'Destination'],

        // Empty rows for new entries
        ['', '', '', '', '', '', ''], // Empty rows for new entries
        ['', '', '', '', '', '', ''],
        ['', '', '', '', '', '', ''],
        ['', '', '', '', '', '', ''],
        ['', '', '', '', '', '', ''],
    ];

    // Create worksheet
    const ws = XLSX.utils.aoa_to_sheet(templateData);

    // Set column widths for better readability
    const colWidths = [
        { wch: 25 }, // name
        { wch: 18 }, // ic passport
        { wch: 18 }, // mobile number
        { wch: 15 }, // nationality
        { wch: 22 }, // passport expiry
        { wch: 25 }, // company
        { wch: 18 }  // destination
    ];
    ws['!cols'] = colWidths;

    // Style the header rows (merge cells and styling)
    if (!ws['!merges']) ws['!merges'] = [];
    ws['!merges'].push(
        // Merge header title cells
        { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } }
    );

    // Add basic header styling
    const headerStyle = { font: { bold: true, sz: 14 }, alignment: { horizontal: 'center' } };

    // Apply styles to header cells
    ws['A1'].s = headerStyle;
    ws['A2'].s = headerStyle;

    // Create workbook and add worksheet
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Crew_Template');

    // Generate filename with current date
    const today = new Date();
    const dateStr = today.toISOString().split('T')[0].replace(/-/g, '');
    const filename = `KTSB_Marine_Crew_Template_${dateStr}.xlsx`;

    // Download the file
    XLSX.writeFile(wb, filename);
}

// Fetch and populate agents
async function loadActiveAgents() {
    try {
        const tabId = new URLSearchParams(window.location.search).get('tab_id');
        const url = tabId ? `api/get_active_agents.php?tab_id=${encodeURIComponent(tabId)}` : 'api/get_active_agents.php';
        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.agents.length > 0) {
            const agentSelect = document.getElementById('assigned-agent');
            const agentContainer = document.getElementById('assign-agent-container');
            
            // Clear existing options except first one
            while (agentSelect.options.length > 1) {
                agentSelect.remove(1);
            }

            data.agents.forEach(agent => {
                const option = document.createElement('option');
                option.value = agent.id;
                option.textContent = agent.name || agent.username;
                agentSelect.appendChild(option);
            });

            // Show the container
            agentContainer.style.display = 'flex';
        }
    } catch (error) {
        console.error('Error fetching agents:', error);
    }
}

// Profile dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load agents on page load
    loadActiveAgents();
    
    const profileBtn = document.getElementById('profile-dropdown-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileBtn && profileDropdown) {
        // Toggle dropdown on button click
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                profileDropdown.classList.add('hidden');
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById('sidebar-scroll-container');
    if (sidebar) {
        // Restore position
        const savedPos = localStorage.getItem('sidebarScrollPos');
        if (savedPos) sidebar.scrollTop = savedPos;

        // Save position on scroll
        sidebar.addEventListener('scroll', () => {
            localStorage.setItem('sidebarScrollPos', sidebar.scrollTop);
        });
    }
});
</script>
<!-- Delete Confirmation Modal -->
<div id="delete-confirmation-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Centering Hack -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">

                    <!-- Red Warning Icon -->
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="delete-modal-title">
                            Delete Crew?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="delete-modal-description">
                                Are you sure you want to delete this crew? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirm-delete-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Delete
                </button>
                <button type="button" id="cancel-delete-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Success Modal -->
<div id="upload-success-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">

                    <!-- ICON WRAPPER (Animation Container) -->
                    <div id="icon-wrapper" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">

                        <!-- 1. The Tick Icon (Hidden initially) -->
                        <svg id="success-icon" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                        </svg>

                        <!-- 2. The User Plus Icon (Visible initially) -->
                        <svg id="user-plus-icon" class="w-6 h-6 absolute hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>

                    </div>

                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <!-- Dynamic Title -->
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="upload-modal-title">
                            Upload Successful
                        </h3>
                        <!-- Dynamic Description -->
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="upload-modal-description">
                                File uploaded successfully.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeUploadSuccessModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Request Success Modal -->
<div id="request-success-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                            <!-- Single Line Path with stroke-width 4 -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Request Submitted</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Your request has successfully submitted.</p>
                            <p class="text-sm text-gray-500 mt-1">Your request number : <span id="success-request-id" class="font-bold text-gray-900"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="request-success-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Confirmation Modal Listeners
    const deleteModal = document.getElementById('delete-confirmation-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (pendingDeletionIndexes.length > 0) {
                // Sort descending to prevent index shifting issues
                pendingDeletionIndexes.sort((a, b) => b - a);
                
                pendingDeletionIndexes.forEach(index => {
                    if (marineData.crewTransfer.crewList) {
                        marineData.crewTransfer.crewList.splice(index, 1);
                    }
                });
                
                renderCrewList();
                pendingDeletionIndexes = []; // Reset
            }
            deleteModal.classList.add('hidden');
        });
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            pendingDeletionIndexes = []; // Reset
            deleteModal.classList.add('hidden');
        });
    }
});
</script>
</body></html>
