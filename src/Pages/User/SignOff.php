<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';
$currentUser = getCurrentUser();

// Check if user has permission to access this page (only company and admin users can make requests)
if ($currentUser['user_type'] !== 'user' && $currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Get DB Connection and Fetch Vessels
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, vessel_name FROM vessels ORDER BY vessel_name ASC");
$vessels = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Crew Sign Off</title>
    <script src="assets/js/tailwindcss.js"></script>
    <script src="assets/js/xlsx.full.min.js"></script>
    <link href="assets/css/fonts.css" rel="stylesheet"/>
    <link href="assets/css/material-icons.css" rel="stylesheet"/>
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

        @keyframes drawVariableSpeed {
            0% { stroke-dashoffset: 20; }
            60% { stroke-dashoffset: 14; }
            100% { stroke-dashoffset: 0; }
        }

        /* --- Existing Cool Mode Animation (Keep for Request Success) --- */
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
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
<!-- Collapsible SideNavBar -->
<?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
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
            <div class="flex flex-1 flex-col lg:ml-64 pt-16">
                <main class="flex-1 overflow-y-auto p-4 md:p-8 pb-40">
                <div class="mx-auto max-w-7xl">
                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Crew Sign Off</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Enter details for crew member sign off.</p>
                    </div>
                    <!-- Form -->
    <form id="crew-signoff-form" class="space-y-8">
        <!-- General Information Section -->
        <div class="rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
            <div class="space-y-4">
                <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">General Information</h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
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
                        <input id="po-number" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                    </label>
                    <label class="flex flex-col">
                        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Request Date</p>
                        <input id="request-date" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
                    </label>
                    <label class="flex flex-col">
                        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Request Time</p>
                        <select id="request-time" class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
                            <option value="" disabled selected>Select Time</option>
                        </select>
                    </label>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <label class="flex flex-col">
                        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Remarks</p>
                        <textarea id="remarks" class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary resize vertical" rows="3"></textarea>
                    </label>
                    <label class="flex flex-col" id="assign-agent-container" style="display: none;">
                        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Assign to Agent</p>
                        <select id="assigned-agent" class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
                            <option value="">Unassigned</option>
                        </select>
                    </label>
                </div>
            </div>
        </div>

                    <div class="mb-8"></div>

                    <!-- Other Services Card -->
                    <div class="rounded-lg border border-[#DEE2E6] bg-white dark:border-gray-700 dark:bg-gray-800/50 p-6 space-y-4">
                        <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Other Services</h3>
                        <div class="pt-2 space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" id="other-services-fields">
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

                    <div class="mb-8"></div>

                    <!-- Crew Details Card -->
                    <div class="rounded-lg border border-[#DEE2E6] bg-white dark:border-gray-700 dark:bg-gray-800/50 p-6 space-y-4">
                        <h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Crew Details</h3>
                        <div class="border border-[#DEE2E6] rounded-lg p-4 dark:border-gray-700 pt-2 space-y-4" id="crew-form">
<!-- Row 1: Name (Full Width) -->
<div class="flex flex-col">
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Name</span>
        <input id="crew-name" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
    </label>
</div>

<!-- Row 2: IC/Passport and Passport Expiry -->
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">IC/Passport</span>
        <input id="crew-ic" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
    </label>
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Passport Expiry</span>
        <input id="crew-passport-expiry" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
    </label>
</div>

<!-- Row 3: Mobile Number and Nationality -->
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Mobile Number</span>
        <input id="crew-mobile" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
    </label>
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Nationality</span>
        <input id="crew-nationality" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
    </label>
</div>

<!-- Row 4: Company and Destination -->
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Company</span>
        <input id="crew-company" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
    </label>
    <label class="flex flex-col">
        <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Destination</span>
        <input id="crew-destination" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
    </label>
</div>
                            <div class="flex gap-2 mt-6">
<button id="download-template" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background-color: #008700;" onmouseover="this.style.backgroundColor='#006600'" onmouseout="this.style.backgroundColor='#008700'">Download Template</button>
<button id="upload-excel-crew" class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm" style="background-color: #008700;" onmouseover="this.style.backgroundColor='#006600'" onmouseout="this.style.backgroundColor='#008700'">Upload Excel</button>
<input type="file" id="excel-file-input" accept=".xlsx,.xls" class="hidden">
<button id="submit-crew" class="rounded-lg bg-[#212180] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212180]">Add Crew</button>
                            </div>
                        </div>

                        <!-- Crew List Section (inside card) -->
                        <div class="space-y-4" id="crew-list-section" style="display: none;">
                            <h4 class="border-b border-[#DEE2E6] pb-2 text-base font-semibold text-[#212529] dark:border-gray-700 dark:text-gray-200">Crew Member List</h4>
                            <div class="pt-2" id="crew-list"></div>
                        </div>
                    </div>
                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
                        <button type="button" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Cancel</button>
<button type="submit" class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121]">Submit Sign Off</button>
                    </div>
                    </form>
                    <!-- Edit Crew Modal -->
                    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                                <div class="p-6 space-y-4">
                                    <h3 class="text-lg font-bold text-[#212529] dark:text-gray-200">Edit Crew Member</h3>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Name</span>
                                            <input id="edit-crew-name" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                                        </label>
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">IC/Passport</span>
                                            <input id="edit-crew-ic" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                                        </label>
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Company</span>
                                            <input id="edit-crew-company" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                                        </label>
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Destination</span>
                                            <input id="edit-crew-destination" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Mobile Number</span>
                                            <input id="edit-crew-mobile" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                                        </label>
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Nationality</span>
                                            <input id="edit-crew-nationality" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
                                        </label>
                                        <label class="flex flex-col">
                                            <span class="pb-1 text-sm font-medium text-[#212529] dark:text-gray-300">Passport Expiry</span>
                                            <input id="edit-crew-passport-expiry" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-sm text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
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
            </main>
        </div>
    </div>
    <script>
        let crewList = [];
        let editingIndex = null;
        let pendingDeletionIndexes = [];

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
                // If no saved state, keep the default checked state from HTML
            });
        }

        function loadToggleState(id) {
            const state = localStorage.getItem(id);
            const element = document.getElementById(id);
            if (state !== null && element) {
                element.checked = state === 'true';
            }
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

        setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
        setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
        setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

        // Special handling for other-services-toggle to load state from localStorage on page load
        document.addEventListener('DOMContentLoaded', function() {
            const otherServicesToggle = document.getElementById('other-services-toggle');
            if (otherServicesToggle) {
                loadToggleState('other-services-toggle');
                // Trigger the setupToggle update after loading state
                otherServicesToggle.dispatchEvent(new Event('change'));
            }
        });

        // Clear form function
        function clearCrewForm() {
            document.getElementById('crew-name').value = '';
            document.getElementById('crew-ic').value = '';
            document.getElementById('crew-passport-expiry').value = '';
            document.getElementById('crew-nationality').value = '';
            document.getElementById('crew-company').value = '';
            document.getElementById('crew-destination').value = '';
            document.getElementById('crew-mobile').value = '';
        }

        // Render crew list
        function renderCrewList() {
            const crewListContainer = document.getElementById('crew-list');
            const crewListSection = document.getElementById('crew-list-section');

            if (crewList.length === 0) {
                crewListSection.style.display = 'none';
                return;
            }

            crewListSection.style.display = 'block';

            let tableHTML = `
                <div class="overflow-x-auto border border-[#DEE2E6] rounded-lg dark:border-gray-700">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all-crew" class="form-checkbox rounded">
                                </th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID/Passport</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nationality</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Destination</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Passport Expiry</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mobile</th>
                                <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">`;

            crewList.forEach((crew, index) => {
                // Format passport expiry date
                const expiryDate = crew.passportExpiry ? new Date(crew.passportExpiry).toLocaleDateString('en-US', {year: 'numeric', month: 'short'}) : 'N/A';

                // Display name with patronym handling for Malaysian names
                const fullName = crew.name || '';
                const nameParts = fullName.trim().split(/\s+/);
                let displayName = 'N/A';

                if (nameParts.length > 0) {
                    // Common Malaysian patronyms to skip
                    const patronyms = ['bin', 'binti', 'bt', 'bte', 'a/l', 'a/p', 'ibn', 'bint'];
                    const lowerSecondPart = nameParts[1]?.toLowerCase();

                    // If second part is a patronym and there are at least 3 parts, skip it
                    if (nameParts.length >= 3 && lowerSecondPart && patronyms.includes(lowerSecondPart)) {
                        displayName = [nameParts[0], nameParts[2]].join(' ');
                    } else if (nameParts.length >= 2) {
                        // Take first two words
                        displayName = nameParts.slice(0, 2).join(' ');
                    } else {
                        // Single word name
                        displayName = nameParts[0];
                    }
                }

                const rowClass = index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900';

                tableHTML += `
                            <tr class="${rowClass}">
                                <td class="px-2 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="row-crew-checkbox form-checkbox rounded" data-index="${index}">
                                </td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">${index + 1}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">${displayName}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.ic || 'N/A'}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.nationality || 'N/A'}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.company || 'N/A'}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.destination || 'N/A'}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${expiryDate}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">${crew.mobile || 'N/A'}</td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm">
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
                                <span id="selected-count">0</span> of <span id="total-count">${crewList.length}</span> selected
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="delete-selected" class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed" disabled>Delete Selected</button>
                            </div>
                        </div>
                    </div>
                </div>`;

            crewListContainer.innerHTML = tableHTML;

            // Add event listeners
            setupCheckboxEventListeners();
        }

        // Submit crew functionality
        document.getElementById('submit-crew').addEventListener('click', function(e) {
            e.preventDefault();
            const crewData = {
                name: document.getElementById('crew-name').value.trim(),
                ic: document.getElementById('crew-ic').value.trim(),
                passportExpiry: document.getElementById('crew-passport-expiry').value,
                nationality: document.getElementById('crew-nationality').value.trim(),
                company: document.getElementById('crew-company').value.trim(),
                destination: document.getElementById('crew-destination').value.trim(),
                mobile: document.getElementById('crew-mobile').value.trim()
            };

            // Basic validation
            if (!crewData.name) {
                alert('Name is required');
                return;
            }

            crewList.push(crewData);
            renderCrewList();
            clearCrewForm();
        });

        // Edit modal functions
        function openEditModal() {
            const modal = document.getElementById('edit-modal');
            const crew = crewList[editingIndex];

            document.getElementById('edit-crew-name').value = crew.name || '';
            document.getElementById('edit-crew-ic').value = crew.ic || '';
            document.getElementById('edit-crew-passport-expiry').value = crew.passportExpiry || '';
            document.getElementById('edit-crew-nationality').value = crew.nationality || '';
            document.getElementById('edit-crew-company').value = crew.company || '';
            document.getElementById('edit-crew-destination').value = crew.destination || '';
            document.getElementById('edit-crew-mobile').value = crew.mobile || '';

            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        // Cancel edit
        document.getElementById('cancel-edit').addEventListener('click', closeEditModal);

        // Save edit
        document.getElementById('save-edit').addEventListener('click', function() {
            const crewData = {
                name: document.getElementById('edit-crew-name').value.trim(),
                ic: document.getElementById('edit-crew-ic').value.trim(),
                passportExpiry: document.getElementById('edit-crew-passport-expiry').value,
                nationality: document.getElementById('edit-crew-nationality').value.trim(),
                company: document.getElementById('edit-crew-company').value.trim(),
                destination: document.getElementById('edit-crew-destination').value.trim(),
                mobile: document.getElementById('edit-crew-mobile').value.trim()
            };

            // Basic validation
            if (!crewData.name) {
                alert('Name is required');
                return;
            }

            crewList[editingIndex] = crewData;
            renderCrewList();
            closeEditModal();
        });

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

        // Load agents on page load
        document.addEventListener('DOMContentLoaded', loadActiveAgents);

        // Date and Time Validation Logic
        // Date and Time Validation Logic
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('request-date');
            const timeInput = document.getElementById('request-time');

            function generateTimeOptions() {
                // Clear existing options except the first one
                while (timeInput.options.length > 1) {
                    timeInput.remove(1);
                }
                for (let i = 0; i < 24; i++) {
                    const hour = String(i).padStart(2, '0');
                    const time = `${hour}:00`;
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    timeInput.appendChild(option);
                }
            }

            if (timeInput) generateTimeOptions();

            function updateMinDateTime() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const todayDate = `${year}-${month}-${day}`;
                const currentHour = now.getHours();
                const currentMinutes = now.getMinutes();
                
                if (dateInput) {
                    dateInput.min = todayDate;
                }

                if (timeInput && dateInput.value) {
                    const options = timeInput.options;
                    if (dateInput.value === todayDate) {
                        for (let i = 1; i < options.length; i++) {
                            const hour = parseInt(options[i].value.split(':')[0]);
                            if (hour < currentHour || (hour === currentHour && currentMinutes > 0)) {
                                options[i].disabled = true;
                                options[i].style.color = '#999';
                            } else {
                                options[i].disabled = false;
                                options[i].style.display = '';
                                options[i].style.color = '';
                            }
                        }
                        // Reset if selected time is now invalid
                        if (timeInput.value) {
                            const selectedHour = parseInt(timeInput.value.split(':')[0]);
                            if (selectedHour < currentHour || (selectedHour === currentHour && currentMinutes > 0)) {
                                timeInput.value = '';
                            }
                        }
                    } else {
                        for (let i = 1; i < options.length; i++) {
                            options[i].disabled = false;
                            options[i].style.display = '';
                            options[i].style.color = '';
                        }
                    }
                }
            }

            function validateDateTime() {
                updateMinDateTime();
            }

            if (dateInput) {
                dateInput.addEventListener('change', validateDateTime);
                dateInput.addEventListener('focus', updateMinDateTime);
            }

            if (timeInput) {
                timeInput.addEventListener('change', validateDateTime);
                timeInput.addEventListener('focus', updateMinDateTime);
            }
            
            // Initial call
            updateMinDateTime();
        });

        // Prevent form submission when clicking submit crew
        document.getElementById('submit-crew').addEventListener('click', function(e) {
            e.preventDefault();
        });

// Main form submission
        document.getElementById('crew-signoff-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (crewList.length === 0) {
                alert('Please add at least one crew member before submitting the form.');
                return;
            }

            // Validation for Other Services
            const takeawayCheckbox = document.getElementById('takeaway-checkbox');
            const takeawayInput = document.getElementById('takeaway');
            if (takeawayCheckbox.checked && !takeawayInput.value) {
                alert('Please enter a quantity for Takeaway.');
                takeawayInput.focus();
                return;
            }

            const baggageHandlingCheckbox = document.getElementById('baggage-handling-checkbox');
            const baggageHandlingInput = document.getElementById('baggage-handling');
            if (baggageHandlingCheckbox.checked && !baggageHandlingInput.value) {
                alert('Please enter a quantity for Baggage Handling.');
                baggageHandlingInput.focus();
                return;
            }

            // Collect general information
            const generalInfo = {
                vesselName: document.getElementById('vessel-name').value.trim(),
                poNumber: document.getElementById('po-number').value.trim(),
                requestDate: document.getElementById('request-date').value,
                requestTime: document.getElementById('request-time').value,
                remarks: document.getElementById('remarks').value.trim(),
                assignedAgentId: document.getElementById('assigned-agent').value || null
            };

            // Collect other services data
            const otherServices = {
                takeawayQuantity: document.getElementById('takeaway').disabled ? null : parseInt(document.getElementById('takeaway').value) || null,
                baggageHandlingQuantity: document.getElementById('baggage-handling').disabled ? null : parseInt(document.getElementById('baggage-handling').value) || null
            };

            // Prepare crew details
            const crewDetails = crewList.map(crew => ({
                name: crew.name,
                ic: crew.ic,
                mobile: crew.mobile || null,
                nationality: crew.nationality || null,
                passportExpiry: crew.passportExpiry || null,
                company: crew.company || null,
                destination: crew.destination || null
            }));

            // Prepare request payload
            const requestData = {
                generalInfo: generalInfo,
                otherServices: otherServices,
                crewDetails: crewDetails
            };

            try {
                // Submit the request
                const tabId = new URLSearchParams(window.location.search).get('tab_id');
                const url = tabId ? `api/crew_sign_off_request.php?tab_id=${encodeURIComponent(tabId)}` : 'api/crew_sign_off_request.php';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });


                const rawText = await response.text();
                console.log('Raw Server Response:', rawText);

                let result;
                try {
                    result = JSON.parse(rawText);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Raw Text:', rawText);
                    alert('Server Error (Not JSON): ' + rawText.substring(0, 200));
                    throw new Error('Invalid JSON response');
                }


                if (result.success) {
                    // Show success modal
                    const successModal = document.getElementById('request-success-modal');
                    const successRequestId = document.getElementById('success-request-id');
                    
                    // Assuming the API returns the request ID in result.requestId or similar
                    successRequestId.textContent = result.requestId || 'N/A';
                    // Assuming the API returns the request ID in result.requestId or similar
            successRequestId.textContent = result.requestId || 'N/A';
            successModal.classList.remove('hidden');
            
            // Trigger animation
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
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('Submission error:', error);
                alert('An error occurred while submitting the request. Please try again.');
            }
        });

        // Checkbox functionality
        function setupCheckboxEventListeners() {
            const selectAllCheckbox = document.getElementById('select-all-crew');
            const deleteSelectedBtn = document.getElementById('delete-selected');
            const selectedCountEl = document.getElementById('selected-count');
            const totalCountEl = document.getElementById('total-count');

            // Select all functionality
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.row-crew-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                updateSelectedCount();
            });

            // Individual checkbox change
            document.querySelectorAll('.row-crew-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllState();
                    updateSelectedCount();
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

                // Store indexes for deletion
                pendingDeletionIndexes = selectedIndexes;

                // Update modal text
                const descriptionEl = document.getElementById('delete-modal-description');
                if (descriptionEl) {
                    descriptionEl.textContent = `Are you sure you want to delete ${selectedIndexes.length} crew member(s)? This action cannot be undone.`;
                }

                // Show modal
                const deleteModal = document.getElementById('delete-confirmation-modal');
                if (deleteModal) {
                    deleteModal.classList.remove('hidden');
                }
            });

            // Delete Modal Logic
            const deleteModal = document.getElementById('delete-confirmation-modal');
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            const cancelDeleteBtn = document.getElementById('cancel-delete-btn');

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (pendingDeletionIndexes.length > 0) {
                        // Remove selected crew members (in reverse order to maintain correct indexes)
                        pendingDeletionIndexes.sort((a, b) => b - a);
                        pendingDeletionIndexes.forEach(index => {
                            crewList.splice(index, 1);
                        });

                        renderCrewList();
                        pendingDeletionIndexes = []; // Reset
                    }
                    deleteModal.classList.add('hidden');
                });
            }

            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', function() {
                    deleteModal.classList.add('hidden');
                    pendingDeletionIndexes = []; // Reset
                });
            }

            function updateSelectAllState() {
                const totalCheckboxes = document.querySelectorAll('.row-crew-checkbox').length;
                const checkedCheckboxes = document.querySelectorAll('.row-crew-checkbox:checked').length;
                selectAllCheckbox.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
                selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }

            function updateSelectedCount() {
                const selectedCount = document.querySelectorAll('.row-crew-checkbox:checked').length;
                const totalCount = crewList.length;

                selectedCountEl.textContent = selectedCount;
                totalCountEl.textContent = totalCount;

                // Enable/disable delete button
                deleteSelectedBtn.disabled = selectedCount === 0;
            }
        }

        // Close modal when clicking outside
        document.getElementById('edit-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Add event delegation for dynamically created edit buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-crew-btn')) {
                editingIndex = parseInt(e.target.getAttribute('data-index'));
                openEditModal();
            }
        });

