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
                    // Check if username or email already exists
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM agents WHERE username = ? OR email = ?");
                    $checkStmt->execute([sanitize($_POST['username']), sanitize($_POST['email'])]);
                    if ($checkStmt->fetchColumn() > 0) {
                        throw new Exception('Username or email already exists');
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO agents (username, password_hash, full_name, email, phone_number, company_name, customer_code, created_by, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    // Hash password (default: password123)
                    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
                    $stmt->execute([
                        sanitize($_POST['username']),
                        $password_hash,
                        sanitize($_POST['full_name']),
                        sanitize($_POST['email']),
                        sanitize($_POST['phone_number']),
                        sanitize($_POST['company_name']),
                        sanitize($_POST['customer_code']),
                        $user_id,
                        sanitize($_POST['status'])
                    ]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'create', 'Created new agent: ' . sanitize($_POST['username']), $pdo->lastInsertId());
                    }
                    $message = 'Agent created successfully! Default password: password123';
                    $message_type = 'success';
                    break;

                case 'update':
                    // Check if username or email already exists (excluding current agent)
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM agents WHERE (username = ? OR email = ?) AND agent_id != ?");
                    $checkStmt->execute([
                        sanitize($_POST['username']), 
                        sanitize($_POST['email']),
                        intval($_POST['id'])
                    ]);
                    if ($checkStmt->fetchColumn() > 0) {
                        throw new Exception('Username or email already exists');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE agents SET
                            username = ?, full_name = ?, email = ?, phone_number = ?,
                            company_name = ?, customer_code = ?, status = ?, last_login = ?
                        WHERE agent_id = ?
                    ");
                    $stmt->execute([
                        sanitize($_POST['username']),
                        sanitize($_POST['full_name']),
                        sanitize($_POST['email']),
                        sanitize($_POST['phone_number']),
                        sanitize($_POST['company_name']),
                        sanitize($_POST['customer_code']),
                        sanitize($_POST['status']),
                        $_POST['last_login'] ?: null,
                        intval($_POST['id'])
                    ]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'update', 'Updated agent: ' . sanitize($_POST['username']), intval($_POST['id']));
                    }
                    $message = 'Agent updated successfully!';
                    $message_type = 'success';
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("SELECT username FROM agents WHERE agent_id = ?");
                    $stmt->execute([intval($_POST['id'])]);
                    $agent_name = $stmt->fetch()['username'];

                    $stmt = $pdo->prepare("DELETE FROM agents WHERE agent_id = ?");
                    $stmt->execute([intval($_POST['id'])]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'delete', 'Deleted agent: ' . $agent_name, intval($_POST['id']));
                    }
                    $message = 'Agent deleted successfully!';
                    $message_type = 'success';
                    break;

                case 'reset_password':
                    $stmt = $pdo->prepare("SELECT username FROM agents WHERE agent_id = ?");
                    $stmt->execute([intval($_POST['id'])]);
                    $agent_name = $stmt->fetch()['username'];

                    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE agents SET password_hash = ? WHERE agent_id = ?");
                    $stmt->execute([$password_hash, intval($_POST['id'])]);

                    if (function_exists('logActivity')) {
                        logActivity($user_id, 'update', 'Reset password for agent: ' . $agent_name, intval($_POST['id']));
                    }
                    $message = 'Password reset successfully! New password: password123';
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

// Get companies for dropdowns
$companies_stmt = $pdo->query("SELECT company_name, company_code FROM companies ORDER BY company_name");
$companies = $companies_stmt->fetchAll();

// Build search query
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
$params = [];

