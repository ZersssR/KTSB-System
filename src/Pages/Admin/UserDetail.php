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

$userId = $_GET['id'] ?? '';

if (empty($userId)) {
    header('Location: user_management.php');
    exit;
}

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'reset_password_manual') {
        // Check if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Validation
        $errors = [];
        if (empty($newPassword)) {
            $errors[] = 'New password is required';
        }
        if (empty($confirmPassword)) {
            $errors[] = 'Confirm password is required';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            try {
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$hashedPassword, $userId]);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);
                    exit;
                } else {
                    $message = 'Password reset successfully!';
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'errors' => ['Failed to reset password.']]);
                    exit;
                } else {
                    $message = 'Failed to reset password.';
                    $messageType = 'error';
                }
            }
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            } else {
                $message = implode(', ', $errors);
                $messageType = 'error';
            }
        }
    } elseif ($action === 'deactivate' || $action === 'activate') {
        try {
            $newStatus = ($action === 'deactivate') ? 'inactive' : 'active';
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$newStatus, $userId]);

            $message = 'User ' . ($action === 'deactivate' ? 'deactivated' : 'activated') . ' successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Failed to update user status.';
            $messageType = 'error';
        }
        } elseif ($action === 'edit_user') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $companyCode = trim($_POST['company_code'] ?? '');
            $phoneNumber = trim($_POST['phone_number'] ?? '');

            // Validation
            $errors = [];
            if (empty($username))
                $errors[] = 'Username is required';
            if (empty($email))
                $errors[] = 'Email is required';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errors[] = 'Invalid email format';
            if (empty($companyCode))
                $errors[] = 'Company is required';

            if (empty($errors)) {
                try {
                    // Check uniqueness
                    $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
                    $stmt->execute([$username, $email, $userId]);
                    if ($stmt->rowCount() > 0) {
                        $message = 'Username or Email already exists.';
                        $messageType = 'error';
                    } else {
                        // Get company name from companies table using company_code
                        $stmt = $conn->prepare("SELECT company_name FROM companies WHERE company_code = ?");
                        $stmt->execute([$companyCode]);
                        $company = $stmt->fetch(PDO::FETCH_ASSOC);
                        $companyName = $company ? $company['company_name'] : null;

                        if (!$companyName) {
                            $message = 'Invalid Company selected.';
                            $messageType = 'error';
                        } else {
                            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, company_name = ?, customer_code = ?, phone_number = ? WHERE user_id = ?");
                            $stmt->execute([$username, $email, $companyName, $companyCode, $phoneNumber, $userId]);
                            $message = 'User updated successfully!';
                            $messageType = 'success';
                            
                            // Refresh user data
                            $stmt = $conn->prepare("SELECT u.*, a.username as created_by_name FROM users u LEFT JOIN admins a ON u.created_by = a.id WHERE u.user_id = ?");
                            $stmt->execute([$userId]);
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                } catch (PDOException $e) {
                    $message = 'Failed to update user.';
                    $messageType = 'error';
                }
            } else {
                $message = implode(', ', $errors);
                $messageType = 'error';
            }
        }
}

