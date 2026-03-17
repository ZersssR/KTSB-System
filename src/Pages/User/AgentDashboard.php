<?php
// Dashboard page - only accessible to agent accounts
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Check if user is an agent
if ($currentUser['role'] !== 'agent') {
    header('Location: index.php');
    exit;
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Get some statistics for the dashboard
$conn = getDBConnection();

try {
    // For agents, filter by assigned_agent_id; for others, use company
    $filterColumn = ($currentUser['role'] === 'agent') ? 'assigned_agent_id' : 'company';
    $filterValue = ($currentUser['role'] === 'agent') ? $currentUser['id'] : ($currentUser['company'] ?? 'Marine Co.');

    // Count of marine requests for this agent/company
    $stmt = $conn->prepare("SELECT COUNT(*) as total_marine FROM marine_requests WHERE $filterColumn = ?");
    $stmt->execute([$filterValue]);
    $marineCount = $stmt->fetch()['total_marine'];

    // Count of fuel/water requests
    $stmt = $conn->prepare("SELECT COUNT(*) as total_fuel_water FROM fuel_water_requests WHERE $filterColumn = ?");
    $stmt->execute([$filterValue]);
    $fuelWaterCount = $stmt->fetch()['total_fuel_water'];

    // Count of crew sign on requests
    $stmt = $conn->prepare("SELECT COUNT(*) as total_sign_on FROM crew_sign_on_requests WHERE $filterColumn = ?");
    $stmt->execute([$filterValue]);
    $signOnCount = $stmt->fetch()['total_sign_on'];

    // Count of crew sign off requests
    $stmt = $conn->prepare("SELECT COUNT(*) as total_sign_off FROM crew_sign_off_requests WHERE $filterColumn = ?");
    $stmt->execute([$filterValue]);
    $signOffCount = $stmt->fetch()['total_sign_off'];

    $crewTransferCount = $signOnCount + $signOffCount;

    // Recent activity (last 5 requests)
    $stmt = $conn->prepare("
        (SELECT 
            CASE 
                WHEN (mr.crew_transfer_type IS NULL AND mr.fuel_water_data IS NULL AND mr.general_works_data IS NULL) THEN 'berth' 
                ELSE 'marine' 
            END as type, 
            mr.id, CONCAT(mr.vessel_name, ' (', IFNULL(u.company_name, 'Unknown'), ')') as name, mr.created_at as request_date, mr.status 
         FROM marine_requests mr LEFT JOIN users u ON mr.user_id = u.user_id 
         WHERE $filterColumn = ? ORDER BY mr.created_at DESC LIMIT 5)
        UNION
        (SELECT 'fuel_water' as type, fw.id, CONCAT(fw.vessel_name, ' (', IFNULL(u.company_name, 'Unknown'), ')') as name, fw.request_date, fw.status 
         FROM fuel_water_requests fw LEFT JOIN users u ON fw.user_id = u.user_id 
         WHERE $filterColumn = ? ORDER BY fw.request_date DESC LIMIT 5)
        UNION
        (SELECT 'crew_sign_on' as type, cson.id, CONCAT(cson.vessel_name, ' - ', (SELECT COUNT(*) FROM crew_sign_on_details WHERE request_id = cson.id), ' crew (', IFNULL(u.company_name, 'Unknown'), ')') as name, cson.request_date, cson.status 
         FROM crew_sign_on_requests cson LEFT JOIN users u ON cson.user_id = u.user_id 
         WHERE $filterColumn = ? ORDER BY cson.request_date DESC LIMIT 5)
        UNION
        (SELECT 'crew_sign_off' as type, csoff.id, CONCAT(csoff.vessel_name, ' - ', (SELECT COUNT(*) FROM crew_sign_off_details WHERE request_id = csoff.id), ' crew (', IFNULL(u.company_name, 'Unknown'), ')') as name, csoff.request_date, csoff.status 
         FROM crew_sign_off_requests csoff LEFT JOIN users u ON csoff.user_id = u.user_id 
         WHERE $filterColumn = ? ORDER BY csoff.request_date DESC LIMIT 5)
        UNION
        (SELECT 'light_port' as type, lpr.id, CONCAT(lpr.vessel_name, ' (', IFNULL(lpr.company_name, IFNULL(u.company_name, 'Unknown')), ')') as name, lpr.request_date, lpr.status 
         FROM light_port_requests lpr LEFT JOIN users u ON lpr.user_id = u.user_id 
         WHERE $filterColumn = ? ORDER BY lpr.request_date DESC LIMIT 5)
        ORDER BY request_date DESC LIMIT 5
    ");
    $stmt->execute([$filterValue, $filterValue, $filterValue, $filterValue, $filterValue]);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Latest request assigned to this agent
    $stmt = $conn->prepare("
        (SELECT 
            CASE 
                WHEN (mr.crew_transfer_type IS NULL AND mr.fuel_water_data IS NULL AND mr.general_works_data IS NULL) THEN 'berth' 
                ELSE 'marine' 
            END as type, 
            mr.id, CONCAT(mr.vessel_name, ' (', IFNULL(u.company_name, 'Unknown'), ')') as name, mr.created_at as request_date, mr.status 
         FROM marine_requests mr LEFT JOIN users u ON mr.user_id = u.user_id 
         WHERE mr.assigned_agent_id = ? ORDER BY mr.created_at DESC LIMIT 1)
        UNION
        (SELECT 'fuel_water' as type, fw.id, CONCAT(fw.vessel_name, ' (', IFNULL(u.company_name, 'Unknown'), ')') as name, fw.request_date, fw.status 
         FROM fuel_water_requests fw LEFT JOIN users u ON fw.user_id = u.user_id 
         WHERE fw.assigned_agent_id = ? ORDER BY fw.request_date DESC LIMIT 1)
        UNION
        (SELECT 'crew_sign_on' as type, cson.id, CONCAT(cson.vessel_name, ' - ', (SELECT COUNT(*) FROM crew_sign_on_details WHERE request_id = cson.id), ' crew (', IFNULL(u.company_name, 'Unknown'), ')') as name, cson.request_date, cson.status 
         FROM crew_sign_on_requests cson LEFT JOIN users u ON cson.user_id = u.user_id 
         WHERE cson.assigned_agent_id = ? ORDER BY cson.request_date DESC LIMIT 1)
        UNION
        (SELECT 'crew_sign_off' as type, csoff.id, CONCAT(csoff.vessel_name, ' - ', (SELECT COUNT(*) FROM crew_sign_off_details WHERE request_id = csoff.id), ' crew (', IFNULL(u.company_name, 'Unknown'), ')') as name, csoff.request_date, csoff.status 
         FROM crew_sign_off_requests csoff LEFT JOIN users u ON csoff.user_id = u.user_id 
         WHERE csoff.assigned_agent_id = ? ORDER BY csoff.request_date DESC LIMIT 1)
        UNION
        (SELECT 'light_port' as type, lpr.id, CONCAT(lpr.vessel_name, ' (', IFNULL(lpr.company_name, IFNULL(u.company_name, 'Unknown')), ')') as name, lpr.request_date, lpr.status 
         FROM light_port_requests lpr LEFT JOIN users u ON lpr.user_id = u.user_id 
         WHERE lpr.assigned_agent_id = ? ORDER BY lpr.request_date DESC LIMIT 1)
        ORDER BY request_date DESC LIMIT 1
    ");
    $stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id'], $currentUser['id'], $currentUser['id']]);
    $latestAssigned = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $marineCount = 0;
    $fuelWaterCount = 0;
    $signOnCount = 0;
    $signOffCount = 0;
    $crewTransferCount = 0;
    $recentActivity = [];
    $latestAssigned = null;
}

// Function to get status color
function getStatusColor($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return 'text-yellow-600 bg-yellow-50 border-yellow-200';
        case 'approved':
        case 'completed':
        case 'supplied':
            return 'text-green-600 bg-green-50 border-green-200';
        case 'rejected':
            return 'text-red-600 bg-red-50 border-red-200';
        default:
            return 'text-gray-600 bg-gray-50 border-gray-200';
    }
}
?>

<?php
// Check if user needs to change password (if it matches default 'password123')
$showPasswordChangeModal = false;
try {
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$currentUser['id']]);
    $userHash = $stmt->fetchColumn();

    if ($userHash && password_verify('password123', $userHash)) {
        $showPasswordChangeModal = true;
    }
} catch (PDOException $e) {
    // Ignore error, just don't show modal
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Kuala Terengganu Support Base</title>
    <script src="assets/js/tailwindcss.js"></script>
    <script src="assets/js/xlsx.full.min.js"></script>
    <link href="assets/css/fonts.css" rel="stylesheet" />
    <link href="assets/css/material-icons.css" rel="stylesheet" />
    <link href="assets/css/vt323.css" rel="stylesheet" />
    <!-- Babel Standalone for React -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
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
    </style>
    <script src="tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <!-- Collapsible SideNavBar -->
        <?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
        <!-- Activity tracking script -->
        <script>
                (function () {
                    let activityTimeout;

                    function updateActivity() {
                        // Get tab_id from URL or storage to ensure session persistence
                        const urlParams = new URLSearchParams(window.location.search);
                        const tabId = urlParams.get('tab_id') || sessionStorage.getItem('ktsb_tab_id');

                        let url = 'update_activity.php';
                        if (tabId) {
                            url += '?tab_id=' + encodeURIComponent(tabId);
                        }

                        // Send AJAX request to update activity
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({}),
                        })
                            .then(response => response.json())
                            .then(data => {
                                // Activity updated successfully
                            })
                            .catch(error => {
                                console.error('Error updating activity:', error);
                            });
                    }

                    function resetActivityTimeout() {
                        clearTimeout(activityTimeout);
                        activityTimeout = setTimeout(updateActivity, 30000); // Update every 30 seconds if no activity
                    }

                    function handleUserActivity() {
                        updateActivity(); // Immediate update on activity
                        resetActivityTimeout();
                    }

                    // Listen for user activity events
                    document.addEventListener('mousemove', handleUserActivity);
                    document.addEventListener('keydown', handleUserActivity);
                    document.addEventListener('click', handleUserActivity);
                    document.addEventListener('scroll', handleUserActivity);
                    document.addEventListener('touchstart', handleUserActivity); // For mobile

                    // Initial activity update
                    handleUserActivity();
                })();
        </script>
        <!-- Header Bar -->
        <header
            class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <img src="assets/images/KSB Logo.JPG" alt="KSB Logo"
                    class="h-10 w-auto object-contain mr-1 hidden md:block">
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Kuala Terengganu Support
                    Base</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button type="button" id="profile-dropdown-btn"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors"
                        title="Profile">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span
                            class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['username']); ?></span>
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
        <!-- Main content area -->
        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <!-- Dashboard Content -->
            <main class="relative flex-1 overflow-y-auto p-4 md:p-8">
                <!-- Notification Widget Mounting Point -->
                <div id="notification-root" class="absolute top-4 right-4 md:right-8 z-30"></div>
                <div class="mx-auto max-w-7xl space-y-8">
                    <!-- Welcome Section -->
                    <div class="text-center py-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em] mb-2">
                            Welcome back, <?php
                            // Use first name if available, otherwise username
                            $displayName = $currentUser['name'] ?? $currentUser['username'];
                            if ($currentUser['name']) {
                                // Get first word of name
                                $nameParts = explode(' ', trim($currentUser['name']));
                                $displayName = $nameParts[0];
                            }
                            echo htmlspecialchars($displayName);
                            ?>!</h2>
                    </div>

                    <!-- Dashboard Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Marine Requests Card -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Marine Requests</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        <?php echo $marineCount; ?>
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-orange-600">directions_boat</span>
                            </div>
                        </div>

                        <!-- Fuel & Water Requests Card -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Fuel & Water</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        <?php echo $fuelWaterCount; ?>
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-orange-600">water_drop</span>
                            </div>
                        </div>

                        <!-- Crew Transfer Requests Card -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Crew Transfer</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        <?php echo $crewTransferCount; ?>
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-orange-600">groups</span>
                            </div>
                        </div>

                        <!-- Total Requests Card -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Requests</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        <?php echo $marineCount + $fuelWaterCount + $crewTransferCount; ?>
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-3xl text-orange-600">analytics</span>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Requests Card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest Requests</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">The most recent 5 requests assigned
                                to you</p>
                        </div>
                        <div class="p-6">
                            <?php if (empty($recentActivity)): ?>
                                <div class="text-center py-8">
                                    <span
                                        class="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-500">assignment</span>
                                    <p class="text-gray-600 dark:text-gray-400 mt-2">No requests yet</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($recentActivity as $request): ?>
                                        <?php
                                        // Determine the detail page URL based on request type
                                        $detailUrl = '';
                                        switch ($request['type']) {
                                            case 'marine':
                                                $detailUrl = 'marine-detail.php?id=' . $request['id'];
                                                break;
                                            case 'berth':
                                                $detailUrl = 'marine-detail.php?id=' . $request['id'];
                                                break;
                                            case 'fuel_water':
                                                $detailUrl = 'fuel-water-detail.php?id=' . $request['id'];
                                                break;
                                            case 'crew_sign_on':
                                                $detailUrl = 'sign-on-detail.php?id=' . $request['id'];
                                                break;
                                            case 'crew_sign_off':
                                                $detailUrl = 'sign-off-detail.php?id=' . $request['id'];
                                                break;
                                            case 'light_port':
                                                $detailUrl = 'light-port-detail.php?id=' . $request['id'];
                                                break;
                                        }
                                        ?>
                                        <a href="<?php echo $detailUrl; ?>"
                                            class="block hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:-translate-y-1 hover:shadow-lg transition-all duration-200 rounded-lg">
                                            <div
                                                class="flex items-center justify-between p-4 bg-white dark:bg-white border border-black rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <span class="material-symbols-outlined text-2xl text-orange-600">
                                                        <?php
                                                        switch ($request['type']) {
                                                            case 'marine':
                                                                echo 'directions_boat';
                                                                break;
                                                            case 'berth':
                                                                echo 'anchor';
                                                                break;
                                                            case 'fuel_water':
                                                                echo 'water_drop';
                                                                break;
                                                            case 'crew_sign_on':
                                                                echo 'person_add';
                                                                break;
                                                            case 'crew_sign_off':
                                                                echo 'person_remove';
                                                                break;
                                                            case 'light_port':
                                                                echo 'lightbulb';
                                                                break;
                                                            default:
                                                                echo 'assignment';
                                                        }
                                                        ?>
                                                    </span>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                                            <?php echo htmlspecialchars($request['name']); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            <?php echo ucfirst(str_replace('_', ' ', $request['type'])); ?> •
                                                            <?php echo date('M j, Y', strtotime($request['request_date'])); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-full <?php echo getStatusColor($request['status']); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                                </span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        function updateTime() {
            const date = new Date();
            document.getElementById('date').innerText = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase();
            document.getElementById('time').innerText = date.toLocaleTimeString('en-US', { hour12: false });
        }
        setInterval(updateTime, 1000);
        updateTime(); // initial

        // Sidebar toggle states
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
                updateToggle();
            }
        }

        setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
        setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
        setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function () {
            const profileBtn = document.getElementById('profile-dropdown-btn');
            const profileDropdown = document.getElementById('profile-dropdown');

            if (profileBtn && profileDropdown) {
                // Toggle dropdown on button click
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                });

                // Close dropdown when pressing Escape
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        profileDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    <!-- Password Change Modal -->
    <?php if ($showPasswordChangeModal): ?>
        <div id="password-change-modal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-6 text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/20">
                        <span
                            class="material-symbols-outlined text-2xl text-orange-600 dark:text-orange-400">lock_reset</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Change Password Required</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">For security reasons, you must change your
                        default password before proceeding.</p>
                </div>

                <form id="password-change-form" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                        <input type="password" id="new-password" name="new_password" required minlength="6"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-900 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="Enter new password">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New
                            Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" required minlength="6"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-900 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="Confirm new password">
                    </div>

                    <div id="password-error"
                        class="hidden rounded-lg bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    </div>

                    <button type="submit" id="update-password-btn" disabled
                        class="w-full rounded-lg bg-gray-400 px-4 py-2 text-sm font-medium text-white cursor-not-allowed focus:outline-none dark:bg-gray-600">
                        Update Password
                    </button>
                </form>
            </div>
        </div>

        <script>
            const newPasswordInput = document.getElementById('new-password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const updateBtn = document.getElementById('update-password-btn');
            const errorDiv = document.getElementById('password-error');

            function checkPasswords() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (newPassword && confirmPassword && newPassword === confirmPassword && newPassword.length >= 6) {
                    updateBtn.disabled = false;
                    updateBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'dark:bg-gray-600');
                    updateBtn.classList.add('bg-orange-600', 'hover:bg-orange-700', 'focus:ring-2', 'focus:ring-orange-500', 'focus:ring-offset-2', 'dark:focus:ring-offset-gray-800');
                    errorDiv.classList.add('hidden');
                } else {
                    updateBtn.disabled = true;
                    updateBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'dark:bg-gray-600');
                    updateBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700', 'focus:ring-2', 'focus:ring-orange-500', 'focus:ring-offset-2', 'dark:focus:ring-offset-gray-800');

                    if (confirmPassword && newPassword !== confirmPassword) {
                        errorDiv.textContent = 'Passwords do not match.';
                        errorDiv.classList.remove('hidden');
                    } else if (newPassword && newPassword.length < 6) {
                        errorDiv.textContent = 'Password must be at least 6 characters long.';
                        errorDiv.classList.remove('hidden');
                    } else {
                        errorDiv.classList.add('hidden');
                    }
                }
            }

            newPasswordInput.addEventListener('input', checkPasswords);
            confirmPasswordInput.addEventListener('input', checkPasswords);

            document.getElementById('password-change-form').addEventListener('submit', function (e) {
                e.preventDefault();

                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (newPassword !== confirmPassword) {
                    errorDiv.textContent = 'Passwords do not match.';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                if (newPassword.length < 6) {
                    errorDiv.textContent = 'Password must be at least 6 characters long.';
                    errorDiv.classList.remove('hidden');
                    return;
                }

                // Send request to API
                fetch('api/change_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Success - reload page
                            window.location.reload();
                        } else {
                            errorDiv.textContent = data.message || 'Failed to update password.';
                            errorDiv.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        errorDiv.textContent = 'An error occurred. Please try again.';
                        errorDiv.classList.remove('hidden');
                    });
            });
        </script>
    <?php endif; ?>
