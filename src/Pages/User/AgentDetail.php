<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';
require_once __DIR__ . '/../../../config/app.php';
$conn = getDBConnection();
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);

// Check if user has permission to access this page
if ($currentUser['user_type'] !== 'user' && $currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Get agent ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user_management.php');
    exit;
}

$id = (int)$_GET['id'];

// Handle POST actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'deactivate' || $action === 'activate') {
        try {
            $newStatus = ($action === 'deactivate') ? 'inactive' : 'active';
            $stmt = $conn->prepare("UPDATE agents SET status = ? WHERE agent_id = ?");
            $stmt->execute([$newStatus, $id]);

            if ($stmt->rowCount() > 0) {
                $message = 'Agent ' . ($action === 'deactivate' ? 'deactivated' : 'activated') . ' successfully!';
                $messageType = 'success';
            } else {
                $message = 'Agent not found.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log("Agent action error: " . $e->getMessage());
            $message = 'Failed to update agent status.';
            $messageType = 'error';
        }
    }
}

// Fetch agent details
try {
    // Join with users table to get creator's name if possible
    $stmt = $conn->prepare("
        SELECT a.*, u.username as created_by_username 
        FROM agents a 
        LEFT JOIN users u ON a.created_by = u.user_id 
        WHERE a.agent_id = ?
    ");
    $stmt->execute([$id]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agent) {
        // Redirect if agent not found
        header('Location: user_management.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching agent details: " . $e->getMessage());
    header('Location: user_management.php');
    exit;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Agent Detail</title>
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
                        "darkGrey": "#212121",
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
        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-inactive {
            background-color: #FEE2E2;
            color: #991B1B;
        }
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
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

        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="user_management.php" class="hover:text-primary">Agent Management</a>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200"><?php echo htmlspecialchars($agent['full_name']); ?></span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Agent Details</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Detailed information for <?php echo htmlspecialchars($agent['full_name']); ?></p>
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

                    <!-- Details Card -->
                    <div class="w-full bg-white rounded-xl shadow-sm p-6 md:p-8">
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Section 1: Agent Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">Agent Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Full Name</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($agent['full_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Username</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($agent['username']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Email</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($agent['email']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Company Name</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($agent['company_name'] ?? 'N/A'); ?></span>
                                    </div>
                                     <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Customer Code</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($agent['customer_code'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Status</span>
                                        <div class="col-span-2">
                                            <span class="status-pill status-<?php echo strtolower($agent['status']) === 'active' ? 'active' : 'inactive'; ?>"><?php echo ucfirst($agent['status']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Section 2: System Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">System Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Created By</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($agent['created_by_username'] ?? 'System'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Created At</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($agent['created_at']))); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Last Login</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo $agent['last_login'] ? htmlspecialchars(date('M j, Y H:i', strtotime($agent['last_login']))) : 'Never'; ?></span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Action Buttons and Back Button -->
                    <div class="mt-6 flex justify-between items-center">
                        <a href="user_management.php" class="inline-flex items-center gap-2 rounded-lg bg-darkGrey px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-darkGrey/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-darkGrey transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            Back to Agent Management
                        </a>

                        <div class="flex gap-3">
                             <form method="POST" class="inline" onsubmit="return confirm('<?php echo strtolower($agent['status']) === 'active' ? 'Deactivate' : 'Activate'; ?> agent <?php echo htmlspecialchars($agent['full_name']); ?>?')">
                                <input type="hidden" name="action" value="<?php echo strtolower($agent['status']) === 'active' ? 'deactivate' : 'activate'; ?>"/>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg <?php echo strtolower($agent['status']) === 'active' ? 'bg-[#D10000] hover:bg-[#D10000]/90 focus-visible:outline-[#D10000]' : 'bg-[#008F1D] hover:bg-[#008F1D]/90 focus-visible:outline-[#008F1D]'; ?> px-4 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors">
                                    <span class="material-symbols-outlined text-sm"><?php echo strtolower($agent['status']) === 'active' ? 'block' : 'check_circle'; ?></span>
                                    <?php echo strtolower($agent['status']) === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
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
        updateTime(); 

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
            }
        });

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
                updateToggle(); 
            }
        }

        setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
        setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
        setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');
    </script>
</body>
</html>
