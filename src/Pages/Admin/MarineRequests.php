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

// Handle search filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$month = $_GET['month'] ?? '';
$year = $_GET['year'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20;
$offset = ($page - 1) * $results_per_page;
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Marine Requests List</title>
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

        /* Custom styles for the table design */
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
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .status-inprogress {
            background-color: #F0F9FF;
            color: #0369A1;
        }

        .status-pendingendorsement {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-endorsed {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-requestamendment {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-cancel {
            background-color: #FEE2E2;
            color: #991B1B;
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
                            <span class="text-[#212529] dark:text-gray-200">Marine Requests</span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Marine Requests</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Complete
                            list of all marine requests from all users.</p>
                    </div>

                    <!-- Filter Card -->
                    <div class="mb-6">
                        <div
                            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg shadow-gray-200/20 dark:shadow-gray-900/20 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="material-symbols-outlined text-primary text-xl">filter_list</span>
                                <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200">Filter Requests</h3>
                            </div>
                            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <!-- Search Input -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-lg">search</span>
                                    </div>
                                    <input type="text" name="search"
                                        value="<?php echo htmlspecialchars($search); ?>"
                                        placeholder="Search vessel, BOD No..."
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm placeholder-gray-500 dark:placeholder-gray-400">
                                </div>
                                <!-- Status Filter -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span
                                            class="material-symbols-outlined text-gray-400 text-lg">check_circle</span>
                                    </div>
                                    <select name="status_filter"
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="assign" <?php echo $status_filter === 'assign' ? 'selected' : ''; ?>>Assign</option>
                                        <option value="in progress" <?php echo $status_filter === 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="pending endorsement" <?php echo $status_filter === 'pending endorsement' ? 'selected' : ''; ?>>Pending Endorsement</option>
                                        <option value="endorsed" <?php echo $status_filter === 'endorsed' ? 'selected' : ''; ?>>Endorsed</option>
                                        <option value="request amendment" <?php echo $status_filter === 'request amendment' ? 'selected' : ''; ?>>Request Amendment</option>
                                        <option value="cancel" <?php echo $status_filter === 'cancel' ? 'selected' : ''; ?>>Cancel</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                    </div>
                                </div>
                                <!-- Month/Year -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="relative">
                                        <select name="month"
                                            class="w-full pl-4 pr-8 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                            <option value="">Month</option>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $month == $i ? 'selected' : ''; ?>>
                                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span
                                                class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <select name="year"
                                            class="w-full pl-4 pr-8 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                            <option value="">Year</option>
                                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                                    <?php echo $y; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span
                                                class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Buttons -->
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-4 py-3 text-white rounded-lg hover:opacity-90 transition-all duration-200 text-sm font-medium bg-[#212121]">
                                        <span class="material-symbols-outlined text-sm align-middle">search</span>
                                        Search
                                    </button>
                                    <a href="marine_requests.php"
                                        class="px-4 py-3 text-white rounded-lg hover:opacity-90 transition-all duration-200 text-sm font-medium bg-[#D10000]">
                                        <span class="material-symbols-outlined text-sm align-middle">refresh</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Type</th>
                                    <th>Vessel</th>
                                    <th>BOD No</th>
                                    <th>Requestor</th>
                                    <th>Date Submitted</th>
                                    <th>ETA</th>
                                    <th>ETD</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Build WHERE conditions for main query
                                $whereConditions = [];
                                $params = [];

                                if (!empty($search)) {
                                    $searchTerm = '%' . $search . '%';
                                    $whereConditions[] = "(mr.vessel_name LIKE ? OR mr.bod_no LIKE ? OR mr.log_no LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?)";
                                    $params[] = $searchTerm;
                                    $params[] = $searchTerm;
                                    $params[] = $searchTerm;
                                    $params[] = $searchTerm;
                                    $params[] = $searchTerm;
                                }
                                if (!empty($status_filter)) {
                                    $whereConditions[] = "mr.status = ?";
                                    $params[] = $status_filter;
                                }
                                if (!empty($month)) {
                                    $whereConditions[] = "MONTH(mr.created_at) = ?";
                                    $params[] = $month;
                                }
                                if (!empty($year)) {
                                    $whereConditions[] = "YEAR(mr.created_at) = ?";
                                    $params[] = $year;
                                }

                                $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

                                // Count total records for pagination
                                $countSql = "SELECT COUNT(*) as total FROM marine_requests mr 
                                            LEFT JOIN users u ON mr.user_id = u.user_id 
                                            $whereClause";
                                $countStmt = $conn->prepare($countSql);
                                $countStmt->execute($params);
                                $total_records = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
                                $total_pages = ceil($total_records / $results_per_page);

                                // Main query to fetch marine requests
                                $sql = "SELECT mr.*, 
                                            u.username, 
                                            u.full_name,
                                            COALESCE(mfws.marine_id, mgw.marine_id) as has_marine_services,
                                            CASE 
                                                WHEN mr.crew_transfer_type IS NOT NULL OR mfws.id IS NOT NULL OR mgw.id IS NOT NULL THEN 'Marine'
                                                ELSE 'Berth' 
                                            END as request_type
                                        FROM marine_requests mr 
                                        LEFT JOIN users u ON mr.user_id = u.user_id 
                                        LEFT JOIN marine_fuel_water_services mfws ON mr.marine_id = mfws.marine_id
                                        LEFT JOIN marine_general_works mgw ON mr.marine_id = mgw.marine_id
                                        $whereClause 
                                        GROUP BY mr.marine_id
                                        ORDER BY mr.created_at DESC 
                                        LIMIT ? OFFSET ?";

                                $params[] = $results_per_page;
                                $params[] = $offset;

                                $stmt = $conn->prepare($sql);
                                $stmt->execute($params);
                                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (count($results) > 0) {
                                    $no = $offset + 1;
                                    foreach ($results as $row) {
                                        // Determine requestor name
                                        $requestor = !empty($row['full_name']) ? $row['full_name'] : $row['username'];
                                        $requestor = !empty($requestor) ? $requestor : 'Unknown';

                                        // Format dates
                                        $created_at = !empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '';
                                        $eta = !empty($row['eta']) ? date('d/m/Y H:i', strtotime($row['eta'])) : '';
                                        $etd = !empty($row['etd']) ? date('d/m/Y H:i', strtotime($row['etd'])) : '';

                                        // Determine CSS class for status
                                        $statusClass = strtolower(str_replace(' ', '', $row['status']));
                                        
                                        echo "<tr class='clickable-row' data-id='" . htmlspecialchars($row['marine_id']) . "' style='cursor: pointer;'>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>";
                                        if ($row['request_type'] === 'Berth') {
                                            echo "<span class='inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10'><span class='material-symbols-outlined text-[16px]'>anchor</span>Berth</span>";
                                        } else {
                                            echo "<span class='inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10'><span class='material-symbols-outlined text-[16px]'>directions_boat</span>Marine</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($row['vessel_name'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['bod_no'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($requestor) . "</td>";
                                        echo "<td>" . htmlspecialchars($created_at) . "</td>";
                                        echo "<td>" . htmlspecialchars($eta) . "</td>";
                                        echo "<td>" . htmlspecialchars($etd) . "</td>";
                                        echo "<td><span class='status-pill status-$statusClass'>" . htmlspecialchars(ucwords($row['status'])) . "</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center py-8 text-gray-500 dark:text-gray-400'>No marine requests found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div
                            class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-lg shadow-sm">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <!-- Mobile pagination -->
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status_filter=<?php echo urlencode($status_filter); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                                <?php endif; ?>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status_filter=<?php echo urlencode($status_filter); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                                <?php endif; ?>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span
                                            class="font-medium"><?php echo min($offset + $results_per_page, $total_records); ?></span>
                                        of <span class="font-medium"><?php echo $total_records; ?></span> results
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                                        aria-label="Pagination">
                                        <?php
                                        // Pagination links
                                        for ($i = 1; $i <= $total_pages; $i++) {
                                            $active = $i == $page ? 'bg-primary text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50';
                                            echo "<a href='?page=$i&search=" . urlencode($search) . "&status_filter=" . urlencode($status_filter) . "&month=$month&year=$year' class='relative inline-flex items-center px-4 py-2 text-sm font-semibold $active focus:z-20'>$i</a>";
                                        }
                                        ?>
                                    </nav>
                                </div>
                            </div>
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
                profileBtn.addEventListener('click', function (e) { 
                    e.stopPropagation(); 
                    profileDropdown.classList.toggle('hidden'); 
                });
                document.addEventListener('click', function (e) { 
                    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden'); 
                    }
                });
            }

            // Rows clickable - redirect to admin marine detail page
            const rows = document.querySelectorAll('.clickable-row');
            rows.forEach(row => {
                row.addEventListener('click', function () {
                    // Redirect to admin marine detail page
                   window.location.href = 'marine_detail.php?id=' + this.dataset.id;
                });
            });
        });
    </script>
</body>
</html>