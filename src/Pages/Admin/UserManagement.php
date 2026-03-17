<?php
require_once __DIR__ . '/../../../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username, id FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$user_id = $admin['id'];
$currentPage = 'user_management.php';

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

// Handle Form Submission (Create User)
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Handle Status Update
        if ($_POST['action'] === 'toggle_status') {
            $userId = $_POST['user_id'];
            $newStatus = $_POST['new_status'];

            try {
                $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
                $stmt->execute([$newStatus, $userId]);
                logActivity($user_id, 'update', "User status updated to " . ucfirst($newStatus), $userId);
                $message = "User status updated to " . ucfirst($newStatus);
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = "Error updating status: " . $e->getMessage();
                $message_type = 'error';
            }
        }
        // Handle Create User
        elseif ($_POST['action'] === 'create_user') {
            $username = trim($_POST['username']);
            $fullName = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $companyCode = trim($_POST['company_code']);
            $phoneNumber = trim($_POST['phone_number']);
            
            // Handle level access checkboxes
            $isEndorser = isset($_POST['is_endorser']) ? 1 : 0;
            $isRequisitioner = isset($_POST['is_requisitioner']) ? 1 : 0;

            // Validation
            if (empty($username) || empty($fullName) || empty($email) || empty($password) || empty($companyCode)) {
                $message = "All fields are required.";
                $message_type = 'error';
            } else {
                try {
                    // Check if username or email exists
                    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    if ($stmt->fetch()) {
                        $message = "Username or Email already exists.";
                        $message_type = 'error';
                    } else {
                        // Get company name from companies table using company_code
                        $stmt = $conn->prepare("SELECT company_name FROM companies WHERE company_code = ?");
                        $stmt->execute([$companyCode]);
                        $company = $stmt->fetch(PDO::FETCH_ASSOC);
                        $companyName = $company ? $company['company_name'] : null;

                        if (!$companyName) {
                            $message = "Invalid Company selected.";
                            $message_type = 'error';
                        } else {
                            // Get the next user ID (running number) with format USR001, USR002, etc.
                            $stmt = $conn->query("SELECT MAX(CAST(SUBSTRING(user_code, 4) AS UNSIGNED)) as max_id FROM users WHERE user_code LIKE 'USR%'");
                            $result = $stmt->fetch();
                            $next_id = ($result['max_id'] ?? 0) + 1;
                            $user_code = 'USR' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
                            
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            // Insert with user_code and new fields
                            $stmt = $conn->prepare("INSERT INTO users (user_code, username, full_name, email, password_hash, company_name, customer_code, phone_number, is_endorser, is_requisitioner, created_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                            $stmt->execute([$user_code, $username, $fullName, $email, $passwordHash, $companyName, $companyCode, $phoneNumber, $isEndorser, $isRequisitioner, $admin['id']]);
                            
                            logActivity($user_id, 'create', 'Created new user: ' . $username . ' with ID: ' . $user_code, $conn->lastInsertId());
                            $message = "User created successfully with ID: " . $user_code . "!";
                            $message_type = 'success';
                        }
                    }
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }

        // Handle Update User - ADD THIS NEW SECTION
        elseif ($_POST['action'] === 'update_user') {
            $userId = intval($_POST['user_id']);
            $username = trim($_POST['username']);
            $fullName = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $companyCode = trim($_POST['company_code']);
            $phoneNumber = trim($_POST['phone_number']);
            $password = $_POST['password']; // Optional for update
            
            // Handle level access checkboxes
            $isEndorser = isset($_POST['is_endorser']) ? 1 : 0;
            $isRequisitioner = isset($_POST['is_requisitioner']) ? 1 : 0;

            // Validation
            if (empty($username) || empty($fullName) || empty($email) || empty($companyCode)) {
                $message = "Username, Full Name, Email, and Company are required.";
                $message_type = 'error';
            } else {
                try {
                    // Check if username or email exists for OTHER users (excluding current user)
                    $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
                    $stmt->execute([$username, $email, $userId]);
                    if ($stmt->fetch()) {
                        $message = "Username or Email already exists for another user.";
                        $message_type = 'error';
                    } else {
                        // Get company name from companies table using company_code
                        $stmt = $conn->prepare("SELECT company_name FROM companies WHERE company_code = ?");
                        $stmt->execute([$companyCode]);
                        $company = $stmt->fetch(PDO::FETCH_ASSOC);
                        $companyName = $company ? $company['company_name'] : null;

                        if (!$companyName) {
                            $message = "Invalid Company selected.";
                            $message_type = 'error';
                        } else {
                            // Build update query based on whether password is provided
                            if (!empty($password)) {
                                // Update with new password
                                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, password_hash = ?, company_name = ?, customer_code = ?, phone_number = ?, is_endorser = ?, is_requisitioner = ? WHERE user_id = ?");
                                $stmt->execute([$username, $fullName, $email, $passwordHash, $companyName, $companyCode, $phoneNumber, $isEndorser, $isRequisitioner, $userId]);
                            } else {
                                // Update without changing password
                                $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, company_name = ?, customer_code = ?, phone_number = ?, is_endorser = ?, is_requisitioner = ? WHERE user_id = ?");
                                $stmt->execute([$username, $fullName, $email, $companyName, $companyCode, $phoneNumber, $isEndorser, $isRequisitioner, $userId]);
                            }
                            
                            logActivity($user_id, 'update', 'Updated user: ' . $username, $userId);
                            $message = "User updated successfully!";
                            $message_type = 'success';
                            
                            // Redirect to remove edit parameter from URL
                            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
                            exit();
                        }
                    }
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
        // Handle Delete User
        elseif ($_POST['action'] === 'delete_user') {
            $userId = intval($_POST['user_id']);
            
            try {
                // Get user details for logging
                $stmt = $conn->prepare("SELECT username, user_code FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    
                    logActivity($user_id, 'delete', 'Deleted user: ' . $user['username'] . ' with ID: ' . $user['user_code'], $userId);
                    $message = 'User deleted successfully!';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = "Error deleting user: " . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Pagination
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Handle search and filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ? OR user_code LIKE ? OR company_name LIKE ?)";
    $search_term = "%{$search}%";
    $params = array_merge($params, array_fill(0, 5, $search_term));
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($role_filter)) {
    if ($role_filter === 'endorser') {
        $where_conditions[] = "is_endorser = 1";
    } elseif ($role_filter === 'requisitioner') {
        $where_conditions[] = "is_requisitioner = 1";
    } elseif ($role_filter === 'both') {
        $where_conditions[] = "is_endorser = 1 AND is_requisitioner = 1";
    }
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count with filters
$count_sql = "SELECT COUNT(*) FROM users $where_sql";
$stmt = $conn->prepare($count_sql);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users for current page with filters
$sql = "SELECT u.*, a.username as created_by_name 
        FROM users u 
        LEFT JOIN admins a ON u.created_by = a.id 
        $where_sql 
        ORDER BY u.created_at DESC 
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);

// Bind search parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(($key + 1), $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Fetch Companies for Dropdown from companies table
$companies = [];
try {
    $stmt = $conn->query("SELECT company_code, company_name FROM companies ORDER BY company_name ASC");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching companies for user management: " . $e->getMessage());
}

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>User Management - Admin</title>
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
                                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">User Management</h1>
                                <p class="text-slate-600 dark:text-slate-400">Create and manage user accounts.</p>
                            </div>
                            <button onclick="showCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 inline-flex items-center gap-2">
                                <span class="material-symbols-outlined">person_add</span>
                                Create User
                            </button>
                        </div>

                        <!-- Message Display -->
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
                                        <input type="text" name="search" id="userSearch" 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                               placeholder="Search by name, ID, email, or company..."
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500">
                                    </div>
                                    
                                    <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                        <a href="?page=user_management" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
                                <button onclick="exportUsers()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
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
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                                            <select name="status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                                                <option value="">All Status</option>
                                                <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Role</label>
                                            <select name="role" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg">
                                                <option value="">All Roles</option>
                                                <option value="endorser" <?php echo (isset($_GET['role']) && $_GET['role'] == 'endorser') ? 'selected' : ''; ?>>Endorsers Only</option>
                                                <option value="requisitioner" <?php echo (isset($_GET['role']) && $_GET['role'] == 'requisitioner') ? 'selected' : ''; ?>>Requisitioners Only</option>
                                                <option value="both" <?php echo (isset($_GET['role']) && $_GET['role'] == 'both') ? 'selected' : ''; ?>>Both Roles</option>
                                            </select>
                                        </div>
                                        <div class="flex justify-end gap-3 pt-4">
                                            <button type="button" onclick="closeFilterModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-sm">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Apply Filters</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <table class="w-full text-left table-auto">
                                <thead class="bg-slate-50 dark:bg-slate-700">
                                    <tr class="border-b border-slate-200 dark:border-slate-600">
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white w-16">No.</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">User ID</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">User</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Full Name</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Company</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Level Access</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white">Status</th>
                                        <th class="px-6 py-4 font-semibold text-slate-900 dark:text-white text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
                                    <?php 
                                    $counter = $offset + 1;
                                    foreach ($users as $user): 
                                    ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-sm font-mono">
                                                <?php echo str_pad($counter, 2, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="px-6 py-4 text-slate-900 dark:text-white font-medium">
                                                <?php echo htmlspecialchars($user['user_code'] ?? 'USR' . str_pad($user['user_id'], 3, '0', STR_PAD_LEFT)); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-slate-900 dark:text-white font-semibold">
                                                            <?php echo htmlspecialchars($user['username']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                                            <?php echo htmlspecialchars($user['email']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-900 dark:text-white">
                                                <?php echo htmlspecialchars($user['full_name'] ?? '-'); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-slate-900 dark:text-white">
                                                    <?php echo htmlspecialchars($user['company_name']); ?>
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    <?php echo htmlspecialchars($user['phone_number'] ?? '-'); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col gap-1">
                                                    <?php if ($user['is_endorser'] ?? 0): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                            <span class="material-symbols-outlined text-xs mr-1">verified</span>
                                                            Endorser
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($user['is_requisitioner'] ?? 0): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                            <span class="material-symbols-outlined text-xs mr-1">assignment</span>
                                                            Requisitioner
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!($user['is_endorser'] ?? 0) && !($user['is_requisitioner'] ?? 0)): ?>
                                                        <span class="text-xs text-slate-400">No access</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo ($user['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; ?>">
                                                    <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button onclick="editUser(<?php echo $user['user_id']; ?>)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                                        <span class="material-symbols-outlined text-base">edit</span>
                                                    </button>
                                                    <button onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-colors" title="Delete">
                                                        <span class="material-symbols-outlined text-base">delete</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        $counter++;
                                        endforeach; 
                                    ?>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="material-symbols-outlined text-4xl text-gray-300">people</span>
                                                    <span>No users found. Create your first user to get started.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Pagination Footer -->
                            <div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-4">
                                <div class="text-sm text-slate-700 dark:text-slate-300">
                                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $limit, $total_users); ?></span> of <span class="font-medium"><?php echo $total_users; ?></span> results
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php
                                    // Build query string for pagination
                                    $query_params = [];
                                    if (!empty($search)) $query_params['search'] = $search;
                                    if (!empty($status_filter)) $query_params['status'] = $status_filter;
                                    if (!empty($role_filter)) $query_params['role'] = $role_filter;
                                    $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
                                    ?>
                                    
                                    <a href="?page=user_management&p=<?php echo max(1, $page - 1); ?><?php echo $query_string; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
                                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                                        <span class="hidden sm:inline ml-1">Previous</span>
                                    </a>

                                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-primary/10 border border-primary/20 rounded-lg">
                                        <?php echo $page; ?>
                                    </span>

                                    <a href="?page=user_management&p=<?php echo min($total_pages, $page + 1); ?><?php echo $query_string; ?>" 
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

    <!-- Create/Edit User Modal -->
    <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-8 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white" id="modalTitle">Create User</h3>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="userForm" method="POST" class="p-8 space-y-8">
                    <input type="hidden" name="action" id="formAction" value="create_user">
                    <input type="hidden" name="user_id" id="recordId" value="">

                    <!-- User Information Section -->
                    <div class="space-y-4">
                        <h4 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3">User Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Username *</label>
                                <input type="text" name="username" id="userUsername" required
                                       class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                       placeholder="e.g., johndoe">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Full Name *</label>
                                <input type="text" name="full_name" id="userFullName" required
                                       class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                       placeholder="e.g., John Doe">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email *</label>
                                <input type="email" name="email" id="userEmail" required
                                       class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                       placeholder="e.g., john@example.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" id="passwordLabel">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" id="userPassword" required
                                    class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                    placeholder="Enter password">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Company *</label>
                                <select name="company_code" id="userCompany" required
                                        class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="">Select Company</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?php echo htmlspecialchars($company['company_code']); ?>">
                                            <?php echo htmlspecialchars($company['company_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Phone Number</label>
                                <input type="tel" name="phone_number" id="userPhone"
                                       class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary shadow-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                       placeholder="e.g., +6012-345-6789">
                            </div>
                        </div>
                    </div>

                    <!-- Level Access Section -->
                    <div class="space-y-4">
                        <h4 class="text-xl font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-3">Level Access</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <label class="flex items-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                                    <input type="checkbox" name="is_endorser" id="isEndorser" value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300 flex items-center">
                                        <span class="material-symbols-outlined text-base mr-1">verified</span>
                                        Endorser
                                    </span>
                                </label>
                                <label class="flex items-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                                    <input type="checkbox" name="is_requisitioner" id="isRequisitioner" value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-3 text-sm text-slate-700 dark:text-slate-300 flex items-center">
                                        <span class="material-symbols-outlined text-base mr-1">assignment</span>
                                        Requisitioner
                                    </span>
                                </label>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                <p class="mb-2">You can select one or both roles:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Endorser - Can endorse requests</li>
                                    <li>Requisitioner - Can create requests</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-8 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" onclick="closeModal()" class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-primary/90 transition-colors">Save User</button>
                    </div>
                </form>
            </div>
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

            // Close filter modal on outside click
            document.getElementById('filterModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeFilterModal();
                }
            });

            // Search on Enter key
            const searchInput = document.getElementById('userSearch');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.form.submit();
                    }
                });
            }
        });

        // Modal functions
        function showCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create User';
            document.getElementById('formAction').value = 'create_user';
            document.getElementById('recordId').value = '';
            document.getElementById('userForm').reset();
            
            // Uncheck checkboxes
            document.getElementById('isEndorser').checked = false;
            document.getElementById('isRequisitioner').checked = false;
            
            document.getElementById('userModal').classList.remove('hidden');
        }

        function editUser(id) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'update_user';
            document.getElementById('recordId').value = id;

            // Load user data via AJAX or redirect
            window.location.href = '?page=user_management&edit=' + id;
        }

        function deleteUser(id, username) {
            if (confirm(`Are you sure you want to delete user "${username}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        // Filter modal functions
        function showFilters() {
            document.getElementById('filterModal').classList.remove('hidden');
        }

        function closeFilterModal() {
            document.getElementById('filterModal').classList.add('hidden');
        }

        // Export functionality
        function exportUsers() {
            const users = <?php echo json_encode($users); ?>;
            if (users.length === 0) {
                alert('No users to export');
                return;
            }

            // Create CSV content
            const headers = ['User ID', 'Username', 'Full Name', 'Email', 'Company', 'Phone', 'Endorser', 'Requisitioner', 'Status', 'Created At'];
            const csvContent = [
                headers.join(','),
                ...users.map(user => [
                    user.user_code || 'USR' + String(user.user_id).padStart(3, '0'),
                    user.username,
                    user.full_name || '',
                    user.email,
                    user.company_name,
                    user.phone_number || '',
                    user.is_endorser ? 'Yes' : 'No',
                    user.is_requisitioner ? 'Yes' : 'No',
                    user.status || 'active',
                    user.created_at
                ].map(field => `"${field.toString().replace(/"/g, '""')}"`).join(','))
            ].join('\n');

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'users_export_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

            <?php if ($edit_user): ?>
            // Populate form with user data for editing
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('formAction').value = 'update_user';
                document.getElementById('recordId').value = '<?php echo $edit_user['user_id']; ?>';
                
                document.getElementById('userUsername').value = '<?php echo addslashes($edit_user['username']); ?>';
                document.getElementById('userFullName').value = '<?php echo addslashes($edit_user['full_name'] ?? ''); ?>';
                document.getElementById('userEmail').value = '<?php echo addslashes($edit_user['email']); ?>';
                document.getElementById('userCompany').value = '<?php echo addslashes($edit_user['customer_code'] ?? ''); ?>';
                document.getElementById('userPhone').value = '<?php echo addslashes($edit_user['phone_number'] ?? ''); ?>';
                
                document.getElementById('isEndorser').checked = <?php echo ($edit_user['is_endorser'] ?? 0) ? 'true' : 'false'; ?>;
                document.getElementById('isRequisitioner').checked = <?php echo ($edit_user['is_requisitioner'] ?? 0) ? 'true' : 'false'; ?>;
                
                // Change password field label and make it optional for edit
                const passwordLabel = document.querySelector('label[for="userPassword"]');
                if (passwordLabel) {
                    passwordLabel.innerHTML = 'Password <span class="text-gray-500 text-xs">(leave blank to keep current)</span>';
                }
                
                // Remove required attribute from password field for edit
                document.getElementById('userPassword').removeAttribute('required');
                document.getElementById('userPassword').placeholder = 'Enter new password (optional)';
                
                document.getElementById('userModal').classList.remove('hidden');
            });
            <?php endif; ?>
    </script>
</body>

</html>