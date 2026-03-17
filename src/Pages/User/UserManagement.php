<?php
// Protected user management page - requires authentication and user role
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Check if user has permission to access this page
if ($currentUser['user_type'] !== 'user') {
    header('Location: index.php');
    exit;
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Handle agent deactivation/reactivation
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $agentId = $_POST['agent_id'] ?? '';
    $action = $_POST['action'];

    if (!empty($agentId) && in_array($action, ['deactivate', 'activate'])) {
        require_once __DIR__ . '/../../../config/app.php';
        $conn = getDBConnection();

        try {
            $newStatus = ($action === 'deactivate') ? 'inactive' : 'active';
            $stmt = $conn->prepare("UPDATE agents SET status = ? WHERE agent_id = ?");
            $stmt->execute([$newStatus, $agentId]);

            if ($stmt->rowCount() > 0) {
                $message = 'Agent ' . ($action === 'deactivate' ? 'deactivated' : 'activated') . ' successfully!';
                $messageType = 'success';
            } else {
                $message = 'Agent not found.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log("Agent management error: " . $e->getMessage());
            $message = 'Failed to update agent status.';
            $messageType = 'error';
        }
    }
}

// Get all agents
require_once __DIR__ . '/../../../config/app.php';
$conn = getDBConnection();
$agents = [];

try {
    // Fetch agents based on customer_code (same company)
    // If user has no customer_code (legacy), fallback to created_by or empty
    if (!empty($currentUser['customer_code'])) {
        $stmt = $conn->prepare("SELECT a.agent_id, a.username, a.email, a.full_name, a.company_name, a.created_at, a.last_login, a.status, u.username as created_by_username 
                               FROM agents a 
                               LEFT JOIN users u ON a.created_by = u.user_id 
                               WHERE a.customer_code = ? 
                               ORDER BY a.created_at DESC");
        $stmt->execute([$currentUser['customer_code']]);
    } else {
        // Fallback for legacy users without customer_code
        $stmt = $conn->prepare("SELECT a.agent_id, a.username, a.email, a.full_name, a.company_name, a.created_at, a.last_login, a.status, u.username as created_by_username 
                               FROM agents a 
                               LEFT JOIN users u ON a.created_by = u.user_id 
                               WHERE a.created_by = ? 
                               ORDER BY a.created_at DESC");
        $stmt->execute([$currentUser['id']]);
    }
    $agents = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching agents: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Agent Management</title>
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

        /* Table Header styling */
        thead {
            background-color: #212121;
            color: #ffffff;
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
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Agent Management</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Manage agent accounts and permissions.</p>
                    </div>

                    <!-- Message Display -->
                    <?php if ($message): ?>
                    <div class="mb-6 rounded-lg border p-4 <?php echo $messageType === 'success' ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined mr-2"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Search and Filters Card -->
                    <div class="mb-6">
                        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg shadow-gray-200/20 dark:shadow-gray-900/20 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="material-symbols-outlined text-primary text-xl">filter_list</span>
                                <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200">Filter Agents</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Search Input -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-lg">search</span>
                                    </div>
                                    <input type="text" id="search" placeholder="Search by name or email..." class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm placeholder-gray-500 dark:placeholder-gray-400">
                                </div>
                                <!-- Status Filter -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-lg">check_circle</span>
                                    </div>
                                    <select id="status-filter" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                        <option value="">All Status</option>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agents Table -->
                    <div class="rounded-lg border border-[#DEE2E6] bg-white dark:border-gray-700 dark:bg-gray-800/20">
                        <div class="border-b border-[#DEE2E6] px-6 py-4 bg-[#242424] dark:border-gray-700">
                            <h3 class="text-lg font-bold text-white dark:text-gray-200">All Agents</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-black">
                                    <tr>
                                        <th class="px-6 py-5 text-left text-xs font-medium text-black dark:text-gray-400 uppercase tracking-wider">Agent</th>
                                        <th class="px-6 py-5 text-left text-xs font-medium text-black dark:text-gray-400 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-5 text-left text-xs font-medium text-black dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-5 text-left text-xs font-medium text-black dark:text-gray-400 uppercase tracking-wider">Created By</th>
                                        <th class="px-6 py-5 text-left text-xs font-medium text-black dark:text-gray-400 uppercase tracking-wider">Last Login</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800/20 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($agents as $agent): ?>
                                    <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" data-agent-id="<?php echo $agent['agent_id']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-gray-500 dark:text-gray-400">person</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-[#212529] dark:text-gray-200"><?php echo htmlspecialchars($agent['full_name']); ?></div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($agent['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                <?php echo htmlspecialchars($agent['company_name']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $agent['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; ?>">
                                                <?php echo ucfirst($agent['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-[#212529] dark:text-gray-200">
                                                <?php echo htmlspecialchars($agent['created_by_username'] ?? 'System'); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo date('d M Y', strtotime($agent['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#212529] dark:text-gray-200">
                                            <?php echo $agent['last_login'] ? htmlspecialchars(date('j M Y, H:i', strtotime($agent['last_login']))) : 'Never'; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($agents)): ?>
                        <div class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-6xl text-gray-400 dark:text-gray-500 mb-4">group</span>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No agents found</h3>
                            <p class="text-gray-500 dark:text-gray-400">There are no agents in the system.</p>
                        </div>
                        <?php endif; ?>
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
        const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

        function loadToggleStates() {
            toggles.forEach(id => {
                const key = id;
                const state = localStorage.getItem(key);
                if (state === 'true') {
                    const element = document.getElementById(id);
                    if (element) {
                        element.checked = true;
                    }
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

        setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
        setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
        setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

        // Save and restore sidebar scroll position
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                const scrollTop = localStorage.getItem('sidebarScrollTop');
                if (scrollTop) {
                    sidebar.scrollTop = parseInt(scrollTop);
                }
                
                // Save on scroll
                sidebar.addEventListener('scroll', function() {
                    localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
                });
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

// Filter functionality
document.getElementById('search').addEventListener('input', filterTable);
document.getElementById('status-filter').addEventListener('change', filterTable);

function filterTable() {
    const search = document.getElementById('search').value.toLowerCase();
    const status = document.getElementById('status-filter').value;
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const userCell = row.cells[0];
        const statusCell = row.cells[2];
        const username = userCell.querySelector('.text-sm.font-medium').textContent.toLowerCase();
        const email = userCell.querySelector('.text-sm.text-gray-500').textContent.toLowerCase();
        const statusText = statusCell.querySelector('span').textContent.trim();
        const matchesSearch = username.includes(search) || email.includes(search);
        const matchesStatus = !status || statusText === status;
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Clickable row functionality
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('tbody tr[data-agent-id]');
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            const agentId = this.getAttribute('data-agent-id');
            if (agentId) {
                // Get tab_id from URL or storage to maintain session
                const urlParams = new URLSearchParams(window.location.search);
                const tabId = urlParams.get('tab_id') || sessionStorage.getItem('ktsb_tab_id');
                
                let url = `agent_detail.php?id=${agentId}`;
                if (tabId) {
                    url += `&tab_id=${tabId}`;
                }
                window.location.href = url;
            }
        });
    });
});


    </script>
</body>
</html>
