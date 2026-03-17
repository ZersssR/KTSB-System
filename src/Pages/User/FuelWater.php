<?php
// Protected fuel-water page - requires authentication and appropriate role
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
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

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>

<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Fuel Water Supply</title>
    <script src="assets/js/tailwindcss.js"></script>
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
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
<div class="relative flex h-screen w-full">
<!-- Collapsible SideNavBar -->
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
<!-- Page Heading -->
<div class="mb-8">
<h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Fuel & Water Supply</h2>
<p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Request fuel and water supply for vessel operations.</p>
</div>
<!-- Form -->
<form id="fuel-water-form" class="space-y-8 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
<!-- General Information Section -->
<div class="space-y-4">
<h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">General Information</h3>
<div class="grid grid-cols-1 gap-6 pt-2 md:grid-cols-2">
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Supply To</p>
<input id="supply-to" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
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
</div>
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">PO Number</p>
<input id="po-number" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="text"/>
</label>
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Booking Date</p>
<input id="booking-date" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
</label>
</div>
<div class="grid grid-cols-1 gap-6 pt-2 md:grid-cols-2" id="assign-agent-container" style="display: none;">
    <label class="flex flex-col">
        <p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Assign to Agent</p>
        <select id="assigned-agent" class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
            <option value="">Unassigned</option>
        </select>
    </label>
</div>

</div>
</div>

<div class="mb-8"></div>

<!-- Supply Items Section -->
<div class="rounded-lg border border-[#DEE2E6] bg-white dark:border-gray-700 dark:bg-gray-800/20 p-6 space-y-4">
<h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">Supply Items</h3>
<div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-6" id="supply-form">
<!-- Fuel Supply Card -->
<div class="border border-[#DEE2E6] rounded-lg p-6 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/50">
<div class="flex items-center gap-3 mb-4">
<input type="checkbox" id="fuel-checkbox" class="form-checkbox w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded">
<label class="text-base font-semibold text-[#212529] dark:text-gray-300">Fuel Supply</label>
</div>
<div class="space-y-4">
<label class="block">
<span class="text-sm font-medium text-[#212529] dark:text-gray-300">Booking Time</span>
<select id="fuel-booking-time" class="mt-1 form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed" disabled>
    <option value="" disabled selected>Select Time</option>
</select>
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
<input type="checkbox" id="water-checkbox" class="form-checkbox w-5 h-5 text-primary focus:ring-primary border-gray-300 rounded">
<label class="text-base font-semibold text-[#212529] dark:text-gray-300">Water Supply</label>
</div>
<div class="space-y-4">
<label class="block">
<span class="text-sm font-medium text-[#212529] dark:text-gray-300">Booking Time</span>
<select id="water-booking-time" class="mt-1 form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed" disabled>
    <option value="" disabled selected>Select Time</option>
</select>
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
<div class="flex items-center justify-end gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
<button type="button" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Cancel</button>
<button type="submit" class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121]">Submit Request</button>
</div>
</form>
</div>
</div>
</main>
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
function updateTime() {
    const date = new Date();
    document.getElementById('date').innerText = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}).toUpperCase();
    document.getElementById('time').innerText = date.toLocaleTimeString('en-US', {hour12: false});
}
setInterval(updateTime, 1000);
updateTime(); // initial

// Get page name for unique localStorage keys
const page = window.location.pathname.split('/').pop().split('.')[0] || 'index';

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
    if (state !== null) {
        document.getElementById(id).checked = state === 'true';
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

// Special handling for other-services-toggle to load state from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const otherServicesToggle = document.getElementById('other-services-toggle');
    if (otherServicesToggle) {
        loadToggleState('other-services-toggle');
        // Trigger the setupToggle update after loading state
        otherServicesToggle.dispatchEvent(new Event('change'));
    }
});

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
setupSupplyCheckbox('fuel-checkbox', 'fuel-booking-time', 'fuel-quantity', 'fuel-remarks-container', 'fuel-remarks');
setupSupplyCheckbox('water-checkbox', 'water-booking-time', 'water-quantity', 'water-remarks-container', 'water-remarks');

// Load active agents
async function loadActiveAgents() {
    try {
        const tabId = new URLSearchParams(window.location.search).get('tab_id');
        const url = tabId ? `api/get_active_agents.php?tab_id=${encodeURIComponent(tabId)}` : 'api/get_active_agents.php';
        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.agents.length > 0) {
            const agentSelect = document.getElementById('assigned-agent');
            const agentContainer = document.getElementById('assign-agent-container');
            
            // Clear existing options except the first one
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
            agentContainer.style.display = 'grid';
        }
    } catch (error) {
        console.error('Error loading agents:', error);
    }
}

document.addEventListener('DOMContentLoaded', loadActiveAgents);

