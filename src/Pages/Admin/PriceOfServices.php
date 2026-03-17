<?php
require_once __DIR__ . '/../../../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin details
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT username, id FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$user_id = $admin['id'];
$currentPage = 'price_of_services.php';

// Helper function for activity logging if not defined
if (!function_exists('logActivity')) {
    function logActivity($user_id, $activity_type, $description, $reference_id = null) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO activities (user_id, activity_type, description, reference_id, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $activity_type,
            $description,
            $reference_id,
            $_SERVER['REMOTE_ADDR'] ?? 'system'
        ]);
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
                    // Get the next price ID (running number)
                    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(price_id, 4) AS UNSIGNED)) as max_id FROM price_of_services WHERE price_id LIKE 'PRC%'");
                    $result = $stmt->fetch();
                    $next_id = ($result['max_id'] ?? 0) + 1;
                    $price_id = 'PRC' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("
                        INSERT INTO price_of_services (
                            price_id, scope_of_work, rate, effective_date, expiry_date, customer_group, status, created_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $price_id,
                        sanitize($_POST['scope_of_work']),
                        floatval($_POST['rate']),
                        $_POST['effective_date'],
                        $_POST['expiry_date'],
                        sanitize($_POST['customer_group']),
                        sanitize($_POST['status'] ?: 'active'),
                        $user_id
                    ]);

                    logActivity($user_id, 'create', 'Created new price: ' . $price_id . ' - ' . sanitize($_POST['scope_of_work']), $pdo->lastInsertId());
                    $message = 'Price created successfully with ID: ' . $price_id . '!';
                    $message_type = 'success';
                    break;

                case 'update':
                    $stmt = $pdo->prepare("
                        UPDATE price_of_services SET
                            scope_of_work = ?, rate = ?, effective_date = ?,
                            expiry_date = ?, customer_group = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        sanitize($_POST['scope_of_work']),
                        floatval($_POST['rate']),
                        $_POST['effective_date'],
                        $_POST['expiry_date'],
                        sanitize($_POST['customer_group']),
                        sanitize($_POST['status'] ?: 'active'),
                        intval($_POST['id'])
                    ]);

                    logActivity($user_id, 'update', 'Updated price: ' . sanitize($_POST['scope_of_work']), intval($_POST['id']));
                    $message = 'Price updated successfully!';
                    $message_type = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("SELECT price_id, scope_of_work FROM price_of_services WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);
                    $price = $stmt->fetch();

                    $stmt = $pdo->prepare("DELETE FROM price_of_services WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);

                    logActivity($user_id, 'delete', 'Deleted price: ' . $price['price_id'] . ' - ' . $price['scope_of_work'], intval($_POST['id']));
                    $message = 'Price deleted successfully!';
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
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM price_of_services");
$total_prices = $stmt->fetchColumn();
$total_pages = ceil($total_prices / $limit);

// Get prices for current page
$stmt = $pdo->prepare("SELECT * FROM price_of_services ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$prices = $stmt->fetchAll();

// Get price for editing
$edit_price = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM price_of_services WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_price = $stmt->fetch();
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$customer_group_filter = isset($_GET['customer_group']) ? sanitize($_GET['customer_group']) : '';
$effective_from = isset($_GET['effective_from']) ? sanitize($_GET['effective_from']) : '';
$effective_to = isset($_GET['effective_to']) ? sanitize($_GET['effective_to']) : '';
$expiry_from = isset($_GET['expiry_from']) ? sanitize($_GET['expiry_from']) : '';
$expiry_to = isset($_GET['expiry_to']) ? sanitize($_GET['expiry_to']) : '';
$rate_min = isset($_GET['rate_min']) ? floatval($_GET['rate_min']) : null;
$rate_max = isset($_GET['rate_max']) ? floatval($_GET['rate_max']) : null;

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(scope_of_work LIKE ? OR price_id LIKE ? OR customer_group LIKE ?)";
    $search_term = "%{$search}%";
    $params = array_merge($params, array_fill(0, 3, $search_term));
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($customer_group_filter)) {
    $where_conditions[] = "customer_group = ?";
    $params[] = $customer_group_filter;
}

if (!empty($effective_from)) {
    $where_conditions[] = "effective_date >= ?";
    $params[] = $effective_from;
}

if (!empty($effective_to)) {
    $where_conditions[] = "effective_date <= ?";
    $params[] = $effective_to;
}

if (!empty($expiry_from)) {
    $where_conditions[] = "expiry_date >= ?";
    $params[] = $expiry_from;
}

if (!empty($expiry_to)) {
    $where_conditions[] = "expiry_date <= ?";
    $params[] = $expiry_to;
}

if ($rate_min !== null) {
    $where_conditions[] = "rate >= ?";
    $params[] = $rate_min;
}

if ($rate_max !== null) {
    $where_conditions[] = "rate <= ?";
    $params[] = $rate_max;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count with filters
$count_sql = "SELECT COUNT(*) FROM price_of_services $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_prices = $stmt->fetchColumn();
$total_pages = ceil($total_prices / $limit);

// Get prices for current page with filters
$sql = "SELECT * FROM price_of_services $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind search parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(($key + 1), $value, is_float($value) ? PDO::PARAM_STR : PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$prices = $stmt->fetchAll();

// Customer groups for dropdown
$customer_groups = [
    'All Customers',
    'PAC',
    'Non PAC',
    'KTSB Internal',
    'EPIC',
    'PETRONAS',
    'HALLIBURTON'
];
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Price of Services - Admin</title>
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
    </style>
    <script src="../tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
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
                <div class="mx-auto max-w-7xl">

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Price of Services</h1>
            <p class="text-slate-600 dark:text-slate-400">Manage service pricing and rates.</p>
        </div>
        <button onclick="showCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 inline-flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            Create Price
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
                <input type="text" name="search" id="priceSearch" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       placeholder="Search by ID, scope of work, or customer group..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500">
            </div>
            
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="?page=price_of_services" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
        <button onclick="exportPrices()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <span class="material-symbols-outlined text-sm mr-2">download</span>
            Export
        </button>
    </div>
</div>

<!-- Advanced Filter Modal -->
<div id="filterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Advanced Filters</h3>
                <button onclick="closeFilterModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="filterForm" method="GET" action="" class="p-6 space-y-4">
                <input type="hidden" name="page" value="price_of_services">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                        <option value="">All Status</option>
                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Group</label>
                    <select name="customer_group" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                        <option value="">All Groups</option>
                        <?php foreach ($customer_groups as $group): ?>
                            <option value="<?php echo $group; ?>" <?php echo (isset($_GET['customer_group']) && $_GET['customer_group'] == $group) ? 'selected' : ''; ?>>
                                <?php echo $group; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Min Rate (RM)</label>
                        <input type="number" step="0.01" name="rate_min" 
                               value="<?php echo isset($_GET['rate_min']) ? htmlspecialchars($_GET['rate_min']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Max Rate (RM)</label>
                        <input type="number" step="0.01" name="rate_max" 
                               value="<?php echo isset($_GET['rate_max']) ? htmlspecialchars($_GET['rate_max']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Effective Date From</label>
                        <input type="date" name="effective_from" 
                               value="<?php echo isset($_GET['effective_from']) ? htmlspecialchars($_GET['effective_from']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Effective Date To</label>
                        <input type="date" name="effective_to" 
                               value="<?php echo isset($_GET['effective_to']) ? htmlspecialchars($_GET['effective_to']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Expiry Date From</label>
                        <input type="date" name="expiry_from" 
                               value="<?php echo isset($_GET['expiry_from']) ? htmlspecialchars($_GET['expiry_from']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Expiry Date To</label>
                        <input type="date" name="expiry_to" 
                               value="<?php echo isset($_GET['expiry_to']) ? htmlspecialchars($_GET['expiry_to']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeFilterModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left table-auto">
            <thead class="bg-slate-50 dark:bg-slate-700">
                <tr class="border-b border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white w-16">No.</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Project ID</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Scope of Work Item</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Rates (RM)</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Effective Date</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Expiry Date</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Customer Group</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Status</th>
                    <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
                <?php 
                $counter = $offset + 1;
                foreach ($prices as $price): 
                ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm font-mono">
                            <?php echo str_pad($counter, 2, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-6 py-4 text-slate-900 dark:text-white font-medium">
                            <?php echo htmlspecialchars($price['price_id'] ?? 'PRC' . str_pad($price['id'], 3, '0', STR_PAD_LEFT)); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    <?php
                                    $work = $price['scope_of_work'];
                                    $initials = strtoupper(substr($work, 0, 1));
                                    if (strpos($work, ' ') !== false) {
                                        $parts = explode(' ', $work);
                                        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                    }
                                    echo htmlspecialchars($initials);
                                    ?>
                                </div>
                                <div class="text-slate-900 dark:text-white font-semibold"><?php echo htmlspecialchars($price['scope_of_work']); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium">
                            RM <?php echo number_format($price['rate'], 2); ?>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            <?php echo date('d/m/Y', strtotime($price['effective_date'])); ?>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            <?php echo $price['expiry_date'] ? date('d/m/Y', strtotime($price['expiry_date'])) : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <?php echo htmlspecialchars($price['customer_group'] ?? 'All Customers'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo ($price['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; ?>">
                                <?php echo ucfirst($price['status'] ?? 'active'); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="editPrice(<?php echo $price['id']; ?>)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <button onclick="deletePrice(<?php echo $price['id']; ?>, '<?php echo htmlspecialchars(addslashes($price['scope_of_work'])); ?>')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors" title="Delete">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php 
                    $counter++;
                    endforeach; 
                ?>
                <?php if (empty($prices)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-gray-300">price_change</span>
                                <span>No prices found. Create your first price to get started.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<!-- Pagination Footer -->
<div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-4">
    <div class="text-sm text-slate-700 dark:text-slate-300">
        Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_prices); ?></span> of <span class="font-medium"><?php echo $total_prices; ?></span> results
    </div>
    <div class="flex items-center gap-2">
        <?php
        // Build query string for pagination
        $query_params = [];
        if (!empty($search)) $query_params['search'] = $search;
        if (!empty($status_filter)) $query_params['status'] = $status_filter;
        if (!empty($customer_group_filter)) $query_params['customer_group'] = $customer_group_filter;
        if (!empty($effective_from)) $query_params['effective_from'] = $effective_from;
        if (!empty($effective_to)) $query_params['effective_to'] = $effective_to;
        if (!empty($expiry_from)) $query_params['expiry_from'] = $expiry_from;
        if (!empty($expiry_to)) $query_params['expiry_to'] = $expiry_to;
        if ($rate_min !== null) $query_params['rate_min'] = $rate_min;
        if ($rate_max !== null) $query_params['rate_max'] = $rate_max;
        $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
        ?>
        
        <a href="?page=price_of_services&p=<?php echo max(1, $page - 1); ?><?php echo $query_string; ?>" 
           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
            <span class="hidden sm:inline ml-1">Previous</span>
        </a>

        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-primary/10 border border-primary/20 rounded-lg">
            <?php echo $page; ?>
        </span>

        <a href="?page=price_of_services&p=<?php echo min($total_pages, $page + 1); ?><?php echo $query_string; ?>" 
           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page >= $total_pages ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
            <span class="hidden sm:inline mr-1">Next</span>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </a>
    </div>
</div>
</div>

<!-- Create/Edit Modal -->
<div id="priceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-8 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white" id="modalTitle">Create Price</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="priceForm" method="POST" class="p-8 space-y-8">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="recordId" value="">

                <!-- Price Information Section -->
                <div class="space-y-4">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3">Price Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Scope of Work Item *</label>
                            <textarea name="scope_of_work" id="scopeOfWork" required rows="3"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., Fuel supply for vessels, Berthing services, etc."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Rate (RM) *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500">RM</span>
                                </div>
                                <input type="number" step="0.01" name="rate" id="rate" required
                                       class="w-full pl-12 pr-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Group *</label>
                            <select name="customer_group" id="customerGroup" required
                                    class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="">Select Customer Group</option>
                                <?php foreach ($customer_groups as $group): ?>
                                    <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Effective Date *</label>
                            <input type="date" name="effective_date" id="effectiveDate" required
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiryDate"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            <p class="text-xs text-slate-500 mt-1">Leave empty if no expiry date</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                            <select name="status" id="status" required
                                    class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-8 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-primary/90 transition-colors">Save Price</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Price';
    document.getElementById('formAction').value = 'create';
    document.getElementById('recordId').value = '';
    document.getElementById('priceForm').reset();
    
    // Set default status to active
    document.getElementById('status').value = 'active';
    
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('effectiveDate').value = today;
    
    document.getElementById('priceModal').classList.remove('hidden');
}

function editPrice(id) {
    document.getElementById('modalTitle').textContent = 'Edit Price';
    document.getElementById('formAction').value = 'update';
    document.getElementById('recordId').value = id;

    // Load price data via URL redirect
    window.location.href = '?page=price_of_services&edit=' + id;
}

function deletePrice(id, scopeOfWork) {
    if (confirm(`Are you sure you want to delete price for "${scopeOfWork}"?`)) {
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
    document.getElementById('priceModal').classList.add('hidden');
}

// Toolbar functionality
function showFilters() {
    document.getElementById('filterModal').classList.remove('hidden');
}

function closeFilterModal() {
    document.getElementById('filterModal').classList.add('hidden');
}

function exportPrices() {
    // Simple export functionality
    const prices = <?php echo json_encode($prices); ?>;
    if (prices.length === 0) {
        alert('No prices to export');
        return;
    }

    // Create CSV content
    const headers = ['Project ID', 'Scope of Work Item', 'Rate (RM)', 'Effective Date', 'Expiry Date', 'Customer Group', 'Status'];
    const csvContent = [
        headers.join(','),
        ...prices.map(price => [
            price.price_id || 'PRC' + String(price.id).padStart(3, '0'),
            price.scope_of_work,
            price.rate,
            price.effective_date,
            price.expiry_date || '',
            price.customer_group || 'All Customers',
            price.status || 'active'
        ].map(field => `"${field.toString().replace(/"/g, '""')}"`).join(','))
    ].join('\n');

    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'prices_export_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Only keep the form submission behavior
    const searchInput = document.getElementById('priceSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }
    
    // Close filter modal on outside click
    document.getElementById('filterModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeFilterModal();
        }
    });

    // Validate expiry date is after effective date
    const effectiveDate = document.getElementById('effectiveDate');
    const expiryDate = document.getElementById('expiryDate');
    
    if (effectiveDate && expiryDate) {
        expiryDate.addEventListener('change', function() {
            if (effectiveDate.value && this.value && this.value < effectiveDate.value) {
                alert('Expiry date must be after effective date');
                this.value = '';
            }
        });
    }
});

<?php if ($edit_price): ?>
// Populate form with price data
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalTitle').textContent = 'Edit Price';
    document.getElementById('formAction').value = 'update';
    document.getElementById('recordId').value = '<?php echo $edit_price['id']; ?>';

    // Price Information
    document.getElementById('scopeOfWork').value = '<?php echo addslashes($edit_price['scope_of_work']); ?>';
    document.getElementById('rate').value = '<?php echo $edit_price['rate']; ?>';
    document.getElementById('effectiveDate').value = '<?php echo $edit_price['effective_date']; ?>';
    document.getElementById('expiryDate').value = '<?php echo $edit_price['expiry_date'] ?? ''; ?>';
    document.getElementById('customerGroup').value = '<?php echo addslashes($edit_price['customer_group'] ?? ''); ?>';
    document.getElementById('status').value = '<?php echo $edit_price['status'] ?? 'active'; ?>';

    document.getElementById('priceModal').classList.remove('hidden');
});
<?php endif; ?>
</script>
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
</body>

</html>