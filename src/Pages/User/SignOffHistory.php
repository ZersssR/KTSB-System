<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Crew Sign Off History</title>
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

        /* Custom styles for the new table design */
        .table-container {
            margin: 0; /* Remove margin since it's inside main */
            max-width: none; /* Full width */
            border-radius: 12px; /* More rounded corners */
            overflow: hidden; /* Clips the table inside */
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03), 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        /* Main table styling */
        .table-container table {
            width: 100%;
            border-collapse: collapse; /* Removes gaps between cells */
            color: #374151; /* text-gray-700 */
        }

        /* Table Header (thead) */
        .table-container table thead {
            background-color: #212121;
            color: #ffffff;
        }

        .table-container table th {
            padding: 16px 24px;
            text-align: left;
            font-weight: 500; /* Lighter than bold */
            letter-spacing: 0.5px;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #E5E7EB; /* gray-200 */
        }

        /* Table Body (tbody) */
        .table-container table td {
            padding: 16px 24px;
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
            font-weight: 500;
            border-bottom: 1px solid #F3F4F6; /* gray-100 */
        }

        /* Remove bottom border from the last row */
        .table-container table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Hover effect for rows */
        .table-container table tbody tr:hover {
            background-color: #F9FAFB; /* gray-50 */
        }

        /* Status Pill Style */
        .status-pill {
            display: inline-block;
            padding: 4px 12px; /* Vertical and horizontal padding */
            border-radius: 9999px; /* Makes it a pill */
            font-size: 12px;
            font-weight: 500;
        }

        /* Specific style for "Pending" status */
        .status-pending {
            background-color: #FEF3C7; /* bg-amber-100 */
            color: #B45309; /* text-amber-700 */
        }

        .status-completed {
            background-color: #D1FAE5; /* bg-green-100 */
            color: #065F46; /* text-green-700 */
        }

        .status-rejected {
            background-color: #FEE2E2; /* bg-red-100 */
            color: #991B1B; /* text-red-700 */
        }

        .status-default {
            background-color: #F3F4F6; /* bg-gray-100 */
            color: #374151; /* text-gray-700 */
        }
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
<!-- Collapsible SideNavBar -->
<?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
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
                            <span>History</span>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200">Crew Sign Off</span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <?php if ($currentUser['role'] === 'agent'): ?>
                            <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Crew Sign Off</h2>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Complete list of crew sign off records.</p>
                        <?php else: ?>
                            <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Crew Sign Off History</h2>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Complete history of crew sign off records.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Search and Filter Card -->
                    <div class="mb-6">
                        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg shadow-gray-200/20 dark:shadow-gray-900/20 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="material-symbols-outlined text-primary text-xl">filter_list</span>
                                <h3 class="text-lg font-semibold text-[#212529] dark:text-gray-200">Filter Crew Sign Off</h3>
                            </div>
                            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <!-- Search Input -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-lg">search</span>
                                    </div>
                                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search Request No..." class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm placeholder-gray-500 dark:placeholder-gray-400">
                                </div>
                                <!-- Status Filter -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-lg">check_circle</span>
                                    </div>
                                    <select id="status_filter" name="status_filter" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo ($_GET['status_filter'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo ($_GET['status_filter'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="completed" <?php echo ($_GET['status_filter'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="rejected" <?php echo ($_GET['status_filter'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                    </div>
                                </div>
                                <!-- Created Month/Year -->
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- Month -->
                                    <div class="relative">
                                        <select id="month" name="month" class="w-full pl-4 pr-8 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                            <option value="">All Months</option>
                                            <?php
                                            $months = [
                                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                                                7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                            ];
                                            foreach ($months as $num => $name) {
                                                $selected = (isset($_GET['month']) && $_GET['month'] == $num) ? 'selected' : '';
                                                echo "<option value=\"$num\" $selected>$name</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                        </div>
                                    </div>
                                    <!-- Year -->
                                    <div class="relative">
                                        <select id="year" name="year" class="w-full pl-4 pr-8 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all duration-200 text-sm appearance-none">
                                            <option value="">All Years</option>
                                            <?php
                                            $currentYear = date('Y');
                                            for ($y = $currentYear; $y >= 2020; $y--) {
                                                $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '';
                                                echo "<option value=\"$y\" $selected>$y</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-gray-400 text-sm">expand_more</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Action Buttons -->
                                <div class="flex gap-2">
                                    <button type="submit" class="flex-1 px-4 py-3 text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 text-sm font-medium" style="background-color: #212121;">
                                        <span class="material-symbols-outlined text-sm mr-1">search</span>
                                        Search
                                    </button>
                                    <a href="sign-off-history.php" class="px-4 py-3 text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 text-sm font-medium" style="background-color: #D10000;">
                                        <span class="material-symbols-outlined text-sm">refresh</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Request No</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require_once __DIR__ . '/../../../config/app.php';
                                $conn = getDBConnection();

                                try {
                                    // Pagination settings
                                    $results_per_page = 20;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    if ($page < 1) $page = 1;
                                    $offset = ($page - 1) * $results_per_page;

                                    // Build WHERE clause dynamically
                                    $whereConditions = [];
                                    $params = [];

                                    // Agent filter - agents can only see their assigned requests
                                    if ($currentUser['role'] === 'agent') {
                                        $whereConditions[] = "assigned_agent_id = ?";
                                        $params[] = $currentUser['id'];
                                    }

                                    // Search filter
                                    if (!empty($_GET['search'])) {
                                        $searchTerm = '%' . $_GET['search'] . '%';
                                        $whereConditions[] = "crew_signoff_id LIKE ?";
                                        $params = array_merge($params, [$searchTerm]);
                                    }

                                    // Status filter
                                    if (!empty($_GET['status_filter'])) {
                                        $whereConditions[] = "status = ?";
                                        $params[] = $_GET['status_filter'];
                                    }

                                    // Month/Year filter based on request_date
                                    if (!empty($_GET['month']) && !empty($_GET['year'])) {
                                        $month = intval($_GET['month']);
                                        $year = intval($_GET['year']);
                                        $whereConditions[] = "YEAR(request_date) = ? AND MONTH(request_date) = ?";
                                        $params[] = $year;
                                        $params[] = $month;
                                    } elseif (!empty($_GET['month'])) {
                                        $month = intval($_GET['month']);
                                        $whereConditions[] = "MONTH(request_date) = ?";
                                        $params[] = $month;
                                    } elseif (!empty($_GET['year'])) {
                                        $year = intval($_GET['year']);
                                        $whereConditions[] = "YEAR(request_date) = ?";
                                        $params[] = $year;
                                    }

                                    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

                                    // Get total number of records for pagination
                                    $countStmt = $conn->prepare("SELECT COUNT(*) FROM crew_sign_off_requests $whereClause");
                                    $countStmt->execute($params);
                                    $total_records = $countStmt->fetchColumn();
                                    $total_pages = ceil($total_records / $results_per_page);

                                    // Get paginated results
                                    $stmt = $conn->prepare("SELECT crew_signoff_id, request_date, status FROM crew_sign_off_requests $whereClause ORDER BY request_date DESC, request_time DESC LIMIT $results_per_page OFFSET $offset");
                                    $stmt->execute($params);
                                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (count($results) > 0) {
                                        $no = $offset + 1;
                                        foreach ($results as $row) {
                                            echo "<tr class='clickable-row' data-id='" . $row['crew_signoff_id'] . "' style='cursor: pointer;'>";
                                            echo "<td>$no</td>";
                                            echo "<td>" . htmlspecialchars($row['crew_signoff_id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                                            echo "<td>";
                                            echo "<span class='status-pill status-" . strtolower($row['status']) . "'>" . htmlspecialchars(ucfirst($row['status'])) . "</span>";
                                            echo "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>No crew sign off history found.</td></tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='4'>Error loading history: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-lg shadow-sm">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                            <?php else: ?>
                                <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">Previous</span>
                            <?php endif; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                            <?php else: ?>
                                <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">Next</span>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium"><?php echo $offset + 1; ?></span>
                                    to
                                    <span class="font-medium"><?php echo min($offset + $results_per_page, $total_records); ?></span>
                                    of
                                    <span class="font-medium"><?php echo $total_records; ?></span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <!-- Previous Page Link -->
                                    <?php if ($page > 1): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Previous</span>
                                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                            <span class="sr-only">Previous</span>
                                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Page Numbers -->
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);

                                    if ($start_page > 1) {
                                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">1</a>';
                                        if ($start_page > 2) {
                                            echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>';
                                        }
                                    }

                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        if ($i == $page) {
                                            echo '<span aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">' . $i . '</span>';
                                        } else {
                                            echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">' . $i . '</a>';
                                        }
                                    }

                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>';
                                        }
                                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">' . $total_pages . '</a>';
                                    }
                                    ?>

                                    <!-- Next Page Link -->
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Next</span>
                                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                            <span class="sr-only">Next</span>
                                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                                        </span>
                                    <?php endif; ?>
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
// Get page name for unique localStorage keys
const page = window.location.pathname.split('/').pop().split('.')[0] || 'index';

// Persist toggle states with page prefix
const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

function loadToggleStates() {
    toggles.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            const key = id;
            const state = localStorage.getItem(key);
            if (state !== null) {
                element.checked = state === 'true';
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

function updateTime() {
    const date = new Date();
    document.getElementById('date').innerText = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}).toUpperCase();
    document.getElementById('time').innerText = date.toLocaleTimeString('en-US', {hour12: false});
}
setInterval(updateTime, 1000);
updateTime(); // initial

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

// Make table rows clickable
document.addEventListener('DOMContentLoaded', function() {
    const clickableRows = document.querySelectorAll('.clickable-row');
    clickableRows.forEach(row => {
        row.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            window.location.href = 'sign-off-detail.php?id=' + id;
        });
    });
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
</body>
</html>