// Date and Time Validation Logic
// Date and Time Validation Logic
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('booking-date');
    const fuelTimeInput = document.getElementById('fuel-booking-time');
    const waterTimeInput = document.getElementById('water-booking-time');

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

    if (fuelTimeInput) generateTimeOptions(fuelTimeInput);
    if (waterTimeInput) generateTimeOptions(waterTimeInput);

    function updateMinDateTime() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayDate = `${year}-${month}-${day}`;
        const currentHour = now.getHours();
        
        if (dateInput) {
            dateInput.min = todayDate;
        }

        const updateTimeOptions = (timeInput) => {
            if (timeInput && dateInput.value) {
                const options = timeInput.options;
                if (dateInput.value === todayDate) {
                    for (let i = 1; i < options.length; i++) {
                        const hour = parseInt(options[i].value.split(':')[0]);
                        if (hour < currentHour || (hour === currentHour && currentMinutes > 0)) {
                            options[i].disabled = true;
                            // options[i].style.display = 'none'; // Keep visible but disabled
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
        };

        updateTimeOptions(fuelTimeInput);
        updateTimeOptions(waterTimeInput);
    }

    function validateDateTime() {
        updateMinDateTime();
    }

    if (dateInput) {
        dateInput.addEventListener('change', validateDateTime);
        dateInput.addEventListener('focus', updateMinDateTime);
    }

    if (fuelTimeInput) {
        fuelTimeInput.addEventListener('change', validateDateTime);
        fuelTimeInput.addEventListener('focus', updateMinDateTime);
    }

    if (waterTimeInput) {
        waterTimeInput.addEventListener('change', validateDateTime);
        waterTimeInput.addEventListener('focus', updateMinDateTime);
    }
    
    // Initial call
    updateMinDateTime();
});

// Main form submission
document.getElementById('fuel-water-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const supplyTo = document.getElementById('supply-to').value;
    const vesselName = document.getElementById('vessel-name').value;
    const poNumber = document.getElementById('po-number').value;
    const bookingDate = document.getElementById('booking-date').value;
    const assignedAgentId = document.getElementById('assigned-agent').value;

    const fuelWaterRequests = [];

    // Check fuel supply
    const fuelCheckbox = document.getElementById('fuel-checkbox');
    if (fuelCheckbox.checked) {
        const fuelTime = document.getElementById('fuel-booking-time').value;
        const fuelQuantity = document.getElementById('fuel-quantity').value;
        const fuelRemarks = document.getElementById('fuel-remarks').value.trim();

        if (!fuelTime || !fuelQuantity) {
            alert('Please fill in both booking time and quantity for fuel supply.');
            return;
        }

        fuelWaterRequests.push({
            type: 'fuel',
            bookingTime: fuelTime,
            quantity: fuelQuantity,
            remarks: fuelRemarks || null
        });
    }

    // Check water supply
    const waterCheckbox = document.getElementById('water-checkbox');
    if (waterCheckbox.checked) {
        const waterTime = document.getElementById('water-booking-time').value;
        const waterQuantity = document.getElementById('water-quantity').value;
        const waterRemarks = document.getElementById('water-remarks').value.trim();

        if (!waterTime || !waterQuantity) {
            alert('Please fill in both booking time and quantity for water supply.');
            return;
        }

        fuelWaterRequests.push({
            type: 'water',
            bookingTime: waterTime,
            quantity: waterQuantity,
            remarks: waterRemarks || null
        });
    }

    if (fuelWaterRequests.length === 0) {
        alert('Please select at least one supply item (fuel or water) and fill in the required details.');
        return;
    }

    try {
        const tabId = new URLSearchParams(window.location.search).get('tab_id');
        const url = tabId ? `api/fuel_water_request.php?tab_id=${encodeURIComponent(tabId)}` : 'api/fuel_water_request.php';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                berthRequest: { vesselName: vesselName, poNumber: poNumber }, // Pass relevant berth data
                fuelWaterRequests: fuelWaterRequests,
                fuelWaterGeneral: { bookingDate: bookingDate, assignedAgentId: assignedAgentId }
            })
        });
        const result = await response.json();
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
            alert('Error submitting Fuel & Water request: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting the Fuel & Water request.');
    }
});

// Success Modal Logic
document.addEventListener('DOMContentLoaded', function() {
    const successModal = document.getElementById('request-success-modal');
    const successBtn = document.getElementById('request-success-btn');

    if (successBtn) {
        successBtn.addEventListener('click', function() {
            successModal.classList.add('hidden');
            document.getElementById('fuel-water-form').reset();
            // Re-initialize checkbox states after reset
            setupSupplyCheckbox('fuel-checkbox', 'fuel-booking-time', 'fuel-quantity', 'fuel-remarks-container', 'fuel-remarks');
            setupSupplyCheckbox('water-checkbox', 'water-booking-time', 'water-quantity', 'water-remarks-container', 'water-remarks');
        });
    }
});

// Save and restore sidebar scroll position
document.addEventListener('DOMContentLoaded', function() {
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
        }
    }
});

window.addEventListener('beforeunload', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
    }
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
</body></html>
