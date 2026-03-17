<?php
require_once __DIR__ . '/../../../config/app.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$username_value = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Debug: Check if data received
    error_log("Login attempt - Username: $username");
    
    // Authenticate against admin table
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin) {
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Invalid username';
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
        error_log("Login error: " . $e->getMessage());
    }
    
    $username_value = htmlspecialchars($username);
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Admin Login</title>
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
        
        /* Custom styles for form inputs */
        .form-input-container {
            position: relative;
            border: 2px solid #9CA3AF;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: border-color 0.3s;
            background: white;
        }
        
        .form-input-container:focus-within {
            border-color: #212180;
        }
        
        .form-input-container.dark {
            background: rgba(17, 25, 33, 0.5);
            border-color: #4B5563;
        }
        
        .form-input {
            width: 100%;
            height: 56px;
            background: transparent;
            outline: none;
            border: none;
            padding: 20px 12px 4px 12px;
            font-weight: bold;
            color: #111827;
        }
        
        .form-input.dark {
            color: #F9FAFB;
        }
        
        .form-label {
            position: absolute;
            pointer-events: none;
            transition: all 0.3s;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #6B7280;
            font-size: 14px;
        }
        
        .form-label.float {
            top: 8px;
            left: 12px;
            font-size: 10px;
            color: #212180;
            font-weight: bold;
            transform: none;
        }
        
        .form-icon {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            color: #6B7280;
            cursor: pointer;
        }
    </style>
    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
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
        
        // Floating label effect
        function initFloatingLabels() {
            const inputs = document.querySelectorAll('.form-input');
            
            inputs.forEach(input => {
                const container = input.closest('.form-input-container');
                const label = container.querySelector('.form-label');
                
                // Check if input has value on load
                if (input.value.trim() !== '') {
                    label.classList.add('float');
                }
                
                input.addEventListener('focus', () => {
                    label.classList.add('float');
                    container.style.borderColor = '#212180';
                });
                
                input.addEventListener('blur', () => {
                    if (input.value.trim() === '') {
                        label.classList.remove('float');
                    }
                    container.style.borderColor = input.type === 'password' ? '#9CA3AF' : '#9CA3AF';
                });
            });
        }
        
        // Generate or retrieve tab ID
        function generateTabId() {
            let tabId = sessionStorage.getItem('ktsb_tab_id');
            if (!tabId) {
                tabId = 'tab_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('ktsb_tab_id', tabId);
            }
            return tabId;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize floating labels
            initFloatingLabels();
            
            // Set tab ID
            const tabId = generateTabId();
            const tabInput = document.getElementById('tab_id');
            if (tabInput) {
                tabInput.value = tabId;
            }
            
            // Set cookie for persistence
            document.cookie = 'ktsb_tab_id=' + encodeURIComponent(tabId) + '; path=/; max-age=86400';
        });
    </script>
</head>

<body class="font-display h-screen w-full flex items-center justify-center p-4 relative bg-[url('../assets/images/KTSB%20Background.jpg')] bg-cover bg-center bg-no-repeat bg-fixed">
    <!-- Dark Overlay for subtle contrast -->
    <div class="absolute inset-0 bg-black/10"></div>

    <!-- Card Container -->
    <div class="relative w-full max-w-[1400px] h-[85vh] grid grid-cols-1 lg:grid-cols-5 gap-4">
        <!-- Left Column: Welcome & Announcements -->
        <div class="hidden lg:flex flex-col h-full relative lg:col-span-3">
            <div class="relative w-full h-full flex flex-col px-6 xl:px-8 z-10">
                <div class="w-full h-full flex flex-col gap-4">
                    <!-- Welcome Title Card -->
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-5 w-full shadow-2xl flex-shrink-0">
                        <div class="text-left text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            <h2 class="text-2xl xl:text-3xl font-bold leading-tight mb-2">Admin Portal</h2>
                            <h1 class="text-2xl xl:text-3xl font-bold leading-tight whitespace-nowrap">
                                Kuala Terengganu Support Base
                            </h1>
                        </div>
                    </div>
                    
                    <!-- Announcements (Simplified) -->
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 w-full flex flex-col shadow-2xl overflow-hidden h-full">
                        <div class="flex items-center justify-between mb-6 flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                    <span class="material-symbols-outlined text-white">campaign</span>
                                </div>
                                <h2 class="text-lg font-semibold text-white tracking-wide drop-shadow-sm">Announcements</h2>
                            </div>
                        </div>
                        
                        <div class="space-y-4 overflow-y-auto">
                            <?php
                            // You can add dynamic announcements here
                            $announcements = [
                                ['title' => 'System Maintenance', 'content' => 'Regular maintenance scheduled for Sunday', 'time' => '2 hours ago'],
                                ['title' => 'Security Update', 'content' => 'Please update your passwords regularly', 'time' => '1 day ago'],
                            ];
                            
                            foreach ($announcements as $announcement):
                            ?>
                            <div class="bg-white/85 backdrop-blur-sm rounded-xl p-4 shadow-sm border-l-4 border-l-orange-500">
                                <h3 class="font-bold text-slate-800 text-base mb-2"><?php echo $announcement['title']; ?></h3>
                                <p class="text-slate-600 text-sm mb-2"><?php echo $announcement['content']; ?></p>
                                <div class="flex items-center text-xs text-slate-500">
                                    <span class="material-symbols-outlined text-xs mr-1">schedule</span>
                                    <?php echo $announcement['time']; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="flex flex-col justify-center bg-white dark:bg-gray-900 h-full overflow-y-auto rounded-2xl shadow-2xl overflow-hidden lg:col-span-2">
            <div class="w-full max-w-md mx-auto p-6 space-y-8">
                <div class="flex items-center justify-center gap-4 mb-2">
                    <img src="../assets/images/KSB Logo.JPG" alt="KSB Logo" class="h-24 w-auto object-contain">
                    <div class="text-left">
                        <h2 class="text-3xl font-bold text-[#212529] dark:text-gray-100 leading-tight">Admin Login</h2>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sign in to manage the system</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p class="text-red-600 dark:text-red-400 text-sm"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-8 space-y-6">
                    <!-- Hidden field for tab ID -->
                    <input type="hidden" name="tab_id" id="tab_id" value="">
                    
                    <div class="space-y-6">
                        <!-- Username Input -->
                        <div class="form-input-container dark:border-gray-600">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input dark:text-gray-100" 
                                value="<?php echo $username_value; ?>"
                                required
                                autocomplete="username"
                            >
                            <label for="username" class="form-label dark:text-gray-400">Username</label>
                            <div class="form-icon">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                        </div>
                        
                        <!-- Password Input -->
                        <div class="form-input-container dark:border-gray-600">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input dark:text-gray-100" 
                                required
                                autocomplete="current-password"
                            >
                            <label for="password" class="form-label dark:text-gray-400">Password</label>
                            <div class="form-icon" onclick="togglePassword('password', 'password-icon')">
                                <span id="password-icon" class="material-symbols-outlined cursor-pointer">visibility</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full h-14 flex items-center justify-center rounded-lg bg-primary px-6 text-sm font-semibold text-white shadow-sm hover:bg-opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>