if ($search) {
    $where = "WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ? OR company_name LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

// Get total count
$countQuery = "SELECT COUNT(*) FROM agents " . $where;
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$total_agents = $stmt->fetchColumn();
$total_pages = ceil($total_agents / $limit);

// Get agents for current page
$query = "SELECT a.*, u.username as created_by_name 
          FROM agents a 
          LEFT JOIN users u ON a.created_by = u.user_id 
          $where 
          ORDER BY a.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$agents = $stmt->fetchAll();

// Get agent for editing
$edit_agent = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE agent_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_agent = $stmt->fetch();
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$company_filter = isset($_GET['company']) ? sanitize($_GET['company']) : '';
$created_from = isset($_GET['created_from']) ? sanitize($_GET['created_from']) : '';
$created_to = isset($_GET['created_to']) ? sanitize($_GET['created_to']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(a.username LIKE ? OR a.email LIKE ? OR a.full_name LIKE ? OR a.company_name LIKE ?)";
    $search_term = "%{$search}%";
    $params = array_merge($params, array_fill(0, 4, $search_term));
}

if (!empty($status_filter)) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if (!empty($company_filter)) {
    $where_conditions[] = "a.company_name = ?";
    $params[] = $company_filter;
}

if (!empty($created_from)) {
    $where_conditions[] = "DATE(a.created_at) >= ?";
    $params[] = $created_from;
}

if (!empty($created_to)) {
    $where_conditions[] = "DATE(a.created_at) <= ?";
    $params[] = $created_to;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Pagination
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) FROM agents a $where_sql";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_agents = $stmt->fetchColumn();
$total_pages = ceil($total_agents / $limit);

// Get agents for current page
$query = "SELECT a.*, u.username as created_by_name 
          FROM agents a 
          LEFT JOIN users u ON a.created_by = u.user_id 
          $where_sql 
          ORDER BY a.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);

