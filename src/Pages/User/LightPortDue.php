<?php
// Protected light-port page - requires authentication and appropriate role
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Check if user has permission to access this page (only company and admin users can make requests)
if ($currentUser['user_type'] !== 'user' && $currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Light Port Services</title>
    <script src="assets/js/tailwindcss.js"></script>
    <link href="assets/css/fonts.css" rel="stylesheet"/>
    <link href="assets/css/material-icons.css" rel="stylesheet"/>
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
<main class="flex-1 overflow-y-auto p-4 md:p-8">
<div class="mx-auto max-w-7xl">
<!-- Page Heading -->
<div class="mb-8">
<h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Light Port Due</h2>
<p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Manage port light fees for vessel entries.</p>
</div>

<!-- Form -->
<form id="light-port-form" class="space-y-8 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
<!-- General Information Section -->
<div class="space-y-4">
<h3 class="border-b border-[#DEE2E6] pb-2 text-lg font-bold text-[#212529] dark:border-gray-700 dark:text-gray-200">General Information</h3>
<div class="grid grid-cols-1 gap-6 pt-2 md:grid-cols-2">
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Vessel Name</p>
<input list="vessel-options" id="vessel-name" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="Select or Type Vessel Name"/>
<datalist id="vessel-options">
    <option value="Alkahfi Gentle">
    <option value="Alkahfi Care">
    <option value="Alkahfi Chief">
    <option value="Alkahfi Pride">
    <option value="Blue Petra 2">
    <option value="Alkahfi Asura">
    <option value="Marine Success">
    <option value="Pelican Cheer">
    <option value="Alkahfi Courage">
    <option value="Marine Courage">
    <option value="Surya Halima">
    <option value="Gen 4 One">
    <option value="Jati Four">
    <option value="Tegas Madani">
    <option value="Alkahfi Grace">
    <option value="Ph Prestij">
    <option value="Blue Petra 1">
</datalist>
</label>
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Date & Time</p>
<div class="flex gap-2">
<input id="booking-date" class="form-input flex-1 rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="date"/>
<select id="booking-time" class="form-select flex-1 rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
<option value="" disabled selected>Time</option>
</select>
</div>
</label>
</div>
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Services (Free Tax)</p>
<textarea id="services" class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary resize-none" rows="3" placeholder="Describe the services required"></textarea>
</label>
<label class="flex flex-col">
<p class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Volume/Unit</p>
<input id="volume-unit" class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" type="number" placeholder="Enter volume or unit"/>
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
<!-- Form Actions -->
<div class="flex items-center justify-end gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
<button type="button" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Cancel</button>
<button type="submit" class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121]">Submit Request</button>
</div>
</form>
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
// Update time
function updateTime() {
    const date = new Date();
    document.getElementById('date').innerText = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}).toUpperCase();
    document.getElementById('time').innerText = date.toLocaleTimeString('en-US', {hour12: false});
}
setInterval(updateTime, 1000);
updateTime();

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

// Save and restore sidebar scroll position
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const scrollTop = localStorage.getItem('sidebarScrollTop');
        if (scrollTop) {
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
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('booking-date');
        const timeInput = document.getElementById('booking-time');

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

// Main form submission
document.getElementById('light-port-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const vesselName = document.getElementById('vessel-name').value;
    const bookingDate = document.getElementById('booking-date').value;
    const bookingTime = document.getElementById('booking-time').value;
    const services = document.getElementById('services').value.trim();
    const volumeUnit = document.getElementById('volume-unit').value;
    const assignedAgentId = document.getElementById('assigned-agent').value;

    if (!vesselName || !bookingDate || !bookingTime || !services || !volumeUnit) {
        alert('Please fill in all required fields.');
        return;
    }

    try {
        const tabId = new URLSearchParams(window.location.search).get('tab_id');
        const url = tabId ? `api/light_port_request.php?tab_id=${encodeURIComponent(tabId)}` : 'api/light_port_request.php';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                vesselName: vesselName,
                requestDate: bookingDate,
                requestTime: bookingTime,
                services: services,
                volumeUnit: volumeUnit,
                assignedAgentId: assignedAgentId || null
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
            alert('Error submitting Light Port request: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting the Light Port request.');
    }
});

// Success Modal Logic
document.addEventListener('DOMContentLoaded', function() {
    const successModal = document.getElementById('request-success-modal');
    const successBtn = document.getElementById('request-success-btn');

    if (successBtn) {
        successBtn.addEventListener('click', function() {
            successModal.classList.add('hidden');
            document.getElementById('light-port-form').reset();
        });
    }
});

// Profile dropdown functionality
document.addEventListener('DOMContentLoaded', function() {

    const profileBtn = document.getElementById('profile-dropdown-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                profileDropdown.classList.add('hidden');
            }
        });
    }
});
</script>
</body>
</html>
