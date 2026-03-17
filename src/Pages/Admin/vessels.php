<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';
require_once __DIR__ . '/../../../config/app.php';

$currentUser = getCurrentUser();
$user_id = $currentUser['id']; 

$pdo = getDBConnection();

// Helper functions if not defined globally
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

// Handle CRUD operations
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        try {
            switch ($action) {
                case 'create':
                    // Get the next vessel ID (running number)
                    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(vessel_code, 5) AS UNSIGNED)) as max_id FROM vessels WHERE vessel_code LIKE 'VSL%'");
                    $result = $stmt->fetch();
                    $next_id = ($result['max_id'] ?? 0) + 1;
                    $vessel_code = 'VSL' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("
                        INSERT INTO vessels (vessel_code, vessel_name, ship_type, status, loa_meters, draft_meters, vessel_df, gt_tonnage, nt_tonnage, dwt_tonnage)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $vessel_code,
                        sanitize($_POST['vessel_name']),
                        sanitize($_POST['vessel_type']),
                        sanitize($_POST['status']),
                        floatval($_POST['loa_meters']),
                        floatval($_POST['draft_meters']),
                        floatval($_POST['vessel_df']),
                        floatval($_POST['gt_tonnage']),
                        floatval($_POST['nt_tonnage']),
                        floatval($_POST['dwt_tonnage'])
                    ]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'create', 'Created new vessel: ' . sanitize($_POST['vessel_name']) . ' with ID: ' . $vessel_code, $pdo->lastInsertId());
                    }
                    $message = 'Vessel created successfully with ID: ' . $vessel_code . '!';
                    $message_type = 'success';
                    break;

                case 'update':
                    $stmt = $pdo->prepare("
                        UPDATE vessels SET
                            vessel_name = ?, ship_type = ?, status = ?,
                            loa_meters = ?, draft_meters = ?, vessel_df = ?, gt_tonnage = ?,
                            nt_tonnage = ?, dwt_tonnage = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        sanitize($_POST['vessel_name']),
                        sanitize($_POST['vessel_type']),
                        sanitize($_POST['status']),
                        floatval($_POST['loa_meters']),
                        floatval($_POST['draft_meters']),
                        floatval($_POST['vessel_df']),
                        floatval($_POST['gt_tonnage']),
                        floatval($_POST['nt_tonnage']),
                        floatval($_POST['dwt_tonnage']),
                        intval($_POST['id'])
                    ]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'update', 'Updated vessel: ' . sanitize($_POST['vessel_name']), intval($_POST['id']));
                    }
                    $message = 'Vessel updated successfully!';
                    $message_type = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("SELECT vessel_name FROM vessels WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);
                    $vessel_name = $stmt->fetch()['vessel_name'];

                    $stmt = $pdo->prepare("DELETE FROM vessels WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'delete', 'Deleted vessel: ' . $vessel_name, intval($_POST['id']));
                    }
                    $message = 'Vessel deleted successfully!';
                    $message_type = 'success';
                    break;
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM vessels");
$total_vessels = $stmt->fetchColumn();
$total_pages = ceil($total_vessels / $limit);

// Get vessels for current page
$stmt = $pdo->prepare("SELECT * FROM vessels ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vessels = $stmt->fetchAll();

// Get vessel for editing
$edit_vessel = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM vessels WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_vessel = $stmt->fetch();
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$vessel_type_filter = isset($_GET['vessel_type']) ? sanitize($_GET['vessel_type']) : '';
$loa_min = isset($_GET['loa_min']) ? floatval($_GET['loa_min']) : null;
$loa_max = isset($_GET['loa_max']) ? floatval($_GET['loa_max']) : null;
$dwt_min = isset($_GET['dwt_min']) ? floatval($_GET['dwt_min']) : null;
$dwt_max = isset($_GET['dwt_max']) ? floatval($_GET['dwt_max']) : null;

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(vessel_name LIKE ? OR vessel_code LIKE ? OR ship_type LIKE ?)";
    $search_term = "%{$search}%";
    $params = array_merge($params, array_fill(0, 3, $search_term));
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($vessel_type_filter)) {
    $where_conditions[] = "ship_type = ?";
    $params[] = $vessel_type_filter;
}

if ($loa_min !== null) {
    $where_conditions[] = "loa_meters >= ?";
    $params[] = $loa_min;
}

if ($loa_max !== null) {
    $where_conditions[] = "loa_meters <= ?";
    $params[] = $loa_max;
}

if ($dwt_min !== null) {
    $where_conditions[] = "dwt_tonnage >= ?";
    $params[] = $dwt_min;
}

if ($dwt_max !== null) {
    $where_conditions[] = "dwt_tonnage <= ?";
    $params[] = $dwt_max;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count with filters
$count_sql = "SELECT COUNT(*) FROM vessels $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_vessels = $stmt->fetchColumn();
$total_pages = ceil($total_vessels / $limit);

// Get vessels for current page with filters
$sql = "SELECT * FROM vessels $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind search parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(($key + 1), $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vessels = $stmt->fetchAll();

// Vessel types for dropdown
$vessel_types = [
    'Barge',
    'Commercial Vessel',
    'Crew Boat',
    'General Project',
    'MISC',
    'RIG',
    'Supply Vessel',
    'Survey Vessel',
    'Tanker'
];
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Vessels Management - Admin</title>
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
        .status-active {
            background-color: #10B981;
            color: white;
        }
        .status-inactive {
            background-color: #6B7280;
            color: white;
        }
    </style>
    <script src="../tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <?php include __DIR__ . '/../../Components/Layout/AdminSidebar.php'; ?>

        <!-- Header Bar -->
        <header class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Kuala Terengganu Support Base (Administrator)</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button type="button" id="profile-dropdown-btn" class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors" title="Profile">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['username'] ?? 'Admin'); ?></span>
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
            <main class="relative flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-7xl mx-auto space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Vessels</h1>
                                <p class="text-slate-600 dark:text-slate-400">Manage vessel registration and specifications.</p>
                            </div>
                            <button onclick="showCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 inline-flex items-center gap-2">
                                <span class="material-symbols-outlined">add</span>
                                Create Vessel
                            </button>
                        </div>

                        <?php if ($message): ?>
                            <div class="p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Top Toolbar -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6 pt-6">
                            <div class="relative flex-1">
                                <form method="GET" action="" class="flex gap-2">
                                    <!-- Keep existing GET parameters -->
                                    <?php if(isset($_GET['edit'])): ?>
                                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                                    <?php endif; ?>
                                    
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-gray-400">search</span>
                                        </div>
                                        <input type="text" name="search" id="vesselSearch" 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                               placeholder="Search by vessel name, ID, or type..."
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500">
                                    </div>
                                    
                                    <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                        <a href="?page=vessels" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            Clear
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary/90">
                                        Search
                                    </button>
                                </form>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="showFilters()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <span class="material-symbols-outlined text-sm mr-2">filter_list</span>
                                    Advanced Filter
                                </button>
                                <button onclick="exportVessels()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <span class="material-symbols-outlined text-sm mr-2">download</span>
                                    Export
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Modal -->
                        <div id="filterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full">
                                    <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Advanced Filters</h3>
                                        <button onclick="closeFilterModal()" class="text-slate-400 hover:text-slate-600">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </div>
                                    <form id="filterForm" method="GET" action="" class="p-6 space-y-4">
                                        <input type="hidden" name="page" value="vessels">
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                                            <select name="status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                                <option value="">All Status</option>
                                                <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Vessel Type</label>
                                            <select name="vessel_type" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                                <option value="">All Types</option>
                                                <?php foreach ($vessel_types as $type): ?>
                                                    <option value="<?php echo $type; ?>" <?php echo (isset($_GET['vessel_type']) && $_GET['vessel_type'] == $type) ? 'selected' : ''; ?>>
                                                        <?php echo $type; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Min LOA (m)</label>
                                                <input type="number" step="0.1" name="loa_min" 
                                                       value="<?php echo isset($_GET['loa_min']) ? htmlspecialchars($_GET['loa_min']) : ''; ?>"
                                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Max LOA (m)</label>
                                                <input type="number" step="0.1" name="loa_max" 
                                                       value="<?php echo isset($_GET['loa_max']) ? htmlspecialchars($_GET['loa_max']) : ''; ?>"
                                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Min DWT</label>
                                                <input type="number" name="dwt_min" 
                                                       value="<?php echo isset($_GET['dwt_min']) ? htmlspecialchars($_GET['dwt_min']) : ''; ?>"
                                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Max DWT</label>
                                                <input type="number" name="dwt_max" 
                                                       value="<?php echo isset($_GET['dwt_max']) ? htmlspecialchars($_GET['dwt_max']) : ''; ?>"
                                                       class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                            </div>
                                        </div>
                                        
                                        <div class="flex justify-end gap-3 pt-4">
                                            <button type="button" onclick="closeFilterModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90">Apply Filters</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <table class="w-full text-left table-auto">
                                <thead class="bg-slate-50 dark:bg-slate-700">
                                    <tr class="border-b border-slate-200 dark:border-slate-600">
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white w-16">No.</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Vessel ID</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Vessel Name</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Vessel Type</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Status</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Created At</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Updated At</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
                                    <?php 
                                    $counter = $offset + 1;
                                    foreach ($vessels as $vessel): 
                                    ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm font-mono">
                                                <?php echo str_pad($counter, 2, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="px-6 py-4 text-slate-900 dark:text-white font-medium">
                                                <?php echo htmlspecialchars($vessel['vessel_code'] ?? 'VSL' . str_pad($vessel['id'], 3, '0', STR_PAD_LEFT)); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                        <?php
                                                        $name = $vessel['vessel_name'];
                                                        $initials = strtoupper(substr($name, 0, 1));
                                                        echo htmlspecialchars($initials);
                                                        ?>
                                                    </div>
                                                    <div class="text-slate-900 dark:text-white font-semibold"><?php echo htmlspecialchars($vessel['vessel_name']); ?></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                                <?php echo htmlspecialchars($vessel['ship_type'] ?? '-'); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo ($vessel['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; ?>">
                                                    <?php echo ucfirst($vessel['status'] ?? 'active'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">
                                                <?php echo date('Y-m-d H:i', strtotime($vessel['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">
                                                <?php echo date('Y-m-d H:i', strtotime($vessel['updated_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button onclick="editVessel(<?php echo $vessel['id']; ?>)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                                        <span class="material-symbols-outlined text-base">edit</span>
                                                    </button>
                                                    <button onclick="deleteVessel(<?php echo $vessel['id']; ?>, '<?php echo htmlspecialchars(addslashes($vessel['vessel_name'])); ?>')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors" title="Delete">
                                                        <span class="material-symbols-outlined text-base">delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        $counter++;
                                        endforeach; 
                                    ?>
                                    <?php if (empty($vessels)): ?>
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="material-symbols-outlined text-4xl text-gray-300">directions_boat</span>
                                                    <span>No vessels found. Create your first vessel to get started.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <!-- Pagination Footer -->
                            <div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-4">
                                <div class="text-sm text-slate-700 dark:text-slate-300">
                                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_vessels); ?></span> of <span class="font-medium"><?php echo $total_vessels; ?></span> results
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php
                                    // Build query string for pagination
                                    $query_params = [];
                                    if (!empty($search)) $query_params['search'] = $search;
                                    if (!empty($status_filter)) $query_params['status'] = $status_filter;
                                    if (!empty($vessel_type_filter)) $query_params['vessel_type'] = $vessel_type_filter;
                                    if ($loa_min !== null) $query_params['loa_min'] = $loa_min;
                                    if ($loa_max !== null) $query_params['loa_max'] = $loa_max;
                                    if ($dwt_min !== null) $query_params['dwt_min'] = $dwt_min;
                                    if ($dwt_max !== null) $query_params['dwt_max'] = $dwt_max;
                                    $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
                                    ?>
                                    
                                    <a href="?page=vessels&p=<?php echo max(1, $page - 1); ?><?php echo $query_string; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
                                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                                        <span class="hidden sm:inline ml-1">Previous</span>
                                    </a>

                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-primary/10 border border-primary/20 rounded-lg">
                                        <?php echo $page; ?>
                                    </span>

                                    <a href="?page=vessels&p=<?php echo min($total_pages, $page + 1); ?><?php echo $query_string; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page >= $total_pages ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
                                        <span class="hidden sm:inline mr-1">Next</span>
                                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="vesselModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-8 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white" id="modalTitle">Create Vessel</h3>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="vesselForm" method="POST" class="p-8 space-y-6">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="vesselId" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                             <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">General Information</h4>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vessel Name *</label>
                            <input type="text" name="vessel_name" id="vesselName" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="e.g., Alkahfi Care">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vessel Type *</label>
                            <select name="vessel_type" id="vesselType" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="">Select Vessel Type</option>
                                <?php foreach ($vessel_types as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                         <div class="md:col-span-2 mt-4">
                             <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Technical Specifications</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Max Draught (m)</label>
                            <input type="number" step="0.1" name="draft_meters" id="draftMeters"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="0.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">LOA (m)</label>
                            <input type="number" step="0.1" name="loa_meters" id="loaMeters"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="0.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">DF</label>
                            <input type="number" step="0.1" name="vessel_df" id="vesselDf"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="0.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">DWT</label>
                            <input type="number" name="dwt_tonnage" id="dwtTonnage"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">GRT</label>
                            <input type="number" name="gt_tonnage" id="gtTonnage"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NRT</label>
                            <input type="number" name="nt_tonnage" id="ntTonnage"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                placeholder="0">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" onclick="closeModal()" class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-primary/90 transition-colors">Save Vessel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function showCreateModal() {
        document.getElementById('modalTitle').textContent = 'Create Vessel';
        document.getElementById('formAction').value = 'create';
        document.getElementById('vesselId').value = '';
        document.getElementById('vesselForm').reset();
        document.getElementById('status').value = 'active';
        document.getElementById('vesselModal').classList.remove('hidden');
    }

    function editVessel(id) {
        document.getElementById('modalTitle').textContent = 'Edit Vessel';
        document.getElementById('formAction').value = 'update';
        document.getElementById('vesselId').value = id;

        // Load vessel data (reload page with query param for simple fetching)
        window.location.href = '?page=vessels&edit=' + id;
    }

    function deleteVessel(id, name) {
        if (confirm(`Are you sure you want to delete vessel "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal() {
        document.getElementById('vesselModal').classList.add('hidden');
    }
    
    function exportVessels() {
        // Simple export functionality
        const vessels = <?php echo json_encode($vessels); ?>;
        if (vessels.length === 0) {
            alert('No vessels to export');
            return;
        }

        const headers = ['Vessel ID', 'Vessel Name', 'Vessel Type', 'Status', 'LOA', 'Draft', 'DF', 'DWT', 'GRT', 'NRT', 'Created At', 'Updated At'];
        const csvContent = [
            headers.join(','),
            ...vessels.map(v => [
                v.vessel_code || 'VSL' + String(v.id).padStart(3, '0'),
                v.vessel_name,
                v.ship_type,
                v.status,
                v.loa_meters,
                v.draft_meters,
                v.vessel_df,
                v.dwt_tonnage,
                v.gt_tonnage,
                v.nt_tonnage,
                v.created_at,
                v.updated_at
            ].map(field => `"${(field || '').toString().replace(/"/g, '""')}"`).join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'vessels_export_' + new Date().toISOString().split('T')[0] + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Filter modal functionality
    function showFilters() {
        document.getElementById('filterModal').classList.remove('hidden');
    }

    function closeFilterModal() {
        document.getElementById('filterModal').classList.add('hidden');
    }
    
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Only keep the form submission behavior
        const searchInput = document.getElementById('vesselSearch');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        }
        
        // Close filter modal on outside click
        const filterModal = document.getElementById('filterModal');
        if (filterModal) {
            filterModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeFilterModal();
                }
            });
        }
        
        // Date and Time
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            
            document.getElementById('date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Profile dropdown functionality
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

    <?php if ($edit_vessel): ?>
    // Populate form with vessel data
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalTitle').textContent = 'Edit Vessel';
        document.getElementById('formAction').value = 'update';
        document.getElementById('vesselId').value = '<?php echo $edit_vessel['id']; ?>';
        document.getElementById('vesselName').value = '<?php echo addslashes($edit_vessel['vessel_name']); ?>';
        
        // Set vessel type dropdown
        const vesselTypeSelect = document.getElementById('vesselType');
        const vesselTypeValue = '<?php echo addslashes($edit_vessel['ship_type'] ?? ''); ?>';
        if (vesselTypeValue) {
            for (let i = 0; i < vesselTypeSelect.options.length; i++) {
                if (vesselTypeSelect.options[i].value === vesselTypeValue) {
                    vesselTypeSelect.selectedIndex = i;
                    break;
                }
            }
        }
        
        document.getElementById('status').value = '<?php echo $edit_vessel['status'] ?? 'active'; ?>';
        document.getElementById('loaMeters').value = '<?php echo $edit_vessel['loa_meters'] ?? ''; ?>';
        document.getElementById('draftMeters').value = '<?php echo $edit_vessel['draft_meters'] ?? ''; ?>';
        document.getElementById('vesselDf').value = '<?php echo $edit_vessel['vessel_df'] ?? ''; ?>';
        document.getElementById('gtTonnage').value = '<?php echo $edit_vessel['gt_tonnage'] ?? ''; ?>';
        document.getElementById('ntTonnage').value = '<?php echo $edit_vessel['nt_tonnage'] ?? ''; ?>';
        document.getElementById('dwtTonnage').value = '<?php echo $edit_vessel['dwt_tonnage'] ?? ''; ?>';
        document.getElementById('vesselModal').classList.remove('hidden');
    });
    <?php endif; ?>
    </script>
</body>
</html>