// Save sidebar scroll position on page unload
window.addEventListener('beforeunload', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
    }
});

// Restore sidebar scroll position with multiple timing mechanisms
function restoreSidebarScroll() {
    const sidebar = document.getElementById('sidebar');
    const scrollTop = localStorage.getItem('sidebarScrollTop');
    if (sidebar && scrollTop) {
        sidebar.scrollTop = parseInt(scrollTop);
        // Also try with requestAnimationFrame for extra reliability
        requestAnimationFrame(() => {
            sidebar.scrollTop = parseInt(scrollTop);
        });
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

document.getElementById('takeaway-checkbox').addEventListener('change', function() {
    const isChecked = this.checked;
    const takeawayInput = document.getElementById('takeaway');

    if (isChecked) {
        takeawayInput.disabled = false;
        takeawayInput.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-gray-800', 'dark:text-gray-400');
        takeawayInput.classList.add('bg-background-light', 'text-[#212529]', 'dark:bg-gray-900/50', 'dark:text-gray-200');
    } else {
        takeawayInput.disabled = true;
        takeawayInput.classList.remove('bg-background-light', 'text-[#212529]', 'dark:bg-gray-900/50', 'dark:text-gray-200');
        takeawayInput.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-gray-800', 'dark:text-gray-400');
    }
});

document.getElementById('baggage-handling-checkbox').addEventListener('change', function() {
    const isChecked = this.checked;
    const baggageHandlingInput = document.getElementById('baggage-handling');

    if (isChecked) {
        baggageHandlingInput.disabled = false;
        baggageHandlingInput.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-gray-800', 'dark:text-gray-400');
        baggageHandlingInput.classList.add('bg-background-light', 'text-[#212529]', 'dark:bg-gray-900/50', 'dark:text-gray-200');
    } else {
        baggageHandlingInput.disabled = true;
        baggageHandlingInput.classList.remove('bg-background-light', 'text-[#212529]', 'dark:bg-gray-900/50', 'dark:text-gray-200');
        baggageHandlingInput.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-gray-800', 'dark:text-gray-400');
    }
});

// Excel file upload and processing for crew sign off
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

            if (jsonData.length < 2) {
                alert('Excel file must contain at least a header row and one data row');
                return;
            }

            // Process the Excel data
            const processedCrew = processExcelDataSignOff(jsonData);

            if (processedCrew.length === 0) {
                alert('No valid crew data found in the Excel file. Please check the column headers.');
                return;
            }

            // Add processed crew to the list
            addExcelCrewToListSignOff(processedCrew);

            // Reset file input
            document.getElementById('excel-file-input').value = '';

        } catch (error) {
            console.error('Error processing Excel file:', error);
            alert('Error processing Excel file. Please check the file format.');
        }
    };

    reader.onerror = function() {
        alert('Error reading the Excel file');
    };

    reader.readAsArrayBuffer(file);
}

