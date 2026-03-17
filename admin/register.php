<?php
require_once __DIR__ . '/../config/app.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);

    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if username or email already exists
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $error = 'Username or email already exists';
        } else {
            // Create new admin
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, email) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $password_hash, $email])) {
                $message = 'Admin account created successfully! You can now log in.';
            } else {
                $error = 'Failed to create admin account';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Admin Registration</title>
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
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }
    </style>
</head>

<body
    class="font-display h-screen w-full flex items-center justify-center p-4 relative bg-[url('../assets/images/KTSB%20Background.jpg')] bg-cover bg-center bg-no-repeat bg-fixed">
    <!-- Dark Overlay for subtle contrast -->
    <div class="absolute inset-0 bg-black/10"></div>

    <!-- Card Container -->
    <div class="relative w-full max-w-[1400px] h-[85vh] grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Left Column: Welcome & Announcements (Info Left) -->
        <div class="hidden lg:flex flex-col h-full relative lg:col-span-3">
            <div class="relative w-full h-full flex flex-col px-6 xl:px-8 z-10">
                <div class="w-full h-full flex flex-col gap-4">
                    <!-- Welcome Title Card -->
                    <div
                        class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-5 w-full shadow-2xl flex-shrink-0">
                        <div class="text-left text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            <h2 class="text-2xl xl:text-3xl font-bold leading-tight mb-2">Admin Registration</h2>
                            <h1 class="text-2xl xl:text-3xl font-bold leading-tight whitespace-nowrap">
                                Kuala Terengganu Support Base
                            </h1>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 w-full shadow-2xl flex-1">
                        <h3 class="text-lg font-semibold text-white mb-4">Registration Information</h3>
                        <div class="text-white/90 space-y-3">
                            <p>• Create a new admin account for the system</p>
                            <p>• All fields are required</p>
                            <p>• Password must be at least 6 characters</p>
                            <p>• Use a valid email address</p>
                            <p>• After registration, you can log in using the admin login page</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Registration Form -->
        <div
            class="flex flex-col justify-center bg-white dark:bg-gray-900 h-full overflow-y-auto rounded-2xl shadow-2xl overflow-hidden lg:col-span-2">
            <div class="w-full max-w-md mx-auto p-6 space-y-8">
                <div class="flex items-center justify-center gap-4 mb-2">
                    <img src="../assets/images/KSB Logo.JPG" alt="KSB Logo" class="h-24 w-auto object-contain">
                    <div class="text-left">
                        <h2 class="text-3xl font-bold text-[#212529] dark:text-gray-100 leading-tight">Register Admin</h2>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Create a new admin account</p>
                </div>

                <?php if ($message): ?>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <p class="text-green-600 dark:text-green-400 text-sm"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p class="text-red-600 dark:text-red-400 text-sm"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-8 space-y-6">
                    <div class="space-y-6">
                        <!-- Username -->
                        <div class="relative border-2 rounded-xl overflow-hidden transition-colors duration-300 bg-white dark:bg-gray-900/50 border-gray-400 dark:border-gray-600">
                            <input
                                name="username"
                                type="text"
                                required
                                class="w-full h-14 bg-transparent outline-none border-none px-4 text-gray-900 dark:text-gray-100 rounded-md font-bold"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            />
                            <label class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-600 dark:text-gray-400 text-sm pointer-events-none transition-all duration-300">
                                Username
                            </label>
                        </div>

                        <!-- Email -->
                        <div class="relative border-2 rounded-xl overflow-hidden transition-colors duration-300 bg-white dark:bg-gray-900/50 border-gray-400 dark:border-gray-600">
                            <input
                                name="email"
                                type="email"
                                required
                                class="w-full h-14 bg-transparent outline-none border-none px-4 text-gray-900 dark:text-gray-100 rounded-md font-bold"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            />
                            <label class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-600 dark:text-gray-400 text-sm pointer-events-none transition-all duration-300">
                                Email Address
                            </label>
                        </div>

                        <!-- Password -->
                        <div class="relative border-2 rounded-xl overflow-hidden transition-colors duration-300 bg-white dark:bg-gray-900/50 border-gray-400 dark:border-gray-600">
                            <input
                                name="password"
                                type="password"
                                required
                                class="w-full h-14 bg-transparent outline-none border-none px-4 text-gray-900 dark:text-gray-100 rounded-md font-bold"
                            />
                            <label class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-600 dark:text-gray-400 text-sm pointer-events-none transition-all duration-300">
                                Password
                            </label>
                        </div>

                        <!-- Confirm Password -->
                        <div class="relative border-2 rounded-xl overflow-hidden transition-colors duration-300 bg-white dark:bg-gray-900/50 border-gray-400 dark:border-gray-600">
                            <input
                                name="confirm_password"
                                type="password"
                                required
                                class="w-full h-14 bg-transparent outline-none border-none px-4 text-gray-900 dark:text-gray-100 rounded-md font-bold"
                            />
                            <label class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-600 dark:text-gray-400 text-sm pointer-events-none transition-all duration-300">
                                Confirm Password
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full h-14 flex items-center justify-center rounded-lg bg-primary px-6 text-sm font-semibold text-white shadow-sm hover:bg-opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                            Create Admin Account
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="login.php" class="text-sm text-primary hover:text-primary/80">
                            Already have an account? Login here
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</body>

</html>
