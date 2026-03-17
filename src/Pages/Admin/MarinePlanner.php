<?php
require_once __DIR__ . '/../../../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Set current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Handle form submissions
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$requests = [];

if (isset($_POST['load_requests'])) {
    $selectedDate = $_POST['selected_date'];

    // Fetch marine requests where ETA date matches selected date and status is pending OR assigned
    // This ensures assigned requests still show in the marine planner
    $stmt = $conn->prepare("
        SELECT mr.*, u.company_name as user_company
        FROM marine_requests mr
        LEFT JOIN users u ON mr.user_id = u.user_id
        WHERE DATE(mr.eta) = ? AND mr.status IN ('pending', 'assign')
        ORDER BY mr.eta ASC
    ");
    $stmt->execute([$selectedDate]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST['save_assignments'])) {
    $assignments = $_POST['berth_assignment'] ?? [];

    foreach ($assignments as $marineId => $berthId) {
        if (!empty($berthId)) {
            // Map UI values to database values
            $berthMap = [
                'B1' => 'Berth 1',
                'B2' => 'Berth 2', 
                'B3' => 'Berth 3',
                'B4' => 'Berth 4'
            ];
            
            $dbBerthValue = $berthMap[$berthId] ?? $berthId;
            
            $stmt = $conn->prepare("
                UPDATE marine_requests
                SET berth_id = ?, berth_assigned_date = CURDATE(), berth_assigned_at = NOW(), 
                    status = 'assign', updated_at = NOW()
                WHERE marine_id = ?
            ");
            $stmt->execute([$dbBerthValue, $marineId]);
        }
    }

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF'] . "?date=" . urlencode($selectedDate));
    exit();
}

// Function to display berth in UI format
function displayBerth($dbValue) {
    $berthDisplay = [
        'B1' => 'Berth 1',
        'B2' => 'Berth 2',
        'B3' => 'Berth 3',
        'B4' => 'Berth 4',
        'Berth 1' => 'Berth 1',
        'Berth 2' => 'Berth 2',
        'Berth 3' => 'Berth 3',
        'Berth 4' => 'Berth 4'
    ];
    
    return $berthDisplay[$dbValue] ?? $dbValue;
}

// Function to get database berth value
function getDbBerthValue($uiValue) {
    $berthMap = [
        'B1' => 'Berth 1',
        'B2' => 'Berth 2',
        'B3' => 'Berth 3',
        'B4' => 'Berth 4'
    ];
    
    return $berthMap[$uiValue] ?? $uiValue;
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Marine Planner</title>
    <script src="../assets/js/tailwindcss.js"></script>
    <link href="../assets/css/fonts.css" rel="stylesheet" />
    <link href="../assets/css/material-icons.css" rel="stylesheet" />
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

        .table-container {
            margin: 0;
            max-width: none;
            border-radius: 12px;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03), 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            color: #374151;
        }

        .table-container table thead {
            background-color: #212121;
            color: #ffffff;
        }

        .table-container table th {
            padding: 16px 24px;
            text-align: left;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #E5E7EB;
        }

        .table-container table td {
            padding: 16px 24px;
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
            font-weight: 500;
            border-bottom: 1px solid #F3F4F6;
        }

        .table-container table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-container table tbody tr:hover {
            background-color: #F9FAFB;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #FEF3C7;
            color: #B45309;
        }

        .status-assign {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-assigned {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-inprogress {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .status-pendingendorsement {
            background-color: #FEF3C7;
            color: #B45309;
        }
        .status-endorsed {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-requestamendment {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        .status-cancel {
            background-color: #F3F4F6;
            color: #6B7280;
        }
        .status-default {
            background-color: #F3F4F6;
            color: #374151;
        }
    </style>
    <script src="../tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../Components/Layout/AdminSidebar.php'; ?>

        <!-- Header Bar -->
        <header
            class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Kuala Terengganu Support
                    Base (Administrator)</h1>
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
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($admin['username']); ?></span>
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
            <main class="relative flex-1 overflow-y-auto p-4 md:p-8">
                <div class="w-full">
                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <span>Request List</span>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200">Marine Planner</span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Marine Planner</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Plan and assign berths for marine requests.</p>
                    </div>

                    <!-- Date Selection Form -->
                    <div class="mb-6">
                        <div
                            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg shadow-gray-200/20 dark:shadow-gray-900/20 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="material-symbols-outlined text-primary text-xl">calendar_today</span>
                                <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200">Select Date</h3>
                            </div>
                            <form method="POST" class="flex gap-4 items-end">
                                <div class="flex-1">
                                    <label for="selected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                                    <input type="date" id="selected_date" name="selected_date"
                                        value="<?php echo htmlspecialchars($selectedDate); ?>"
                                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200">
                                </div>
                                <button type="submit" name="load_requests"
                                    class="px-6 py-2 text-white rounded-lg hover:opacity-90 transition-all duration-200 font-medium bg-[#212121]">
                                    <span class="material-symbols-outlined text-sm align-middle mr-2">search</span>
                                    Load Requests
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($requests)): ?>
                    <!-- Assignment Form -->
                    <form method="POST">
                        <input type="hidden" name="selected_date" value="<?php echo htmlspecialchars($selectedDate); ?>">

                        <!-- Table -->
                        <div class="table-container mb-6">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Est. Arrival</th>
                                        <th>Vessel</th>
                                        <th>BOD No</th>
                                        <th>Company</th>
                                        <th>Berth ID</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($requests as $request) {
                                        // Get current berth value for selection
                                        $currentBerth = $request['berth_id'];
                                        
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($request['eta']))) . "</td>";
                                        echo "<td>" . htmlspecialchars($request['vessel_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($request['bod_no'] ?? '') . "</td>";
                                        echo "<td>" . htmlspecialchars($request['company'] ?? $request['user_company'] ?? '') . "</td>";
                                        echo "<td>";
                                        echo "<select name='berth_assignment[" . htmlspecialchars($request['marine_id']) . "]' class='w-32 px-2 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm font-medium' style='min-width: 100px;'>";
                                        echo "<option value='' class='text-gray-500'>-- Select --</option>";
                                        
                                        // Map database values to UI values for selection
                                        $berthOptions = [
                                            'B1' => 'Berth 1',
                                            'B2' => 'Berth 2',
                                            'B3' => 'Berth 3',
                                            'B4' => 'Berth 4'
                                        ];
                                        
                                        foreach ($berthOptions as $dbValue => $displayValue) {
                                            // Check if current berth matches either the DB value or display value
                                            $selected = '';
                                            if ($currentBerth === $dbValue || 
                                                $currentBerth === $displayValue || 
                                                $currentBerth === 'Berth ' . substr($dbValue, 1)) {
                                                $selected = 'selected';
                                            }
                                            echo "<option value='$dbValue' $selected class='font-medium'>$displayValue</option>";
                                        }
                                        echo "</select>";
                                        echo "</td>";
                                        
                                        // Status display with proper formatting
                                        $status = strtolower(str_replace(' ', '', $request['status']));
                                        echo "<td><span class='status-pill status-$status'>" . htmlspecialchars(ucfirst($request['status'])) . "</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end">
                            <button type="submit" name="save_assignments"
                                class="px-6 py-3 text-white rounded-lg hover:opacity-90 transition-all duration-200 font-medium bg-green-600">
                                <span class="material-symbols-outlined text-sm align-middle mr-2">save</span>
                                Save Assignments
                            </button>
                        </div>
                    </form>
                    <?php elseif (isset($_POST['load_requests'])): ?>
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined text-4xl text-gray-400">info</span>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">No pending or assigned requests found for the selected date.</p>
                        </div>
                    <?php endif; ?>

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
        updateTime();

        // Profile dropdown
        document.addEventListener('DOMContentLoaded', function () {
            const profileBtn = document.getElementById('profile-dropdown-btn');
            const profileDropdown = document.getElementById('profile-dropdown');

            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function (e) { e.stopPropagation(); profileDropdown.classList.toggle('hidden'); });
                document.addEventListener('click', function (e) { if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) profileDropdown.classList.add('hidden'); });
            }
        });
    </script>
</body>

</html>