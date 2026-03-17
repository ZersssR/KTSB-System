<?php
require_once __DIR__ . '/../../Utils/CheckAuth.php';
require_once __DIR__ . '/../../../config/app.php';
$conn = getDBConnection();
$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);

// Check if user has permission to access this page
if ($currentUser['user_type'] !== 'user' && $currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle all POST requests before any HTML output
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = (int)($_GET['id'] ?? 0); // Get user ID from URL

    if ($action === 'reset_password_manual') {
        // Check if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!$isAjax) {
            // If not AJAX, redirect back to the page
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

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
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ? AND role != 'company'");
                $stmt->execute([$hashedPassword, $id]);

                if ($stmt->rowCount() > 0) {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);
                        exit;
                    } else {
                        $message = 'Password reset successfully!';
                        $messageType = 'success';
                    }
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'errors' => ['User not found or cannot modify company admins.']]);
                        exit;
                    } else {
                        $message = 'User not found or cannot modify company admins.';
                        $messageType = 'error';
                    }
                }
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
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
            require_once __DIR__ . '/../../../config/app.php';
            $conn = getDBConnection();

            $newStatus = ($action === 'deactivate') ? 0 : 1;
            $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE user_id = ? AND role != 'company'");
            $stmt->execute([$newStatus, $id]);

            if ($stmt->rowCount() > 0) {
                $message = 'Agent ' . ($action === 'deactivate' ? 'deactivated' : 'activated') . ' successfully!';
                $messageType = 'success';
            } else {
                $message = 'Agent not found or cannot modify company admins.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log("Agent action error: " . $e->getMessage());
            $message = 'Failed to update agent status.';
            $messageType = 'error';
        }
        // Redirect after POST
        header('Location: ' . $_SERVER['REQUEST_URI'] . ($message ? '&message=' . urlencode($message) . '&type=' . $messageType : ''));
        exit;
    } elseif ($action === 'reset_password') {
        // Generate a new random password
        $newPassword = bin2hex(random_bytes(8)); // 16 character random password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            require_once __DIR__ . '/../../../config/app.php';
            $conn = getDBConnection();

            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ? AND role != 'company'");
            $stmt->execute([$hashedPassword, $id]);

            if ($stmt->rowCount() > 0) {
                $message = 'Password reset successfully! New password: ' . $newPassword;
                $messageType = 'success';
            } else {
                $message = 'Agent not found or cannot modify company admins.';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = 'Failed to reset password.';
            $messageType = 'error';
        }
        // Redirect after POST
        header('Location: ' . $_SERVER['REQUEST_URI'] . ($message ? '&message=' . urlencode($message) . '&type=' . $messageType : ''));
        exit;
    } elseif ($action === 'edit_user') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $role = $_POST['role'] ?? '';
        $company = trim($_POST['company'] ?? '');

        // Validation
        $errors = [];
        if (empty($username)) {
            $errors[] = 'Username is required';
        }
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        if (empty($role) || !in_array($role, ['company', 'agent'])) {
            $errors[] = 'Invalid role selected';
        }

        if (empty($errors)) {
            try {
                require_once __DIR__ . '/../../../config/app.php';
                $conn = getDBConnection();

                // Check if username is already taken by another user
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
                $stmt->execute([$username, $id]);
                if ($stmt->rowCount() > 0) {
                    $errors[] = 'Username is already taken';
                } else {
                    // Check if email is already taken by another user
                    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
                    $stmt->execute([$email, $id]);
                    if ($stmt->rowCount() > 0) {
                        $errors[] = 'Email is already taken';
                    } else {
                        // Update user
                        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, company_name = ?, phone_number = ?, role = ?, company = ? WHERE user_id = ? AND role != 'company'");
                        $stmt->execute([$name, $username, $email, $companyName, $phoneNumber, $role, $company, $id]);

                        if ($stmt->rowCount() > 0) {
                            $message = 'Agent updated successfully!';
                            $messageType = 'success';
                        } else {
                            $message = 'Agent not found or cannot modify company admins.';
                            $messageType = 'error';
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Agent edit error: " . $e->getMessage());
                $message = 'Failed to update agent.';
                $messageType = 'error';
            }
        } else {
            $message = implode(', ', $errors);
            $messageType = 'error';
        }
        // Redirect after POST
        header('Location: ' . $_SERVER['REQUEST_URI'] . ($message ? '&message=' . urlencode($message) . '&type=' . $messageType : ''));
        exit;
    }
}

$id = (int)$_GET['id'];



try {
    // Get user details
                                    $stmt = $conn->prepare("SELECT user_id, username, email, role, company, name, company_name, phone_number, created_at, last_login, is_active FROM users WHERE user_id = ?");
                                    $stmt->execute([$id]);
                                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: user_management.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: user_management.php');
    exit;
}

// Function to get role display name
function getRoleDisplayName($role) {
    switch ($role) {
        case 'company':
            return 'Company Admin';
        case 'agent':
            return 'Agent';
        default:
            return 'Company Admin';
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>User Detail</title>
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
              "darkGrey": "#212121",
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

        /* Modal animations */
        .modal-fade-enter {
            opacity: 0;
            transform: scale(0.95);
        }
        .modal-fade-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: all 0.2s ease-out;
        }
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
<?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>
            <!-- Header Bar -->
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
                    <?php
                    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                        echo "<p>Invalid request.</p>";
                        exit;
                    }

                    $id = (int)$_GET['id'];

                    require_once __DIR__ . '/../../../config/app.php';
                    $conn = getDBConnection();

                    try {
                        // Get user details
                                    $stmt = $conn->prepare("SELECT user_id, username, email, role, company, name, company_name, phone_number, created_at, last_login, is_active FROM users WHERE user_id = ?");
                                    $stmt->execute([$id]);
                                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$user) {
                            echo "<p>User not found.</p>";
                            exit;
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading user details: " . htmlspecialchars($e->getMessage()) . "</p>";
                        exit;
                    }

                    // Handle user actions
                    $message = '';
                    $messageType = '';

                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
                        $action = $_POST['action'];

                        if ($action === 'deactivate' || $action === 'activate') {
                            try {
                                $newStatus = ($action === 'deactivate') ? 0 : 1;
                                $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE user_id = ? AND role != 'company'");
                                $stmt->execute([$newStatus, $id]);

                                if ($stmt->rowCount() > 0) {
                                    $message = 'User ' . ($action === 'deactivate' ? 'deactivated' : 'activated') . ' successfully!';
                                    $messageType = 'success';
                                    // Refresh user data
                                    $stmt = $conn->prepare("SELECT user_id, username, email, role, company, name, company_name, phone_number, created_at, last_login, is_active FROM users WHERE user_id = ?");
                                    $stmt->execute([$id]);
                                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                } else {
                                    $message = 'User not found or cannot modify company admins.';
                                    $messageType = 'error';
                                }
                            } catch (PDOException $e) {
                                error_log("User action error: " . $e->getMessage());
                                $message = 'Failed to update user status.';
                                $messageType = 'error';
                            }
                        } elseif ($action === 'reset_password_manual') {
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
                            if (strlen($newPassword) < 8) {
                                $errors[] = 'Password must be at least 8 characters long';
                            }

                            if (empty($errors)) {
                                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                                try {
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ? AND role != 'company'");
            $stmt->execute([$hashedPassword, $id]);

                                    if ($stmt->rowCount() > 0) {
                                        if ($isAjax) {
                                            header('Content-Type: application/json');
                                            echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);
                                            exit;
                                        } else {
                                            $message = 'Password reset successfully!';
                                            $messageType = 'success';
                                        }
                                    } else {
                                        if ($isAjax) {
                                            header('Content-Type: application/json');
                                            echo json_encode(['success' => false, 'errors' => ['User not found or cannot modify company admins.']]);
                                            exit;
                                        } else {
                                            $message = 'User not found or cannot modify company admins.';
                                            $messageType = 'error';
                                        }
                                    }
                                } catch (PDOException $e) {
                                    error_log("Password reset error: " . $e->getMessage());
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
                        } elseif ($action === 'reset_password') {
                            // Generate a new random password
                            $newPassword = bin2hex(random_bytes(8)); // 16 character random password
                            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                            try {
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ? AND role != 'company'");
            $stmt->execute([$hashedPassword, $id]);

                                if ($stmt->rowCount() > 0) {
                                    $message = 'Password reset successfully! New password: ' . $newPassword;
                                    $messageType = 'success';
                                } else {
                                    $message = 'Agent not found or cannot modify company admins.';
                                    $messageType = 'error';
                                }
                            } catch (PDOException $e) {
                                error_log("Password reset error: " . $e->getMessage());
                                $message = 'Failed to reset password.';
                                $messageType = 'error';
                            }
                        } elseif ($action === 'edit_user') {
                            $username = trim($_POST['username'] ?? '');
                            $email = trim($_POST['email'] ?? '');
                            $role = $_POST['role'] ?? '';
                            $company = trim($_POST['company'] ?? '');

                            // Validation
                            $errors = [];
                            if (empty($username)) {
                                $errors[] = 'Username is required';
                            }
                            if (empty($email)) {
                                $errors[] = 'Email is required';
                            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $errors[] = 'Invalid email format';
                            }
                            if (empty($role) || !in_array($role, ['company', 'agent'])) {
                                $errors[] = 'Invalid role selected';
                            }

                            if (empty($errors)) {
                                try {
                                    // Check if username is already taken by another user
                                    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
                                    $stmt->execute([$username, $id]);
                                    if ($stmt->rowCount() > 0) {
                                        $errors[] = 'Username is already taken';
                                    } else {
                                        // Check if email is already taken by another user
                                        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
                                        $stmt->execute([$email, $id]);
                                        if ($stmt->rowCount() > 0) {
                                            $errors[] = 'Email is already taken';
                                        } else {
                                            // Update user
                                            $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, company_name = ?, phone_number = ?, role = ?, company = ? WHERE user_id = ? AND role != 'company'");
                                            $stmt->execute([$name, $username, $email, $companyName, $phoneNumber, $role, $company, $id]);

                                            if ($stmt->rowCount() > 0) {
                                                $message = 'User updated successfully!';
                                                $messageType = 'success';
                                                // Refresh user data
                                                $stmt = $conn->prepare("SELECT user_id, username, email, role, company, name, company_name, phone_number, created_at, last_login, is_active FROM users WHERE user_id = ?");
                                                $stmt->execute([$id]);
                                                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                            } else {
                                                $message = 'User not found or cannot modify company admins.';
                                                $messageType = 'error';
                                            }
                                        }
                                    }
                                } catch (PDOException $e) {
                                    error_log("User edit error: " . $e->getMessage());
                                    $message = 'Failed to update user.';
                                    $messageType = 'error';
                                }
                            } else {
                                $message = implode(', ', $errors);
                                $messageType = 'error';
                            }
                    }
                }
                    ?>

                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="user_management.php" class="hover:text-primary">Agent Management</a>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200"><?php echo htmlspecialchars($user['username']); ?></span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">User Details</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Detailed information for <?php echo htmlspecialchars($user['username']); ?></p>
                    </div>

                    <!-- Message Display -->
                    <?php if ($message): ?>
                    <div class="mb-6 rounded-lg border p-4 <?php echo $messageType === 'success' ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300'; ?>">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined mr-2"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Details Card -->
                    <div class="w-full bg-white rounded-xl shadow-sm p-6 md:p-8">
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Section 1: User Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">User Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Full Name</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Username</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Email</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Company Name</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($user['company_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Phone Number</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Role</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars(getRoleDisplayName($user['role'])); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Company</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($user['company'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Status</span>
                                        <div class="col-span-2">
                                            <span class="status-pill status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Section 2: Account Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">Account Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Created</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($user['created_at']))); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Last Login</span>
                                        <span class="text-sm font-semibold text-gray-900 col-span-2"><?php echo $user['last_login'] ? htmlspecialchars(date('M j, Y H:i', strtotime($user['last_login']))) : 'Never'; ?></span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Action Buttons and Back Button -->
                    <div class="mt-6 flex justify-between items-center">
                        <!-- Back Button -->
                        <a href="user_management.php" class="inline-flex items-center gap-2 rounded-lg bg-darkGrey px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-darkGrey/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-darkGrey transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            Back to Agent Management
                        </a>

                        <!-- Action Buttons -->
                        <?php if ($user['role'] !== 'company'): ?>
                        <div class="flex gap-3">
                            <button onclick="showEditModal()" class="inline-flex items-center gap-2 rounded-lg bg-[#212180] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212180]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212180] transition-colors">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                Edit User
                            </button>
                            <button onclick="showResetPasswordModal()" class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors">
                                <span class="material-symbols-outlined text-sm">lock_reset</span>
                                Reset Password
                            </button>
                            <form method="POST" class="inline" onsubmit="return confirm('<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?> user <?php echo htmlspecialchars($user['username']); ?>?')">
                                <input type="hidden" name="action" value="<?php echo $user['is_active'] ? 'deactivate' : 'activate'; ?>"/>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg <?php echo $user['is_active'] ? 'bg-[#D10000] hover:bg-[#D10000]/90 focus-visible:outline-[#D10000]' : 'bg-[#008F1D] hover:bg-[#008F1D]/90 focus-visible:outline-[#008F1D]'; ?> px-4 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-colors">
                                    <span class="material-symbols-outlined text-sm"><?php echo $user['is_active'] ? 'block' : 'check_circle'; ?></span>
                                    <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