// Fetch User Details
try {
    $stmt = $conn->prepare("SELECT u.*, a.username as created_by_name FROM users u LEFT JOIN admins a ON u.created_by = a.id WHERE u.user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: user_management.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: user_management.php');
    exit;
}

// Fetch Companies for Dropdown
$companies = [];
try {
    $stmt = $conn->query("SELECT company_code, company_name FROM companies ORDER BY company_name ASC");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching companies for user edit: " . $e->getMessage());
}

$currentPage = 'user_management.php';
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>User Details - Admin</title>
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
                        "darkGrey": "#212121",
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

        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-inactive {
            background-color: #FEE2E2;
            color: #991B1B;
        }
    </style>
    <script src="../tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
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
            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="user_management.php" class="hover:text-primary">User Management</a>
                            <span class="mx-2">/</span>
                            <span
                                class="text-[#212529] dark:text-gray-200"><?php echo htmlspecialchars($user['username']); ?></span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            User Details</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Detailed
                            information for <?php echo htmlspecialchars($user['username']); ?></p>
                    </div>

                    <!-- Message Display -->
                    <?php if ($message): ?>
                        <div
                            class="mb-6 rounded-lg border p-4 <?php echo $messageType === 'success' ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                            <div class="flex items-center">
                                <span
                                    class="material-symbols-outlined mr-2"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Details Card -->
                    <div
                        class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 md:p-8 border border-gray-200 dark:border-gray-700">
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Section 1: User Information -->
                            <section>
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 border-b dark:border-gray-700 pb-2">
                                    User Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Username</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Email</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Company</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2">
                                            <?php echo htmlspecialchars($user['company_name']); ?>
                                            <?php if (!empty($user['customer_code'])): ?>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                                    Code: <?php echo htmlspecialchars($user['customer_code']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Phone
                                            Number</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2"><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Status</span>
                                        <div class="col-span-2">
                                            <span
                                                class="status-pill status-<?php echo $user['status'] === 'active' ? 'active' : 'inactive'; ?>"><?php echo ucfirst($user['status']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Section 2: Account Information -->
                            <section>
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 border-b dark:border-gray-700 pb-2">
                                    Account Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Created
                                            By</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2"><?php echo htmlspecialchars($user['created_by_name'] ?? 'System'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Created
                                            At</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2"><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($user['created_at']))); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 col-span-1">Last
                                            Login</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100 col-span-2"><?php echo $user['last_login'] ? htmlspecialchars(date('M j, Y H:i', strtotime($user['last_login']))) : 'Never'; ?></span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Action Buttons and Back Button -->
                    <div class="mt-6 flex justify-between items-center">
                        <!-- Back Button -->
                        <a href="user_management.php"
                            class="inline-flex items-center gap-2 rounded-lg bg-darkGrey px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-darkGrey/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-darkGrey transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            Back to User Management
                        </a>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button onclick="showEditModal()"
                                class="inline-flex items-center gap-2 rounded-lg bg-[#212180] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212180] transition-colors">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                Edit User
                            </button>
                            <button onclick="showResetPasswordModal()"
                                class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors">
                                <span class="material-symbols-outlined text-sm">lock_reset</span>
                                Reset Password
                            </button>
                            <form method="POST" class="inline"
                                onsubmit="return confirm('<?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> user <?php echo htmlspecialchars($user['username']); ?>?')">
                                <input type="hidden" name="action"
                                    value="<?php echo $user['status'] === 'active' ? 'deactivate' : 'activate'; ?>" />
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg <?php echo $user['status'] === 'active' ? 'bg-[#D10000] hover:bg-[#D10000]/90 focus-visible:outline-[#D10000]' : 'bg-[#008F1D] hover:bg-[#008F1D]/90 focus-visible:outline-[#008F1D]'; ?> px-4 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors">
                                    <span
                                        class="material-symbols-outlined text-sm"><?php echo $user['status'] === 'active' ? 'block' : 'check_circle'; ?></span>
                                    <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<!-- Edit User Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="hideEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_user">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                id="modal-title">Edit User Details</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="username"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                                    <input type="text" name="username" id="username"
                                        value="<?php echo htmlspecialchars($user['username']); ?>" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="email"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input type="email" name="email" id="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="company_code"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <select name="company_code" id="company_code" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Company</option>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?php echo htmlspecialchars($company['company_code']); ?>"
                                                <?php echo ($user['customer_code'] ?? '') === $company['company_code'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($company['company_code'] . ' - ' . $company['company_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="phone_number"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone
                                        Number</label>
                                    <input type="text" name="phone_number" id="phone_number"
                                        value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">
                        Save Changes
                    </button>
                    <button type="button" onclick="hideEditModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="hideResetPasswordModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="resetPasswordForm" onsubmit="handleResetPassword(event)">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="material-symbols-outlined text-orange-600">lock_reset</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                    id="modal-title">Reset Password</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Enter a new password for
                                        this user.</p>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="new_password"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">New
                                                Password</label>
                                            <input type="password" name="new_password" id="new_password" required
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2 dark:bg-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label for="confirm_password"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm
                                                Password</label>
                                            <input type="password" name="confirm_password" id="confirm_password"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm border p-2 dark:bg-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                    <div id="passwordError" class="mt-2 text-sm text-red-600 hidden"></div>
                                    <div id="passwordSuccess" class="mt-2 text-sm text-green-600 hidden"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Reset Password
                        </button>
                        <button type="button" onclick="hideResetPasswordModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
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
        });

        // Modal functions
        function showEditModal() {
            document.getElementById('editModal').classList.remove('hidden');
        }

        function hideEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function showResetPasswordModal() {
            document.getElementById('resetPasswordModal').classList.remove('hidden');
            document.getElementById('passwordError').classList.add('hidden');
            document.getElementById('passwordSuccess').classList.add('hidden');
            document.getElementById('resetPasswordForm').reset();
        }

        function hideResetPasswordModal() {
            document.getElementById('resetPasswordModal').classList.add('hidden');
        }

        function handleResetPassword(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'reset_password_manual');

            const errorDiv = document.getElementById('passwordError');
            const successDiv = document.getElementById('passwordSuccess');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successDiv.textContent = data.message;
                        successDiv.classList.remove('hidden');
                        form.reset();
                        setTimeout(() => {
                            hideResetPasswordModal();
                        }, 2000);
                    } else {
                        errorDiv.textContent = data.errors.join(', ');
                        errorDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorDiv.textContent = 'An error occurred. Please try again.';
                    errorDiv.classList.remove('hidden');
                });
        }
    </script>
</body>

</html>