function processExcelDataSignOff(jsonData) {
    console.log('=== HEADER HUNTING STRATEGY ===');

    // Simplified header detection: The Excel template has 3 lines of title text, so headers start on Row 4 (Index 3)
    const headerRowIndex = 3;

    if (headerRowIndex >= jsonData.length) {
        console.error('Error: Header row (index 3) not found in the Excel file');
        alert('Error: Header row not found. The Excel file must have headers on row 4 (after 3 title rows).');
        return [];
    }

    console.log(`Using Row ${headerRowIndex} as header row:`, jsonData[headerRowIndex]);

    // Create Map: Once that specific row is found, capture the column index for every required field ($nameIndex, $passportIndex, $destIndex, etc.)
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
                console.log(`Found PASSPORT EXPIRY at Column ${columnIndex} (header: "${cell}")`);
            }
            // Check for IC/Passport SECOND
            else if (cellValue.includes('ic') || cellValue.includes('passport') ||
                cellValue.includes('id') ||
                (cellValue.includes('identification') && !cellValue.includes('mobile')) ||
                (cellValue.includes('no') && (cellValue.includes('ic') || cellValue.includes('passport'))) ||
                (cellValue.includes('details') && (cellValue.includes('ic') || cellValue.includes('passport')))) {
                if (icIndex === -1) { // Only set if not already found
                    icIndex = columnIndex;
                    console.log(`Found IC/PASSPORT at Column ${columnIndex} (header: "${cell}")`);
                }
            }
            else if (cellValue.includes('name')) {
                if (nameIndex === -1) {
                    nameIndex = columnIndex;
                    console.log(`Found NAME at Column ${columnIndex}`);
                }
            }
            else if (cellValue.includes('mobile') || cellValue.includes('phone') ||
                     (cellValue.includes('contact') && !cellValue.includes('customer'))) {
                if (mobileIndex === -1) {
                    mobileIndex = columnIndex;
                    console.log(`Found MOBILE at Column ${columnIndex}`);
                }
            }
            else if (cellValue.includes('nationality') || cellValue.includes('country')) {
                nationalityIndex = columnIndex;
                console.log(`Found NATIONALITY at Column ${columnIndex}`);
            }
            else if (cellValue.includes('destination') || cellValue.includes('port')) {
                destIndex = columnIndex;
                console.log(`Found DESTINATION at Column ${columnIndex}`);
            }
            else if (cellValue.includes('company') || cellValue.includes('employer')) {
                companyIndex = columnIndex;
                console.log(`Found COMPANY at Column ${columnIndex}`);
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

    if (destIndex === -1) {
        alert('Error: Could not find "Destination" column in the header row');
        return [];
    }

    // Debug logging as requested: error_log("Destination Index: " . $destIndex);
    console.log(`Destination Index: ${destIndex}`);

    console.log('\n=== FINAL COLUMN MAPPING ===');
    console.log(`Name Index: ${nameIndex}`);
    console.log(`IC/Passport Index: ${icIndex}`);
    console.log(`Mobile Index: ${mobileIndex}`);
    console.log(`Nationality Index: ${nationalityIndex}`);
    console.log(`Destination Index: ${destIndex}`);
    console.log(`Company Index: ${companyIndex}`);
    console.log(`Passport Expiry Index: ${passportExpiryIndex}`);

    // Data Extraction: In the data loop, use these discovered indices strictly
    const dataRows = jsonData.slice(headerRowIndex + 1);
    const processedCrew = [];

    console.log('\n=== PROCESSING DATA ROWS ===');

    dataRows.forEach((row, index) => {
        // Skip empty rows
        if (!row || row.every(cell => !cell || cell.toString().trim() === '')) {
            return;
        }

        console.log(`Processing data row ${index + 1}:`, row.map(cell => cell ? cell.toString() : ''));

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
                    const month = parseInt(mmYyyyMatch[1]) - 1; // JS months are 0-indexed
                    const year = parseInt(mmYyyyMatch[2]);
                    const dateValue = new Date(year, month, 1);
                    if (!isNaN(dateValue.getTime())) {
                        expiryValue = dateValue.toISOString().split('T')[0];
                    }
                } else {
                    // Try to parse as other date formats
                    const dateValue = new Date(expiryValue);
                    if (!isNaN(dateValue.getTime())) {
                        expiryValue = dateValue.toISOString().split('T')[0];
                    }
                }
            }

            crewMember.passportExpiry = expiryValue;
        }

        console.log(`Mapped crew member:`, crewMember);

        // Validate required fields - only require name, allow partial crew data
        if (crewMember.name && crewMember.name.trim() !== '') {
            processedCrew.push(crewMember);
            console.log(`? Added crew member: ${crewMember.name}`);
        } else {
            console.log(`? Skipping row - missing required name field`);
        }
    });

    console.log(`\n=== PROCESSING COMPLETE ===`);
    console.log(`Successfully processed ${processedCrew.length} crew members`);

    return processedCrew;
}