// Get page name for unique localStorage keys
const page = window.location.pathname.split('/').pop().split('.')[0] || 'index';

// Persist toggle states globally across all pages
const toggles = ['other-services-toggle', 'history-toggle', 'agent-toggle'];

function loadToggleStates() {
    toggles.forEach(id => {
        const state = localStorage.getItem(id);
        if (state === 'true') {
            document.getElementById(id).checked = true;
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
        updateToggle(); // initial
    }
}

setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

function updateTime() {
    const date = new Date();
    document.getElementById('date').innerText = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}).toUpperCase();
    document.getElementById('time').innerText = date.toLocaleTimeString('en-US', {hour12: false});
}
setInterval(updateTime, 1000);
updateTime(); // initial

// Combined DOMContentLoaded event listener for all functionality
document.addEventListener('DOMContentLoaded', function() {
    // Save and restore sidebar scroll position
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const scrollTop = localStorage.getItem('sidebarScrollTop');
        if (scrollTop) {
            sidebar.scrollTop = parseInt(scrollTop);
        }
    }

    // Modal functionality
    // Edit user modal functions
    function showEditModal() {
        document.getElementById('edit-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    window.showEditModal = showEditModal;

    function hideEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.hideEditModal = hideEditModal;

    // Reset password modal functions
    function showResetPasswordModal() {
        console.log('showResetPasswordModal called');
        const modal = document.getElementById('reset-password-modal');
        console.log('Modal element:', modal);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('modal-fade-enter-active');
            document.body.style.overflow = 'hidden';
            // Clear form and errors
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
            hideResetPasswordErrors();
            // Disable button on load
            document.getElementById('resetButton').disabled = true;
            console.log('Modal should now be visible');
        } else {
            console.error('Modal element not found');
        }
    }

    function hideResetPasswordModal() {
        document.getElementById('reset-password-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset modal content
        document.getElementById('mainContent').classList.remove('hidden');
        document.getElementById('successMessage').classList.add('hidden');
    }

    function showResetPasswordErrors(errors) {
        const errorDiv = document.getElementById('reset-password-errors');
        const errorText = document.getElementById('reset-password-error-text');
        errorText.textContent = errors.join(', ');
        errorDiv.classList.remove('hidden');
    }

    function hideResetPasswordErrors() {
        const errorDiv = document.getElementById('reset-password-errors');
        errorDiv.classList.add('hidden');
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    function validatePasswordForm() {
        const pass1 = document.getElementById('new-password').value;
        const pass2 = document.getElementById('confirm-password').value;
        const resetButton = document.getElementById('resetButton');
        const errorDiv = document.getElementById('reset-password-errors');
        const confirmInput = document.getElementById('confirm-password');

        if (pass1 === '' || pass2 === '') {
            // Don't show error if fields are empty, just disable button
            resetButton.disabled = true;
            errorDiv.classList.add('hidden');
            // Ensure red border is removed if field is emptied
            if (pass2 === '') {
                confirmInput.classList.remove('border-red-500', 'focus:ring-red-500');
                confirmInput.classList.add('border-gray-300', 'focus:ring-blue-500');
            }
        } else if (pass1 === pass2) {
            // Passwords match
            resetButton.disabled = false;
            errorDiv.classList.add('hidden');
            confirmInput.classList.remove('border-red-500', 'focus:ring-red-500');
            confirmInput.classList.add('border-gray-300', 'focus:ring-blue-500');
        } else {
            // Passwords do not match
            resetButton.disabled = true;
            errorDiv.classList.remove('hidden');
            confirmInput.classList.add('border-red-500');
            confirmInput.classList.remove('border-gray-300');
        }
    }

    window.showResetPasswordModal = showResetPasswordModal;
    window.hideResetPasswordModal = hideResetPasswordModal;
    window.togglePasswordVisibility = togglePasswordVisibility;
    window.validatePasswordForm = validatePasswordForm;

    // Event listeners
    // Password validation
    document.getElementById('new-password').addEventListener('input', validatePasswordForm);
    document.getElementById('confirm-password').addEventListener('input', validatePasswordForm);

    // Form submission
    const form = document.getElementById('reset-password-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text().then(text => {
                    console.log('Response text:', text);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    // Success - show success message
                    document.getElementById('mainContent').classList.add('hidden');
                    document.getElementById('successMessage').classList.remove('hidden');
                } else {
                    // Show errors in modal
                    showResetPasswordErrors(data.errors);
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                showResetPasswordErrors(['An error occurred. Please try again. Error: ' + error.message]);
            });
        });
    }

    // Modal event listeners
    const editModal = document.getElementById('edit-modal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                hideEditModal();
            }
        });
    }

    // Close buttons
    document.getElementById('closeButton').addEventListener('click', hideResetPasswordModal);
    document.getElementById('cancelButton').addEventListener('click', hideResetPasswordModal);
    document.getElementById('doneButton').addEventListener('click', function() {
        hideResetPasswordModal();
        location.reload(); // Refresh to update the page
    });
});

