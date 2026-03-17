<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Check if user is allowed (user role)
if ($currentUser['user_type'] !== 'user') {
    header('Location: dashboard.php');
    exit;
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Get some statistics for the dashboard
$conn = getDBConnection();

// Initialize variables
$marineCount = 0;
$fuelWaterCount = 0;
$signOnCount = 0;
$signOffCount = 0;
$lightDuesCount = 0;
$portDuesCount = 0;
$portClearanceCount = 0;
$marineOvertimeCount = 0;
$recentActivity = [];

try {
    // Get user data
    $stmt = $conn->prepare("SELECT user_id, username, company_name FROM users WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        die("User not found in database!");
    }
    
    $company = $userData['company_name'];
    
    // DEBUG: Check each query individually
    echo "<!-- DEBUG START ==================== -->\n";
    echo "<!-- User ID: {$currentUser['user_id']}, Company: {$company} -->\n";
    
    // 1. Check marine_requests directly
    $marineStmt = $conn->prepare("SELECT COUNT(*) as total FROM marine_requests WHERE company = ?");
    $marineStmt->execute([$company]);
    $marineCount = $marineStmt->fetch()['total'];
    echo "<!-- Marine count: {$marineCount} -->\n";
    
    // 2. Check fuel_water_requests with user_id
    $fwStmt = $conn->prepare("
        SELECT r.*, u.user_id, u.company_name 
        FROM fuel_water_requests r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE u.user_id = ? OR u.company_name = ?
    ");
    $fwStmt->execute([$currentUser['user_id'], $company]);
    $fwResults = $fwStmt->fetchAll();
    echo "<!-- Fuel/Water results count: " . count($fwResults) . " -->\n";
    
    // 3. Check crew_sign_on_requests
    $soStmt = $conn->prepare("
        SELECT r.*, u.user_id, u.company_name 
        FROM crew_sign_on_requests r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE u.user_id = ? OR u.company_name = ?
    ");
    $soStmt->execute([$currentUser['user_id'], $company]);
    $soResults = $soStmt->fetchAll();
    echo "<!-- Sign-On results count: " . count($soResults) . " -->\n";
    
    // 4. Check crew_sign_off_requests
    $sfStmt = $conn->prepare("
        SELECT r.*, u.user_id, u.company_name 
        FROM crew_sign_off_requests r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE u.user_id = ? OR u.company_name = ?
    ");
    $sfStmt->execute([$currentUser['user_id'], $company]);
    $sfResults = $sfStmt->fetchAll();
    echo "<!-- Sign-Off results count: " . count($sfResults) . " -->\n";
    
    // 5. Check light_port_requests
    $lpStmt = $conn->prepare("
        SELECT r.*, u.user_id, u.company_name 
        FROM light_port_requests r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE u.user_id = ? OR u.company_name = ?
    ");
    $lpStmt->execute([$currentUser['user_id'], $company]);
    $lpResults = $lpStmt->fetchAll();
    echo "<!-- Light/Port results count: " . count($lpResults) . " -->\n";
    
    // 6. Check port_clearance_requests
    $pcStmt = $conn->prepare("
        SELECT r.*, u.user_id, u.company_name 
        FROM port_clearance_requests r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE u.user_id = ? OR u.company_name = ?
    ");
    $pcStmt->execute([$currentUser['user_id'], $company]);
    $pcResults = $pcStmt->fetchAll();
    echo "<!-- Port Clearance results count: " . count($pcResults) . " -->\n";
    
    // 7. Check marine_overtime_requests
    $moStmt = $conn->prepare("
        SELECT r.*, u.user_id, u.company_name 
        FROM marine_overtime_requests r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE u.user_id = ? OR u.company_name = ?
    ");
    $moStmt->execute([$currentUser['user_id'], $company]);
    $moResults = $moStmt->fetchAll();
    echo "<!-- Marine Overtime results count: " . count($moResults) . " -->\n";
    
    // 8. Show actual data from each table
    echo "<!-- ===== SAMPLE DATA ===== -->\n";
    
    // Show first 3 marine requests
    $sample = $conn->query("SELECT marine_id, user_id, company, vessel_name FROM marine_requests LIMIT 3");
    echo "<!-- Marine sample: " . print_r($sample->fetchAll(), true) . " -->\n";
    
    // Show first 3 fuel_water requests
    $sample = $conn->query("SELECT fuelwater_id, user_id, vessel_name FROM fuel_water_requests LIMIT 3");
    echo "<!-- Fuel/Water sample: " . print_r($sample->fetchAll(), true) . " -->\n";
    
    // Show first 3 users
    $sample = $conn->query("SELECT user_id, username, company_name FROM users LIMIT 3");
    echo "<!-- Users sample: " . print_r($sample->fetchAll(), true) . " -->\n";
    
    echo "<!-- DEBUG END ==================== -->\n";
    
    // Now run the actual queries with a simpler approach
    // Count marine requests
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM marine_requests WHERE company = ?");
    $stmt->execute([$company]);
    $marineCount = $stmt->fetch()['total'];
    
    // For other tables, count by user_id first, then by company as fallback
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM fuel_water_requests WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $fuelWaterCount = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM crew_sign_on_requests WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $signOnCount = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM crew_sign_off_requests WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $signOffCount = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM light_port_requests WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $lightPortTotal = $stmt->fetch()['total'];
    
    // Split light/port dues
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM light_port_requests WHERE user_id = ? AND request_type = 'light_dues'");
    $stmt->execute([$currentUser['user_id']]);
    $lightDuesCount = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM light_port_requests WHERE user_id = ? AND request_type = 'port_dues'");
    $stmt->execute([$currentUser['user_id']]);
    $portDuesCount = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM port_clearance_requests WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $portClearanceCount = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM marine_overtime_requests WHERE user_id = ?");
    $stmt->execute([$currentUser['user_id']]);
    $marineOvertimeCount = $stmt->fetch()['total'];
    
    // Recent activity - simpler version without subqueries first
    $recentActivity = [];
    
    // Get marine requests
    $stmt = $conn->prepare("SELECT 'marine' as type, marine_id as id, vessel_name as name, created_at, status FROM marine_requests WHERE company = ? ORDER BY created_at DESC LIMIT 2");
    $stmt->execute([$company]);
    $marine = $stmt->fetchAll();
    
    // Get fuel/water
    $stmt = $conn->prepare("SELECT 'fuel_water' as type, fuelwater_id as id, vessel_name as name, created_at, status FROM fuel_water_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
    $stmt->execute([$currentUser['user_id']]);
    $fw = $stmt->fetchAll();
    
    // Get crew sign on
    $stmt = $conn->prepare("SELECT 'crew_sign_on' as type, crew_signon_id as id, vessel_name as name, created_at, status FROM crew_sign_on_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
    $stmt->execute([$currentUser['user_id']]);
    $so = $stmt->fetchAll();
    
    // Get crew sign off
    $stmt = $conn->prepare("SELECT 'crew_sign_off' as type, crew_signoff_id as id, vessel_name as name, created_at, status FROM crew_sign_off_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
    $stmt->execute([$currentUser['user_id']]);
    $sf = $stmt->fetchAll();
    
    // Get light/port
    $stmt = $conn->prepare("SELECT 'light_port' as type, lightport_id as id, vessel_name as name, created_at, status FROM light_port_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
    $stmt->execute([$currentUser['user_id']]);
    $lp = $stmt->fetchAll();
    
    // Merge all arrays
    $recentActivity = array_merge($marine, $fw, $so, $sf, $lp);
    
    // Sort by created_at descending
    usort($recentActivity, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Take first 5
    $recentActivity = array_slice($recentActivity, 0, 5);
    
    echo "<!-- FINAL recent activity count: " . count($recentActivity) . " -->\n";
    
} catch (PDOException $e) {
    echo "<!-- ERROR: " . $e->getMessage() . " -->\n";
    error_log("Dashboard error: " . $e->getMessage());
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
    <script src="tab-session.js"></script>
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
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 48;
        }

        /* Base state for widgets */
        .widget-animate {
            opacity: 0;
            --tw-translate-y: 60px;
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
            transition: opacity 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        /* Active state */
        .widget-visible {
            opacity: 1;
            --tw-translate-y: 0px;
        }
    </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-screen w-full">
        <!-- Sidebar -->
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
        <!-- Activity tracking script -->
        <script>
                (function () {
                    let lastUpdate = 0;
                    const UPDATE_INTERVAL = 60000; // 1 minute

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

                    function handleUserActivity() {
                        const now = Date.now();
                        if (now - lastUpdate > UPDATE_INTERVAL) {
                            updateActivity();
                            lastUpdate = now;
                        }
                    }

                    // Listen for user activity events
                    document.addEventListener('mousemove', handleUserActivity);
                    document.addEventListener('keydown', handleUserActivity);
                    document.addEventListener('click', handleUserActivity);
                    document.addEventListener('scroll', handleUserActivity);
                    document.addEventListener('touchstart', handleUserActivity); // For mobile

                    // Initial activity update
                    updateActivity();
                    lastUpdate = Date.now();
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
                    <div class="text-left mb-6 widget-animate">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <!-- Marine Requests Card -->
                        <a href="marine-history.php" class="block group widget-animate">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Marine</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            <?php echo $marineCount; ?>
                                        </p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-xl text-orange-600 group-hover:scale-110 transition-transform">directions_boat</span>
                                </div>
                            </div>
                        </a>

                        <!-- Port Clearance Card -->
                        <a href="port-clearance-history.php" class="block group widget-animate">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Port Clearance
                                        </p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            <?php echo $portClearanceCount; ?>
                                        </p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-xl text-orange-600 group-hover:scale-110 transition-transform">description</span>
                                </div>
                            </div>
                        </a>

                        <!-- Marine Overtime Card -->
                        <a href="marine-overtime-history.php" class="block group widget-animate">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Marine Overtime
                                        </p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            <?php echo $marineOvertimeCount; ?>
                                        </p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-xl text-orange-600 group-hover:scale-110 transition-transform">schedule</span>
                                </div>
                            </div>
                        </a>

                        <!-- Light Dues Card -->
                        <a href="light-dues-history.php" class="block group widget-animate">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Light Dues</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            <?php echo $lightDuesCount; ?>
                                        </p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-xl text-orange-600 group-hover:scale-110 transition-transform">lightbulb</span>
                                </div>
                            </div>
                        </a>

                        <!-- Port Dues Card -->
                        <a href="port-dues-history.php" class="block group widget-animate">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Port Dues</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            <?php echo $portDuesCount; ?>
                                        </p>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-xl text-orange-600 group-hover:scale-110 transition-transform">anchor</span>
                                </div>
                            </div>
                        </a>

                        <!-- Total Requests Card -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 shadow-sm widget-animate">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Requests</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        <?php echo $marineCount + $fuelWaterCount + $signOnCount + $signOffCount + $lightDuesCount + $portDuesCount + $portClearanceCount + $marineOvertimeCount; ?>
                                    </p>
                                </div>
                                <span class="material-symbols-outlined text-xl text-orange-600">analytics</span>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Requests Card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm widget-animate">
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest Requests</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">The most recent 5 requests from
                                your company</p>
                        </div>
                        <div class="p-5">
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
                                                class="flex items-center justify-between p-3 bg-white dark:bg-white border border-black rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <span class="material-symbols-outlined text-xl text-orange-600">
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
                                                            <?php echo date('M j, Y', strtotime($request['created_at'])); ?>
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

        // Save and restore sidebar scroll position
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar-scroll-container');
            if (sidebar) {
                const scrollTop = localStorage.getItem('sidebarScrollTop');
                if (scrollTop) {
                    sidebar.scrollTop = parseInt(scrollTop);
                }

                // Save on scroll
                sidebar.addEventListener('scroll', function () {
                    localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
                });
            }
        });

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
    <!-- Dashboard Load Animation -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const widgets = document.querySelectorAll('.widget-animate');
            // If DOMContentLoaded already fired (e.g. script runs late), run immediately
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                animateWidgets();
            } else {
                // otherwise wait for it
            }
        });

        function animateWidgets() {
            const widgets = document.querySelectorAll('.widget-animate');
            widgets.forEach((widget, index) => {
                setTimeout(() => {
                    widget.classList.add('widget-visible');
                    // Cleanup after transition (800ms) to restore original behaviors (hover, sidebar toggle)
                    setTimeout(() => {
                        widget.classList.remove('widget-animate', 'widget-visible');
                    }, 800);
                }, index * 100);
            });
        }

        // Run immediately if possible (since we are at end of body)
        animateWidgets();
    </script>
</body>

</html>