function addExcelCrewToListSignOff(excelCrew) {
    // Initialize crew list if it doesn't exist
    if (!crewList) {
        crewList = [];
    }

    // Filter out duplicates based on IC/passport
    const existingICs = new Set(crewList.map(crew => crew.ic?.toLowerCase()));
    const newCrew = excelCrew.filter(crew => !existingICs.has(crew.ic?.toLowerCase()));

    if (newCrew.length === 0) {
        alert('All crew members from the Excel file are already in the list or missing required data.');
        return;
    }

    // Add new crew members
    crewList.push(...newCrew);

    // Re-render the crew list
    renderCrewList();

    // Show success message
    const duplicateCount = excelCrew.length - newCrew.length;
    // let message = `Successfully added ${newCrew.length} crew member(s) from Excel file.`;

    // if (duplicateCount > 0) {
    //     message += ` ${duplicateCount} duplicate(s) were skipped.`;
    // }

    // alert(message);
    showUploadSuccessModal(newCrew.length, duplicateCount);
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

    if (!uploadModal || !uploadModalTitle || !uploadModalDesc) {
        console.error("Modal elements not found");
        return;
    }

    // 1. UPDATE MODAL TEXT
    uploadModalTitle.innerText = "Add Crew Successful";
    
    let descHtml = `Successfully added <strong>${totalAdded}</strong> crew to the list.`;
    if (duplicateCount > 0) {
        descHtml += `<br><span class="text-xs text-gray-400">(${duplicateCount} duplicates skipped)</span>`;
    }
    uploadModalDesc.innerHTML = descHtml;

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
    uploadModal.classList.remove('hidden');
}

