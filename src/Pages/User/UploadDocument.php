<?php
// Protected upload document page - requires authentication
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Function to get role display name
function getRoleDisplayName($role) {
    switch ($role) {
        case 'company':
            return 'Company Admin';
        case 'agent':
            return 'Agent';
        default:
            return 'Company Admin';
    }
}

// Check if user has permission to access this page (only company users can upload documents)
if ($currentUser['role'] !== 'company') {
    header('Location: fuel-water-history.php');
    exit;
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$isOtherServicesActive = in_array($currentPage, ['fuel-water.php', 'light-port-due.php', 'upload_document.php']);
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Upload Document</title>
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
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <aside id="sidebar" class="absolute inset-y-0 left-0 z-20 flex h-[calc(100vh-4rem)] w-64 -translate-x-full flex-col border-r border-[#DEE2E6] bg-background-light transition-transform duration-300 ease-in-out dark:border-gray-700 dark:bg-background-dark overflow-y-auto peer-checked:translate-x-0 lg:fixed lg:translate-x-0 lg:top-16">
            <!-- Scrollable Navigation Section -->
            <div class="flex-1">
                <div class="flex flex-col p-4">
                <div class="flex flex-col gap-4">

                        <nav class="flex flex-col gap-2 mt-4 pb-8">
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'index.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="marine-request.php">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Marine</p>
                            </a>
                            <input id="crew-transfer-toggle" type="checkbox" class="hidden peer" />
                    <label for="crew-transfer-toggle" class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined">group</span>
                            <p class="text-sm font-medium leading-normal">Crew Transfer</p>
                        </span>
                        <span class="material-symbols-outlined transition-transform duration-200 expand-icon-crew-transfer-toggle">expand_more</span>
                    </label>
                    <div id="crew-transfer-submenu" class="ml-6 mt-1 hidden space-y-1 border-l-2 border-orange-500 pl-4">
                        <a href="sign-on.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-on.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_add</span>
                            <p class="text-sm font-medium leading-normal">Sign On</p>
                        </a>
                        <a href="sign-off.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-off.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_remove</span>
                            <p class="text-sm font-medium leading-normal">Sign Off</p>
                        </a>
                    </div>
                            <input id="other-services-toggle" type="checkbox" class="hidden peer" />
                    <label for="other-services-toggle" class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined">build</span>
                            <p class="text-sm font-medium leading-normal">Other Services</p>
                        </span>
                        <span class="material-symbols-outlined transition-transform duration-200 expand-icon-other-services-toggle">expand_more</span>
                    </label>
                    <div id="other-services-submenu" class="ml-6 mt-1 hidden space-y-1 border-l-2 border-orange-500 pl-4">
                        <a href="fuel-water.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'fuel-water.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">water_drop</span>
                            <p class="text-sm font-medium leading-normal">Fuel & Water</p>
                        </a>
                        <a href="light-port-due.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'light-port-due.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">lightbulb</span>
                            <p class="text-sm font-medium leading-normal">Light Port</p>
                        </a>

                    </div>
                    <input id="history-toggle" type="checkbox" class="hidden peer" />
                    <label for="history-toggle" class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined">history</span>
                            <p class="text-sm font-medium leading-normal">History</p>
                        </span>
                        <span class="material-symbols-outlined transition-transform duration-200 expand-icon-history-toggle">expand_more</span>
                    </label>
                    <div id="history-submenu" class="ml-6 mt-1 hidden space-y-1 border-l-2 border-orange-500 pl-4">
                        <a href="marine-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">directions_boat</span>
                            <p class="text-sm font-medium leading-normal">Marine</p>
                        </a>
                        <a href="sign-on-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-on-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_add</span>
                            <p class="text-sm font-medium leading-normal">Crew Sign On</p>
                        </a>
                        <a href="sign-off-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-off-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_remove</span>
                            <p class="text-sm font-medium leading-normal">Crew Sign Off</p>
                        </a>
                        <a href="fuel-water-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'fuel-water-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">water_drop</span>
                            <p class="text-sm font-medium leading-normal">Fuel & Water</p>
                        </a>
                        <a href="light-port-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'light-port-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">lightbulb</span>
                            <p class="text-sm font-medium leading-normal">Light Port History</p>
                        </a>
                    </div>
                            <?php if ($currentUser['user_type'] === 'user'): ?>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'create_agent.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="create_agent.php">
                                <span class="material-symbols-outlined">person_add</span>
                                <p class="text-sm font-medium leading-normal">Create New Agent</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'user_management.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="user_management.php">
                                <span class="material-symbols-outlined">manage_accounts</span>
                                <p class="text-sm font-medium leading-normal">Agent Management</p>
                            </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>


        </aside>
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
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Upload Document</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Upload and manage your documents.</p>
                    </div>

                    <!-- Under Construction Message -->
                    <div class="flex flex-col items-center justify-center min-h-[400px] rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50">
                        <div class="text-center">
                            <div class="mb-4">
                                <span class="material-symbols-outlined text-6xl text-gray-400 dark:text-gray-500">construction</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Under Construction</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">This feature is currently under development.</p>
                            <p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Please check back later for updates.</p>
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

        // Get page name for unique localStorage keys
        const page = window.location.pathname.split('/').pop().split('.')[0] || 'index';

        // Persist toggle states with page prefix
        const toggles = ['crew-transfer-toggle', 'history-toggle', 'other-services-toggle'];

        function loadToggleStates() {
            toggles.forEach(id => {
                const key = id;
                const state = localStorage.getItem(key);
                if (state === 'true') {
                    document.getElementById(id).checked = true;
                }
            });
        }

        function saveToggleState(id) {
            const checkbox = document.getElementById(id);
            const key = id;
            localStorage.setItem(key, checkbox.checked);
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

        // Save sidebar scroll position on page unload
        window.addEventListener('beforeunload', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
            }
        });

// Profile dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Restore sidebar scroll position
    const sidebar = document.getElementById('sidebar');
    const scrollTop = localStorage.getItem('sidebarScrollTop');
    if (sidebar && scrollTop) {
        sidebar.scrollTop = parseInt(scrollTop);
    }

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
</body>
</html>