// Bind search parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(($key + 1), $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$agents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Agents Management - Admin</title>
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
                    <div class="max-w-6xl mx-auto space-y-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Agents</h1>
                                <p class="text-slate-600 dark:text-slate-400">Manage agent accounts and permissions.</p>
                            </div>
                            <button onclick="showCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 inline-flex items-center gap-2">
                                <span class="material-symbols-outlined">add</span>
                                Create Agent
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
                <input type="text" name="search" id="agentSearch" 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       placeholder="Search by username, name, email, or company..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500">
            </div>
            
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="?page=agents" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
        <button onclick="exportAgents()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
                <input type="hidden" name="page" value="agents">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        <option value="">All Status</option>
                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Company</label>
                    <select name="company" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        <option value="">All Companies</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo htmlspecialchars($company['company_name']); ?>" 
                                <?php echo (isset($_GET['company']) && $_GET['company'] == $company['company_name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($company['company_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Created From</label>
                        <input type="date" name="created_from" 
                               value="<?php echo isset($_GET['created_from']) ? htmlspecialchars($_GET['created_from']) : ''; ?>"
                               class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Created To</label>
                        <input type="date" name="created_to" 
                               value="<?php echo isset($_GET['created_to']) ? htmlspecialchars($_GET['created_to']) : ''; ?>"
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
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Agent ID</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Name</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Email</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Contact Number</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Company</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Status</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
                                    <?php foreach ($agents as $agent): ?>
                                        <tr onclick="editAgent(<?php echo $agent['agent_id']; ?>)" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors">
                                            <td class="px-6 py-4 text-slate-900 dark:text-white font-medium">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                        <?php
                                                        $name = $agent['username'];
                                                        $initials = strtoupper(substr($name, 0, 1));
                                                        echo htmlspecialchars($initials);
                                                        ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-slate-900 dark:text-white font-semibold"><?php echo htmlspecialchars($agent['agent_id']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-slate-900 dark:text-white font-semibold"><?php echo htmlspecialchars($agent['full_name']); ?></div>
                                                <div class="text-sm text-slate-500 dark:text-slate-400">@<?php echo htmlspecialchars($agent['username']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($agent['email']); ?></td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($agent['phone_number'] ?? '-'); ?></td>
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($agent['company_name'] ?? '-'); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $agent['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; ?>">
                                                    <?php echo ucfirst($agent['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                                    <button onclick="editAgent(<?php echo $agent['agent_id']; ?>)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                                        <span class="material-symbols-outlined text-base">edit</span>
                                                    </button>
                                                    <button onclick="resetPassword(<?php echo $agent['agent_id']; ?>, '<?php echo htmlspecialchars(addslashes($agent['username'])); ?>')" class="p-2 text-gray-500 hover:text-orange-600 hover:bg-gray-100 rounded-lg transition-colors" title="Reset Password">
                                                        <span class="material-symbols-outlined text-base">key</span>
                                                    </button>
                                                    <button onclick="deleteAgent(<?php echo $agent['agent_id']; ?>, '<?php echo htmlspecialchars(addslashes($agent['username'])); ?>')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors" title="Delete">
                                                        <span class="material-symbols-outlined text-base">delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($agents)): ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="material-symbols-outlined text-4xl text-gray-300">person</span>
                                                    <span>No agents found. <?php echo $search ? 'Try a different search.' : 'Create your first agent to get started.'; ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
<!-- Pagination Footer -->
<div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-4">
    <div class="text-sm text-slate-700 dark:text-slate-300">
        Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_agents); ?></span> of <span class="font-medium"><?php echo $total_agents; ?></span> results
    </div>
    <div class="flex items-center gap-2">
        <?php
        // Build query string for pagination
        $query_params = [];
        if (!empty($search)) $query_params['search'] = $search;
        if (!empty($status_filter)) $query_params['status'] = $status_filter;
        if (!empty($company_filter)) $query_params['company'] = $company_filter;
        if (!empty($created_from)) $query_params['created_from'] = $created_from;
        if (!empty($created_to)) $query_params['created_to'] = $created_to;
        $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
        ?>
        
        <a href="?page=agents&p=<?php echo max(1, $page - 1); ?><?php echo $query_string; ?>" 
           class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
            <span class="hidden sm:inline ml-1">Previous</span>
        </a>

        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-primary/10 border border-primary/20 rounded-lg">
            <?php echo $page; ?>
        </span>

        <a href="?page=agents&p=<?php echo min($total_pages, $page + 1); ?><?php echo $query_string; ?>" 
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
    <div id="agentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-8 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white" id="modalTitle">Create Agent</h3>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="agentForm" method="POST" class="p-8 space-y-6">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="agentId" value="">
                    <input type="hidden" name="last_login" id="lastLogin" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                             <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Agent Information</h4>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Username*</label>
                            <input type="text" name="username" id="username" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Full Name*</label>
                            <input type="text" name="full_name" id="fullName" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email*</label>
                            <input type="email" name="email" id="email" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                            <input type="tel" name="phone_number" id="phoneNumber"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                        
                        <div class="md:col-span-2">
                             <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Company Information</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Company Name</label>
                            <select name="company_name" id="companyName"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="">Select Company</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['company_name']); ?>">
                                        <?php echo htmlspecialchars($company['company_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Company Code</label>
                            <select name="customer_code" id="customerCode"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="">Select Code</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['company_code']); ?>">
                                        <?php echo htmlspecialchars($company['company_code']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                             <h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Account Information</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status*</label>
                            <select name="status" id="status" required
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <?php if (isset($edit_agent) && $edit_agent): ?>
                        <div class="md:col-span-2">
                            <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg">
                                <h5 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">System Information</h5>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-slate-500 dark:text-slate-400">Created By:</span>
                                        <span class="ml-2 text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($edit_agent['created_by_name'] ?? 'System'); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 dark:text-slate-400">Created At:</span>
                                        <span class="ml-2 text-slate-700 dark:text-slate-300"><?php echo date('Y-m-d H:i', strtotime($edit_agent['created_at'])); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 dark:text-slate-400">Last Login:</span>
                                        <span class="ml-2 text-slate-700 dark:text-slate-300"><?php echo $edit_agent['last_login'] ? date('Y-m-d H:i', strtotime($edit_agent['last_login'])) : 'Never'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" onclick="closeModal()" class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-primary/90 transition-colors">Save Agent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function showCreateModal() {
        document.getElementById('modalTitle').textContent = 'Create Agent';
        document.getElementById('formAction').value = 'create';
        document.getElementById('agentId').value = '';
        document.getElementById('lastLogin').value = '';
        document.getElementById('agentForm').reset();
        document.getElementById('status').value = 'active';
        document.getElementById('agentModal').classList.remove('hidden');
    }

    function editAgent(id) {
        document.getElementById('modalTitle').textContent = 'Edit Agent';
        document.getElementById('formAction').value = 'update';
        document.getElementById('agentId').value = id;

        // Load agent data (reload page with query param for simple fetching)
        window.location.href = '?page=agents&edit=' + id;
    }

    function deleteAgent(id, name) {
        if (confirm(`Are you sure you want to delete agent "${name}"?`)) {
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

    function resetPassword(id, name) {
        if (confirm(`Reset password for agent "${name}" to default (password123)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal() {
        document.getElementById('agentModal').classList.add('hidden');
        // Clear edit parameter from URL
        if (window.location.search.includes('edit=')) {
            window.location.href = window.location.pathname + '?page=agents';
        }
    }
    
    function exportAgents() {
        const agents = <?php echo json_encode($agents); ?>;
        if (agents.length === 0) {
            alert('No agents to export');
            return;
        }

        const headers = ['Agent ID', 'Username', 'Full Name', 'Email', 'Phone', 'Company', 'Company Code', 'Status', 'Created At', 'Last Login'];
        const csvContent = [
            headers.join(','),
            ...agents.map(a => [
                a.agent_id,
                a.username,
                a.full_name,
                a.email,
                a.phone_number,
                a.company_name,
                a.customer_code,
                a.status,
                a.created_at,
                a.last_login
            ].map(field => `"${(field || '').toString().replace(/"/g, '""')}"`).join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'agents_export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Date and Time
    document.addEventListener('DOMContentLoaded', function() {
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
            profileBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    profileDropdown.classList.add('hidden');
                }
            });
        }

        // Sync company name and code dropdowns
        const companyNameSelect = document.getElementById('companyName');
        const customerCodeSelect = document.getElementById('customerCode');
        
        if (companyNameSelect && customerCodeSelect) {
            const companyOptions = <?php echo json_encode($companies); ?>;
            const companyMap = {};
            companyOptions.forEach(company => {
                companyMap[company.company_name] = company.company_code;
                companyMap[company.company_code] = company.company_name;
            });

            companyNameSelect.addEventListener('change', function() {
                const companyName = this.value;
                if (companyName && companyMap[companyName]) {
                    customerCodeSelect.value = companyMap[companyName];
                } else {
                    customerCodeSelect.value = '';
                }
            });

            customerCodeSelect.addEventListener('change', function() {
                const companyCode = this.value;
                if (companyCode && companyMap[companyCode]) {
                    companyNameSelect.value = companyMap[companyCode];
                } else {
                    companyNameSelect.value = '';
                }
            });
        }
    });

    <?php if ($edit_agent): ?>
    // Populate form with agent data
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalTitle').textContent = 'Edit Agent';
        document.getElementById('formAction').value = 'update';
        document.getElementById('agentId').value = '<?php echo $edit_agent['agent_id']; ?>';
        document.getElementById('username').value = '<?php echo addslashes($edit_agent['username']); ?>';
        document.getElementById('fullName').value = '<?php echo addslashes($edit_agent['full_name']); ?>';
        document.getElementById('email').value = '<?php echo addslashes($edit_agent['email']); ?>';
        document.getElementById('phoneNumber').value = '<?php echo addslashes($edit_agent['phone_number'] ?? ''); ?>';
        document.getElementById('companyName').value = '<?php echo addslashes($edit_agent['company_name'] ?? ''); ?>';
        document.getElementById('customerCode').value = '<?php echo addslashes($edit_agent['customer_code'] ?? ''); ?>';
        document.getElementById('status').value = '<?php echo $edit_agent['status'] ?? 'active'; ?>';
        document.getElementById('lastLogin').value = '<?php echo $edit_agent['last_login'] ?? ''; ?>';
        document.getElementById('agentModal').classList.remove('hidden');
    });
    <?php endif; ?>

    // Filter modal functionality
function showFilters() {
    document.getElementById('filterModal').classList.remove('hidden');
}

function closeFilterModal() {
    document.getElementById('filterModal').classList.add('hidden');
}

// Remove the old client-side search functionality and replace with this:
document.addEventListener('DOMContentLoaded', function() {
    // Only keep the form submission behavior
    const searchInput = document.getElementById('agentSearch');
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
    
    // Close filter modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !filterModal.classList.contains('hidden')) {
            closeFilterModal();
        }
    });
});
    </script>
</body>
</html>