function closeUploadSuccessModal() {
    const uploadModal = document.getElementById('upload-success-modal');
    if (uploadModal) {
        uploadModal.classList.add('hidden');
    }
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

// Download Excel template for crew sign off
function downloadCrewSignOffTemplate() {
    // Create professional template data
    const templateData = [
        // Header section
        ['KUALA TERENGGANU PORT SUPPORT BASE'],
        ['Crew Sign Off Template'],
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
    XLSX.utils.book_append_sheet(wb, ws, 'Crew_Sign_Off_Template');

    // Generate filename with current date
    const today = new Date();
    const dateStr = today.toISOString().split('T')[0].replace(/-/g, '');
    const filename = `KTSB_Crew_Sign_Off_Template_${dateStr}.xlsx`;

    // Download the file
    XLSX.writeFile(wb, filename);
}

// Add event listeners for Excel functionality
document.addEventListener('DOMContentLoaded', function() {
    // Download template functionality
    document.getElementById('download-template').addEventListener('click', function(e) {
        e.preventDefault();
        downloadCrewSignOffTemplate();
    });

    // Excel upload functionality
    document.getElementById('upload-excel-crew').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('excel-file-input').click();
    });

    document.getElementById('excel-file-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleExcelUpload(file);
        }
    });
});

// Profile dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
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
    </script>
<script>
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
<!-- ========================================= -->
<!-- DELETE CONFIRMATION MODAL HTML            -->
<!-- ========================================= -->
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
    const successBtn = document.getElementById('request-success-btn');
    if (successBtn) {
        successBtn.addEventListener('click', function() {
            document.getElementById('request-success-modal').classList.add('hidden');
            document.getElementById('crew-signoff-form').reset();
            if (typeof crewList !== 'undefined') {
                crewList = [];
                if (typeof renderCrewList === 'function') {
                    renderCrewList();
                }
            }
        });
    }
});
</script>
</body>
</html>
