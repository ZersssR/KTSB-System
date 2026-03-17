<?php
// Protected profile page - requires authentication
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Fetch additional user details from database
require_once __DIR__ . '/../../../config/app.php';
$conn = getDBConnection();
$userDetails = null;
try {
    if ($currentUser['user_type'] === 'agent') {
        $stmt = $conn->prepare("SELECT created_at, full_name, company_name, phone_number, status FROM agents WHERE agent_id = ?");
        $stmt->execute([$currentUser['agent_id']]);
        $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        // Merge with currentUser for display consistency
        $currentUser['name'] = $userDetails['full_name'];
        $currentUser['company'] = $userDetails['company_name'];
        $currentUser['is_active'] = ($userDetails['status'] === 'active');
    } else {
        $stmt = $conn->prepare("SELECT created_at FROM users WHERE user_id = ?");
        $stmt->execute([$currentUser['id']]);
        $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching user details: " . $e->getMessage());
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

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
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link rel="icon" href="KTSB Logo.png" type="image/png"/>
    <title>Profile</title>
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
        <aside class="absolute inset-y-0 left-0 z-20 flex h-[calc(100vh-4rem)] w-64 -translate-x-full flex-col border-r border-[#DEE2E6] bg-background-light transition-transform duration-300 ease-in-out dark:border-gray-700 dark:bg-background-dark overflow-y-auto peer-checked:translate-x-0 lg:fixed lg:translate-x-0 lg:top-16">
            <!-- Scrollable Navigation Section -->
            <div class="flex-1">
            <div id="sidebar-scroll-container" class="flex flex-col p-4">
                <div class="flex flex-col gap-4">

                        <nav class="flex flex-col gap-2 mt-4 pb-2">
                            <!-- Agent Navigation -->
                            <?php if ($currentUser['role'] === 'agent'): ?>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'dashboard.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="dashboard.php">
                                <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
  <rect x="10" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
  <rect x="34" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
  <rect x="10" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
  <rect x="34" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
</svg>
                                <p class="text-sm font-medium leading-normal">Dashboard</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="marine-history.php">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Marine</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-on-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="sign-on-history.php">
                                <span class="material-symbols-outlined">person_add</span>
                                <p class="text-sm font-medium leading-normal">Crew Sign On</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-off-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="sign-off-history.php">
                                <span class="material-symbols-outlined">person_remove</span>
                                <p class="text-sm font-medium leading-normal">Crew Sign Off</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'fuel-water-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="fuel-water-history.php">
                                <span class="material-symbols-outlined">water_drop</span>
                                <p class="text-sm font-medium leading-normal">Fuel & Water</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'light-port-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="light-port-history.php">
                                <span class="material-symbols-outlined">lightbulb</span>
                                <p class="text-sm font-medium leading-normal">Light Port</p>
                            </a>
                            <?php else: ?>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'index.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="index.php">
                                <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
  <rect x="10" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
  <rect x="34" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
  <rect x="10" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
  <rect x="34" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5"/>
</svg>
                                <p class="text-sm font-medium leading-normal">Dashboard</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-request.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>" href="marine-request.php">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Marine</p>
                            </a>
                            <input id="other-services-toggle" type="checkbox" class="hidden peer" />
                    <label for="other-services-toggle" class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined">build</span>
                            <p class="text-sm font-medium leading-normal">Other Services</p>
                        </span>
                        <span class="material-symbols-outlined transition-transform duration-200 expand-icon-other-services-toggle">expand_more</span>
                    </label>
                    <div id="other-services-submenu" class="ml-6 mt-1 hidden space-y-1 border-l-2 border-orange-500 pl-4">
                        <a href="sign-on.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-on.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_add</span>
                            <p class="text-sm font-medium leading-normal">Crew Sign On</p>
                        </a>
                        <a href="sign-off.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-off.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_remove</span>
                            <p class="text-sm font-medium leading-normal">Crew Sign Off</p>
                        </a>
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
                            <p class="text-sm font-medium leading-normal">Marine History</p>
                        </a>
                        <a href="sign-on-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-on-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_add</span>
                            <p class="text-sm font-medium leading-normal">Crew Sign On History</p>
                        </a>
                        <a href="sign-off-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'sign-off-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_remove</span>
                            <p class="text-sm font-medium leading-normal">Crew Sign Off History</p>
                        </a>
                        <a href="fuel-water-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'fuel-water-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">water_drop</span>
                            <p class="text-sm font-medium leading-normal">Fuel & Water History</p>
                        </a>
                        <a href="light-port-history.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'light-port-history.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">lightbulb</span>
                            <p class="text-sm font-medium leading-normal">Light Port History</p>
                        </a>
                    </div>
                            <?php if ($currentUser['user_type'] === 'user'): ?>
                            <input id="agent-toggle" type="checkbox" class="hidden peer" />
                    <label for="agent-toggle" class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined">group</span>
                            <p class="text-sm font-medium leading-normal">Agent</p>
                        </span>
                        <span class="material-symbols-outlined transition-transform duration-200 expand-icon-agent-toggle">expand_more</span>
                    </label>
                    <div id="agent-submenu" class="ml-6 mt-1 hidden space-y-1 border-l-2 border-orange-500 pl-4">
                        <a href="create_agent.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'create_agent.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">person_add</span>
                            <p class="text-sm font-medium leading-normal">Create New Agent</p>
                        </a>
                        <a href="user_management.php" class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'user_management.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                            <span class="material-symbols-outlined">manage_accounts</span>
                            <p class="text-sm font-medium leading-normal">Agent Management</p>
                        </a>
                    </div>
                            <?php endif; ?>
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
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">User Profile</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">View your personal information.</p>
                    </div>

                    <!-- Details Card -->
                    <div class="w-full bg-white rounded-xl shadow-sm p-6 md:p-8">
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Section 1: User Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">User Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Role:</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars(getRoleDisplayName($currentUser['role'])); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Company:</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($currentUser['company'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Status:</span>
                                        <div class="col-span-2">
                                            <span class="inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Section 2: Account Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">Account Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Username:</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Name:</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($currentUser['name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Email:</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Created:</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo isset($userDetails['created_at']) ? htmlspecialchars(date('M j, Y H:i', strtotime($userDetails['created_at']))) : 'N/A'; ?></span>
                                    </div>
                                </div>
                            </section>
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

        // Persist toggle states globally across all pages
        const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

        function loadToggleStates() {
            toggles.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    const state = localStorage.getItem(id);
                    if (state !== null) {
                        element.checked = state === 'true';
                    }
                    // If no saved state, keep the default checked state from HTML
                }
            });
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

        // Profile dropdown functionality
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

        // Sidebar scroll position functionality
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
</body>
</html>