// Save sidebar scroll position on page unload
window.addEventListener('beforeunload', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
    }
});
</script>

<!-- Edit User Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit User</h3>
            <button onclick="hideEditModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="edit_user"/>

            <div>
                <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                <input type="text" id="edit-name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100"/>
            </div>

            <div>
                <label for="edit-username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                <input type="text" id="edit-username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100"/>
            </div>

            <div>
                <label for="edit-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" id="edit-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100"/>
            </div>

            <div>
                <label for="edit-company-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name</label>
                <input type="text" id="edit-company-name" name="company_name" value="<?php echo htmlspecialchars($user['company_name'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100"/>
            </div>

            <div>
                <label for="edit-phone-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                <input type="tel" id="edit-phone-number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100"/>
            </div>

            <div>
                <label for="edit-role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                <select id="edit-role" name="role" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100">

                    <option value="agent" <?php echo $user['role'] === 'agent' ? 'selected' : ''; ?>>Agent</option>
                    <option value="company" <?php echo $user['role'] === 'company' ? 'selected' : ''; ?>>Company Admin</option>
                </select>
            </div>

            <div>
                <label for="edit-company" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                <input type="text" id="edit-company" name="company" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-gray-100"/>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="hideEditModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-[#212180] rounded-lg hover:bg-[#212180]/90 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="reset-password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Main Content -->
        <div id="mainContent">
            <!-- Header -->
            <div class="flex justify-between items-center p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Reset Password</h2>
                <button id="closeButton" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <form id="reset-password-form" class="space-y-5">
                    <input type="hidden" name="action" value="reset_password_manual"/>

                    <!-- Icon and Username Header -->
                    <div class="flex flex-col items-center justify-center space-y-4">
                        <!-- Box-shaped container for icon and username -->
                        <div class="mx-auto flex items-center justify-center h-12 px-5 rounded-lg bg-blue-100 space-x-2.5 border border-blue-200">
                            <svg class="h-6 w-6 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="text-blue-700 font-semibold text-lg"><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                    </div>

                    <p class="text-sm text-center text-gray-600">
                        Please enter and confirm the new password.
                    </p>

                    <!-- New Password Input -->
                    <div>
                        <label for="new-password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <input id="new-password" name="new_password" type="password" required class="w-full p-3 pr-10 border border-blue-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"/>
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700" onclick="togglePasswordVisibility('new-password', 'new-pass-icon')">
                                <i id="new-pass-icon" class="material-symbols-outlined text-base">visibility</i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm New Password Input -->
                    <div>
                        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input id="confirm-password" name="confirm_password" type="password" required class="w-full p-3 pr-10 border border-blue-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"/>
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700" onclick="togglePasswordVisibility('confirm-password', 'confirm-pass-icon')">
                                <i id="confirm-pass-icon" class="material-symbols-outlined text-base">visibility</i>
                            </button>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div id="reset-password-errors" class="hidden text-sm text-center text-red-600 font-medium">
                        <div id="reset-password-error-text"></div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button id="cancelButton" type="button" onclick="hideResetPasswordModal()" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-100 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button id="resetButton" type="submit" class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-sm">lock_reset</span>
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>


        </div>

        <!-- Success Message -->
        <div id="successMessage" class="hidden p-6">
            <div class="flex flex-col items-center justify-center text-center space-y-4 my-8">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Password Reset</h3>
                <p class="text-sm text-gray-600">The password for <strong><?php echo htmlspecialchars($user['username']); ?></strong> has been successfully updated.</p>
                <button id="doneButton" class="w-full px-5 py-2.5 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all shadow-sm">
                    Done
                </button>
            </div>
        </div>


</div>
</body>
</html>
