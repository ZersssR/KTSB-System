<?php
require_once __DIR__ . '/../config/app.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if admin exists
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $error = 'Admin account not found';
        } else {
            // Update password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
            if ($stmt->execute([$password_hash, $username])) {
                $message = 'Password updated successfully! You can now log in with the new password.';
            } else {
                $error = 'Failed to update password';
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
    <title>Reset Admin Password</title>
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
</head>

<body
    class="font-display h-screen w-full flex items-center justify-center p-4 relative bg-[url('../assets/images/KTSB%20Background.jpg')] bg-cover bg-center bg-no-repeat bg-fixed">
    <!-- Dark Overlay for subtle contrast -->
    <div class="absolute inset-0 bg-black/10"></div>

    <!-- Card Container -->
    <div class="relative w-full max-w-md mx-auto">

        <!-- Reset Password Form -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden">
            <div class="w-full max-w-md mx-auto p-6 space-y-8">
                <div class="flex items-center justify-center gap-4 mb-2">
                    <img src="../assets/images/KSB Logo.JPG" alt="KSB Logo" class="h-16 w-auto object-contain">
                    <div class="text-left">
                        <h2 class="text-2xl font-bold text-[#212529] dark:text-gray-100 leading-tight">Reset Password</h2>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Reset admin account password</p>
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
                                value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>"
                            />
                            <label class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-600 dark:text-gray-400 text-sm pointer-events-none transition-all duration-300">
                                Username
                            </label>
                        </div>

                        <!-- New Password -->
                        <div class="relative border-2 rounded-xl overflow-hidden transition-colors duration-300 bg-white dark:bg-gray-900/50 border-gray-400 dark:border-gray-600">
                            <input
                                name="new_password"
                                type="password"
                                required
                                class="w-full h-14 bg-transparent outline-none border-none px-4 text-gray-900 dark:text-gray-100 rounded-md font-bold"
                            />
                            <label class="absolute top-1/2 -translate-y-1/2 left-4 text-gray-600 dark:text-gray-400 text-sm pointer-events-none transition-all duration-300">
                                New Password
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
                            Reset Password
                        </button>
                    </div>

                    <div class="text-center space-y-2">
                        <a href="login.php" class="text-sm text-primary hover:text-primary/80 block">
                            Back to Login
                        </a>
                        <a href="register.php" class="text-sm text-primary hover:text-primary/80 block">
                            Create New Admin
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</body>

</html>
