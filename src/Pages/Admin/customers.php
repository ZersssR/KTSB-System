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
$currentPage = 'customer.php';

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
                    // Get the next customer ID (running number)
                    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(customer_ifs_id, 5) AS UNSIGNED)) as max_id FROM customers WHERE customer_ifs_id LIKE 'CUS%'");
                    $result = $stmt->fetch();
                    $next_id = ($result['max_id'] ?? 0) + 1;
                    $customer_ifs_id = 'CUS' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("
                        INSERT INTO customers (
                            customer_ifs_id, name, customer_full_name, phone,
                            contact_person, contact_person_designation, contact_person_email,
                            address, postcode, location, endorser_number_required,
                            customer_status, customer_price_group, email_notification, 
                            account_status, block_reason, new_request_status,
                            internal_use, is_agent
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $customer_ifs_id,
                        sanitize($_POST['name']),
                        sanitize($_POST['customer_full_name'] ?: null),
                        sanitize($_POST['phone'] ?: null),
                        sanitize($_POST['contact_person'] ?: null),
                        sanitize($_POST['contact_person_designation'] ?: null),
                        sanitize($_POST['contact_person_email'] ?: null),
                        sanitize($_POST['address'] ?: null),
                        sanitize($_POST['postcode'] ?: null),
                        sanitize($_POST['location'] ?: null),
                        intval($_POST['endorser_number_required'] ?: 1),
                        sanitize($_POST['customer_status'] ?: 'active'),
                        sanitize($_POST['customer_price_group'] ?: null),
                        isset($_POST['email_notification']) ? 1 : 0,
                        sanitize($_POST['account_status'] ?: 'released'),
                        sanitize($_POST['block_reason'] ?: null),
                        sanitize($_POST['new_request_status'] ?: 'allow'),
                        isset($_POST['internal_use']) ? 1 : 0,
                        isset($_POST['is_agent']) ? 1 : 0
                    ]);

                    logActivity($user_id, 'create', 'Created new customer: ' . sanitize($_POST['name']) . ' with ID: ' . $customer_ifs_id, $pdo->lastInsertId());
                    $message = 'Customer created successfully with ID: ' . $customer_ifs_id . '!';
                    $message_type = 'success';
                    break;

                case 'update':
                    $stmt = $pdo->prepare("
                        UPDATE customers SET
                            name = ?, customer_full_name = ?, phone = ?,
                            contact_person = ?, contact_person_designation = ?, contact_person_email = ?,
                            address = ?, postcode = ?, location = ?, endorser_number_required = ?,
                            customer_status = ?, customer_price_group = ?,
                            email_notification = ?, account_status = ?, block_reason = ?, new_request_status = ?,
                            internal_use = ?, is_agent = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        sanitize($_POST['name']),
                        sanitize($_POST['customer_full_name'] ?: null),
                        sanitize($_POST['phone'] ?: null),
                        sanitize($_POST['contact_person'] ?: null),
                        sanitize($_POST['contact_person_designation'] ?: null),
                        sanitize($_POST['contact_person_email'] ?: null),
                        sanitize($_POST['address'] ?: null),
                        sanitize($_POST['postcode'] ?: null),
                        sanitize($_POST['location'] ?: null),
                        intval($_POST['endorser_number_required'] ?: 1),
                        sanitize($_POST['customer_status'] ?: 'active'),
                        sanitize($_POST['customer_price_group'] ?: null),
                        isset($_POST['email_notification']) ? 1 : 0,
                        sanitize($_POST['account_status'] ?: 'released'),
                        sanitize($_POST['block_reason'] ?: null),
                        sanitize($_POST['new_request_status'] ?: 'allow'),
                        isset($_POST['internal_use']) ? 1 : 0,
                        isset($_POST['is_agent']) ? 1 : 0,
                        intval($_POST['id'])
                    ]);

                    logActivity($user_id, 'update', 'Updated customer: ' . sanitize($_POST['name']), intval($_POST['id']));
                    $message = 'Customer updated successfully!';
                    $message_type = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("SELECT name FROM customers WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);
                    $customer_name = $stmt->fetch()['name'];

                    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
                    $stmt->execute([intval($_POST['id'])]);

                    logActivity($user_id, 'delete', 'Deleted customer: ' . $customer_name, intval($_POST['id']));
                    $message = 'Customer deleted successfully!';
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
$stmt = $pdo->query("SELECT COUNT(*) FROM customers");
$total_customers = $stmt->fetchColumn();
$total_pages = ceil($total_customers / $limit);

// Get customers for current page
$stmt = $pdo->prepare("SELECT * FROM customers ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll();

// Get customer for editing
$edit_customer = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_customer = $stmt->fetch();
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$account_status_filter = isset($_GET['account_status']) ? sanitize($_GET['account_status']) : '';
$created_from = isset($_GET['created_from']) ? sanitize($_GET['created_from']) : '';
$created_to = isset($_GET['created_to']) ? sanitize($_GET['created_to']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR customer_ifs_id LIKE ? OR phone LIKE ? OR contact_person_email LIKE ? OR address LIKE ?)";
    $search_term = "%{$search}%";
    $params = array_merge($params, array_fill(0, 5, $search_term));
}

if (!empty($status_filter)) {
    $where_conditions[] = "customer_status = ?";
    $params[] = $status_filter;
}

if (!empty($account_status_filter)) {
    $where_conditions[] = "account_status = ?";
    $params[] = $account_status_filter;
}

if (!empty($created_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $created_from;
}

if (!empty($created_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $created_to;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM customers $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_customers = $stmt->fetchColumn();
$total_pages = ceil($total_customers / $limit);

// Get customers for current page
$sql = "SELECT * FROM customers $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind search parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(($key + 1), $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Customer Management - Admin</title>
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

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Customers</h1>
            <p class="text-slate-600 dark:text-slate-400">Manage customer information and contacts.</p>
        </div>
        <button onclick="showCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 inline-flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            Create Customer
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
                <input type="text" name="search" id="customerSearch" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       placeholder="Search by name, ID, phone, email, or address..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500">
            </div>
            
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="?page=customers" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
        <button onclick="exportCustomers()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
                <!-- Add filter fields here -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                        <option value="">All Status</option>
                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Account Status</label>
                    <select name="account_status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                        <option value="">All Account Status</option>
                        <option value="released" <?php echo (isset($_GET['account_status']) && $_GET['account_status'] == 'released') ? 'selected' : ''; ?>>Released</option>
                        <option value="blocked" <?php echo (isset($_GET['account_status']) && $_GET['account_status'] == 'blocked') ? 'selected' : ''; ?>>Blocked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Created Date From</label>
                    <input type="date" name="created_from" 
                           value="<?php echo isset($_GET['created_from']) ? htmlspecialchars($_GET['created_from']) : ''; ?>"
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Created Date To</label>
                    <input type="date" name="created_to" 
                           value="<?php echo isset($_GET['created_to']) ? htmlspecialchars($_GET['created_to']) : ''; ?>"
                           class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
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
    <table class="w-full text-left table-auto">
        <thead class="bg-slate-50 dark:bg-slate-700">
            <tr class="border-b border-slate-200 dark:border-slate-600">
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white w-16">No.</th>
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Customer ID</th>
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Customer Name</th>
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Created At</th>
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Updated At</th>
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Status</th>
                <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
            <?php 
            $counter = $offset + 1; // Start counter from the current offset + 1
            foreach ($customers as $customer): 
            ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm font-mono">
                        <?php echo str_pad($counter, 2, '0', STR_PAD_LEFT); ?>
                    </td>
                    <td class="px-6 py-4 text-slate-900 dark:text-white font-medium">
                        <?php echo htmlspecialchars(($customer['customer_ifs_id'] ?? null) ?: '-'); ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                <?php
                                $name = $customer['name'];
                                $initials = strtoupper(substr($name, 0, 1));
                                if (strpos($name, ' ') !== false) {
                                    $parts = explode(' ', $name);
                                    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                }
                                echo htmlspecialchars($initials);
                                ?>
                            </div>
                            <div class="text-slate-900 dark:text-white font-semibold"><?php echo htmlspecialchars($customer['name']); ?></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">
                        <?php echo date('Y-m-d H:i', strtotime($customer['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm">
                        <?php echo date('Y-m-d H:i', strtotime($customer['updated_at'])); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo ($customer['customer_status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; ?>">
                            <?php echo ucfirst($customer['customer_status'] ?? 'active'); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="editCustomer(<?php echo $customer['id']; ?>)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </button>
                            <button onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars(addslashes($customer['name'])); ?>')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors" title="Delete">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php 
                $counter++;
                endforeach; 
            ?>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                        <div class="flex flex-col items-center gap-2">
                            <span class="material-symbols-outlined text-4xl text-gray-300">people</span>
                            <span>No customers found. Create your first customer to get started.</span>
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
        Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_customers); ?></span> of <span class="font-medium"><?php echo $total_customers; ?></span> results
    </div>
    <div class="flex items-center gap-2">
        <?php
        // Build query string for pagination
        $query_params = [];
        if (!empty($search)) $query_params['search'] = $search;
        if (!empty($status_filter)) $query_params['status'] = $status_filter;
        if (!empty($account_status_filter)) $query_params['account_status'] = $account_status_filter;
        if (!empty($created_from)) $query_params['created_from'] = $created_from;
        if (!empty($created_to)) $query_params['created_to'] = $created_to;
        $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
        ?>
        
        <a href="?page=customers&p=<?php echo max(1, $page - 1); ?><?php echo $query_string; ?>" 
           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
            <span class="hidden sm:inline ml-1">Previous</span>
        </a>

        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-primary/10 border border-primary/20 rounded-lg">
            <?php echo $page; ?>
        </span>

        <a href="?page=customers&p=<?php echo min($total_pages, $page + 1); ?><?php echo $query_string; ?>" 
           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page >= $total_pages ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
            <span class="hidden sm:inline mr-1">Next</span>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </a>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="customerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-8 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white" id="modalTitle">Create Customer</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="customerForm" method="POST" class="p-8 space-y-8">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="recordId" value="">

                <!-- Customer Information Section -->
                <div class="space-y-4">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3">Customer Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Name*</label>
                            <input type="text" name="name" id="customerName" required
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., ABC Corporation">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Full Name</label>
                            <input type="text" name="customer_full_name" id="customerFullName"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., ABC Corporation Sdn Bhd">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Phone</label>
                            <input type="tel" name="phone" id="customerPhone"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., +6012-345-6789">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Contact Person</label>
                            <input type="text" name="contact_person" id="contactPerson"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Contact Person Designation</label>
                            <input type="text" name="contact_person_designation" id="contactPersonDesignation"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., Operations Manager">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Contact Person Email</label>
                            <input type="email" name="contact_person_email" id="contactPersonEmail"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., john@example.com">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Address</label>
                            <textarea name="address" id="customerAddress" rows="3"
                                      class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                      placeholder="Full customer address"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Postcode</label>
                            <input type="text" name="postcode" id="customerPostcode"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., 20000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Location</label>
                            <input type="text" name="location" id="customerLocation"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                   placeholder="e.g., Kuala Terengganu">
                        </div>
                    </div>
                </div>

                <!-- Endorser Number Section -->
                <div class="space-y-4">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3">Additional Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Endorser Number Required</label>
                            <input type="number" name="endorser_number_required" id="endorserNumberRequired" value="1" min="1"
                                   class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Status & Preferences -->
                <div class="space-y-4">
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3">Status & Preferences</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Status*</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="customer_status" value="active" id="customerStatusActive" checked
                                           class="text-primary focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Active</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="customer_status" value="inactive" id="customerStatusInactive"
                                           class="text-primary focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Inactive</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer Price Group</label>
                            <select name="customer_price_group" id="customerPriceGroup"
                                    class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="">Select Price Group</option>
                                <option value="PAC">PAC</option>
                                <option value="Non PAC">Non PAC</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Account Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="account_status" value="released" id="accountStatusReleased" checked
                                           class="text-primary focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Released</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="account_status" value="blocked" id="accountStatusBlocked"
                                           class="text-primary focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Blocked</span>
                                </label>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Reason (for blocking)</label>
                            <textarea name="block_reason" id="blockReason" rows="2"
                                      class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                      placeholder="Reason for blocking account"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">New Request</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="new_request_status" value="allow" id="newRequestAllow" checked
                                           class="text-primary focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Allow</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="new_request_status" value="on_hold" id="newRequestOnHold"
                                           class="text-primary focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">On hold</span>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="email_notification" id="emailNotification" checked
                                       class="text-primary focus:ring-primary rounded">
                                <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Email Notification</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="internal_use" id="internalUse"
                                       class="text-primary focus:ring-primary rounded">
                                <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Internal Use</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_agent" id="isAgent"
                                       class="text-primary focus:ring-primary rounded">
                                <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">Customer Agent</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-8 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-primary/90 transition-colors">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Customer';
    document.getElementById('formAction').value = 'create';
    document.getElementById('recordId').value = '';
    document.getElementById('customerForm').reset();
    
    // Set default status to active
    document.getElementById('customerStatusActive').checked = true;
    
    document.getElementById('customerModal').classList.remove('hidden');
}

function editCustomer(id) {
    document.getElementById('modalTitle').textContent = 'Edit Customer';
    document.getElementById('formAction').value = 'update';
    document.getElementById('recordId').value = id;

    // Load customer data (this would typically be done via AJAX, but for this demo we'll use a form submission)
    window.location.href = '?page=customers&edit=' + id;
}

function deleteCustomer(id, name) {
    if (confirm(`Are you sure you want to delete customer "${name}"?`)) {
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
    document.getElementById('customerModal').classList.add('hidden');
}

// Toolbar functionality
function showFilters() {
    document.getElementById('filterModal').classList.remove('hidden');
}

function closeFilterModal() {
    document.getElementById('filterModal').classList.add('hidden');
}

function exportCustomers() {
    // Simple export functionality
    const customers = <?php echo json_encode($customers); ?>;
    if (customers.length === 0) {
        alert('No customers to export');
        return;
    }

    // Create CSV content
    const headers = ['Customer ID', 'Customer Name', 'Customer Status', 'Created At', 'Updated At'];
    const csvContent = [
        headers.join(','),
        ...customers.map(customer => [
            customer.customer_ifs_id || '',
            customer.name,
            customer.customer_status || 'active',
            customer.created_at,
            customer.updated_at
        ].map(field => `"${field.toString().replace(/"/g, '""')}"`).join(','))
    ].join('\n');

    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'customers_export_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Only keep the form submission behavior
    const searchInput = document.getElementById('customerSearch');
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
});

<?php if ($edit_customer): ?>
// Populate form with customer data
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalTitle').textContent = 'Edit Customer';
    document.getElementById('formAction').value = 'update';
    document.getElementById('recordId').value = '<?php echo $edit_customer['id']; ?>';

    // Customer Information
    document.getElementById('customerName').value = '<?php echo addslashes($edit_customer['name']); ?>';
    document.getElementById('customerFullName').value = '<?php echo addslashes($edit_customer['customer_full_name'] ?? ''); ?>';
    document.getElementById('customerPhone').value = '<?php echo addslashes($edit_customer['phone'] ?? ''); ?>';
    document.getElementById('contactPerson').value = '<?php echo addslashes($edit_customer['contact_person'] ?? ''); ?>';
    document.getElementById('contactPersonDesignation').value = '<?php echo addslashes($edit_customer['contact_person_designation'] ?? ''); ?>';
    document.getElementById('contactPersonEmail').value = '<?php echo addslashes($edit_customer['contact_person_email'] ?? ''); ?>';
    document.getElementById('customerAddress').value = '<?php echo addslashes($edit_customer['address'] ?? ''); ?>';
    document.getElementById('customerPostcode').value = '<?php echo addslashes($edit_customer['postcode'] ?? ''); ?>';
    document.getElementById('customerLocation').value = '<?php echo addslashes($edit_customer['location'] ?? ''); ?>';

    // Endorser Number
    document.getElementById('endorserNumberRequired').value = '<?php echo intval($edit_customer['endorser_number_required'] ?? 1); ?>';

    // Status & Preferences
    const customerStatus = '<?php echo $edit_customer['customer_status'] ?? 'active'; ?>';
    document.querySelector(`input[name="customer_status"][value="${customerStatus}"]`).checked = true;

    document.getElementById('customerPriceGroup').value = '<?php echo addslashes($edit_customer['customer_price_group'] ?? ''); ?>';

    const accountStatus = '<?php echo $edit_customer['account_status'] ?? 'released'; ?>';
    document.querySelector(`input[name="account_status"][value="${accountStatus}"]`).checked = true;

    document.getElementById('blockReason').value = '<?php echo addslashes($edit_customer['block_reason'] ?? ''); ?>';

    const newRequestStatus = '<?php echo $edit_customer['new_request_status'] ?? 'allow'; ?>';
    document.querySelector(`input[name="new_request_status"][value="${newRequestStatus}"]`).checked = true;

    document.getElementById('emailNotification').checked = <?php echo $edit_customer['email_notification'] ? 'true' : 'false'; ?>;
    document.getElementById('internalUse').checked = <?php echo $edit_customer['internal_use'] ? 'true' : 'false'; ?>;
    document.getElementById('isAgent').checked = <?php echo $edit_customer['is_agent'] ? 'true' : 'false'; ?>;

    document.getElementById('customerModal').classList.remove('hidden');
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