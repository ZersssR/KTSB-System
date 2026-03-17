<?php
ob_start(); // Start output buffering
session_start();

require_once __DIR__ . '/../../config/database.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user'; // Default role for new registrations

    // Basic validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password) || empty($confirm_password)) {
        $error_message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } else {
        $auth_pdo = getAuthDB();

        if ($auth_pdo) {
            try {
                // Check if username or email already exists
                $stmt = $auth_pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $error_message = 'Username or email already exists.';
                } else {
                    // Hash the password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user into the database
                    $stmt = $auth_pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $password_hash, $full_name, $role]);

                    $success_message = 'Registration successful! You can now log in.';
                    // Optionally redirect to login page after successful registration
                    header('Location: login.php?registered=true');
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Registration query error: " . $e->getMessage()); // More specific error logging
                $error_message = 'A database error occurred during registration. Please check server logs.';
            }
        } else {
            $error_message = 'Authentication database connection failed. Please check `config/db_config.php` and ensure the `ktsb_auth` database exists and is accessible.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Register - KTSB Port Authority Management System</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
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
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #F8F9FA; /* Light mode background */
        }
        .dark body {
            background-color: #111921; /* Dark mode background */
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-800 dark:text-slate-200">
    <div class="w-full max-w-md p-8 space-y-6 bg-white dark:bg-slate-900/50 rounded-lg shadow-lg border border-slate-200 dark:border-slate-800">
        <div class="flex flex-col items-center">
            <div class="flex size-16 items-center justify-center rounded-full bg-primary/20 text-primary mb-4">
                <span class="material-symbols-outlined text-4xl">person_add</span>
            </div>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Register for KTSB Admin</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2">Create a new account to access the system</p>
        </div>

        <?php if ($error_message): ?>
            <div class="bg-accent-red/10 border border-accent-red text-accent-red px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="bg-green-500/10 border border-green-500 text-green-500 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <form class="space-y-6" action="register.php" method="POST">
            <div>
                <label for="full_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Full Name</label>
                <div class="mt-1">
                    <input id="full_name" name="full_name" type="text" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Username</label>
                <div class="mt-1">
                    <input id="username" name="username" type="text" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email Address</label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm Password</label>
                <div class="mt-1">
                    <input id="confirm_password" name="confirm_password" type="password" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Register
                </button>
            </div>
        </form>

        <div class="text-center text-sm text-slate-600 dark:text-slate-400">
            Already have an account? <a href="login.php" class="font-medium text-primary hover:text-primary/80 dark:text-blue-400 dark:hover:text-blue-300">Log in here</a>
        </div>
    </div>
</body>
</html>
<?php ob_end_flush(); // End output buffering and send all output to the browser ?>