</body>

</html>

<!-- React Notification Widget Script -->
<script type="text/babel" data-type="module">
    import React, { useState, useEffect, useRef } from 'https://esm.sh/react@18.2.0';
    import { createRoot } from 'https://esm.sh/react-dom@18.2.0/client';
    import { Bell, X, CheckCircle2, XCircle, FileText, ChevronRight, Clock } from 'https://esm.sh/lucide-react@0.292.0';

    const NotificationWidget = ({ notifications, onDismiss }) => {
        const [isOpen, setIsOpen] = useState(false);
        const widgetRef = useRef(null);

        useEffect(() => {
            const handleClickOutside = (event) => {
                if (widgetRef.current && !widgetRef.current.contains(event.target)) {
                    setIsOpen(false);
                }
            };

            document.addEventListener('mousedown', handleClickOutside);
            return () => {
                document.removeEventListener('mousedown', handleClickOutside);
            };
        }, []);

        // Modern Icon Logic
        const getIcon = (type) => {
            switch (type) {
                case 'approved': return <CheckCircle2 className="w-5 h-5 text-orange-500" />;
                case 'info': return <FileText className="w-5 h-5 text-orange-500" />;
                case 'rejected': return <XCircle className="w-5 h-5 text-gray-700" />;
                default: return <Bell className="w-5 h-5 text-gray-500" />;
            }
        };

        return (
            <div
                ref={widgetRef}
                className={`bg-white border border-gray-200 shadow-xl overflow-hidden transition-all duration-500 ease-in-out font-sans ${isOpen ? 'w-96 rounded-2xl' : 'w-48 rounded-2xl hover:bg-gray-50 cursor-pointer'}`}
                style={{ maxHeight: isOpen ? '600px' : '64px' }}
                onClick={() => !isOpen && setIsOpen(true)}
            >
                {/* Header with Modern Icon */}
                <div className={`flex items-center whitespace-nowrap h-full ${isOpen ? 'justify-between p-3 border-b border-gray-100 bg-gray-50' : 'p-3 pr-4 gap-3'}`}>
                    <div className={`flex items-center transition-all duration-300 ${isOpen ? 'gap-3 px-1' : 'gap-3'}`}>

                        {/* Animated Orange Icon Container */}
                        <div className={`relative flex items-center justify-center w-10 h-10 rounded-full flex-shrink-0 shadow-sm border ${isOpen ? 'bg-orange-50 border-orange-100 text-orange-600 ring-2 ring-orange-50 ring-offset-1' : 'bg-white border-gray-100 text-gray-500 hover:bg-gray-50 hover:border-gray-200'} transition-all duration-300`}>
                            <Bell className={`w-5 h-5 ${isOpen ? 'fill-current' : ''}`} strokeWidth={2} />
                            {!isOpen && notifications.length > 0 && (
                                <span className="absolute top-2 right-2.5 flex h-2.5 w-2.5">
                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                    <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-500 border border-white"></span>
                                </span>
                            )}
                        </div>

                        <div className="flex flex-col overflow-hidden">
                            <span className={`font-semibold text-sm truncate ${isOpen ? 'text-gray-900' : 'text-gray-700'}`}>
                                {notifications.length} {isOpen ? 'Request Updates' : 'Updates'}
                            </span>
                            {isOpen && <span className="text-xs text-gray-500 truncate">Check your approval status</span>}
                        </div>
                    </div>

                    {isOpen ? (
                        <button onClick={(e) => { e.stopPropagation(); setIsOpen(false); }} className="p-1 rounded-full hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                            <X className="w-5 h-5" />
                        </button>
                    ) : (
                        <ChevronRight className="w-4 h-4 text-gray-400 flex-shrink-0 ml-auto" />
                    )}
                </div>

                {/* List Content */}
                <div className={`flex flex-col bg-white transition-all duration-300 ${isOpen ? 'opacity-100 visible' : 'opacity-0 invisible'}`}>
                    <div className="w-96 max-h-[320px] overflow-y-auto">
                        {notifications.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-gray-400"><CheckCircle2 className="w-12 h-12 mb-2 opacity-20" /><p className="text-sm">No pending updates</p></div>
                        ) : (
                            notifications.map((note) => (
                                <div key={note.id} className="group relative flex gap-4 p-4 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                    <div className="mt-1 flex-shrink-0">{getIcon(note.type)}</div>
                                    <div className="flex-1 pr-6">
                                        <h4 className="text-sm font-semibold leading-none mb-1 text-gray-900">{note.title}</h4>
                                        <p className="text-sm text-gray-600 mb-2 leading-snug">{note.message}</p>
                                        <div className="flex items-center gap-1 text-xs text-gray-400 font-medium"><Clock className="w-3 h-3" />{note.time}</div>
                                    </div>
                                    <button onClick={(e) => onDismiss(note.id, e)} className="absolute top-2 right-2 p-1 text-gray-300 opacity-0 group-hover:opacity-100 hover:text-orange-500 transition-all"><X className="w-3 h-3" /></button>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        );
    };

    const App = () => {
        const [notifications, setNotifications] = useState([
            { id: 1, type: 'approved', title: 'Leave Request Approved', message: 'Your leave request for Dec 25 has been approved.', time: '2 mins ago' },
            { id: 2, type: 'info', title: 'New Policy Update', message: 'Please review the updated HR policy.', time: '1 hour ago' },
            { id: 3, type: 'rejected', title: 'Expense Rejected', message: 'Your expense report #1023 was returned.', time: '3 hours ago' },
            { id: 4, type: 'info', title: 'System Maintenance', message: 'Scheduled maintenance on Saturday at 10 PM.', time: '5 hours ago' },
            { id: 5, type: 'approved', title: 'Project Plan Accepted', message: 'The Q1 project plan has been signed off.', time: '1 day ago' },
            { id: 6, type: 'info', title: 'Welcome New Hire', message: 'Please welcome John Doe to the team!', time: '1 day ago' },
        ]);

        const handleDismiss = (id, e) => {
            e.stopPropagation();
            setNotifications(prev => prev.filter(n => n.id !== id));
        };

        return (
            <div className="relative z-50 w-48 h-16">
                <div className="absolute top-0 right-0">
                    <NotificationWidget notifications={notifications} onDismiss={handleDismiss} />
                </div>
            </div>
        );
    };

    const root = createRoot(document.getElementById('notification-root'));
    root.render(<App />);
</script>