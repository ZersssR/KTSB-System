<?php
// Login page with PHP authentication
require_once __DIR__ . '/../../Utils/Auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle login form submission
$error_message = '';
$redirect_url = '';
$username_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $tabId = trim($_POST['tab_id'] ?? '');
    $remember_me = isset($_POST['remember-me']);

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // First authenticate with current session
        if (authenticate($username, $password)) {
            // Check if we need to switch to a tab-specific session AFTER successful auth
            if (!empty($tabId) && preg_match('/^[a-zA-Z0-9_-]+$/', $tabId)) {
                $desiredSessionName = 'PHPSESSID_' . $tabId;
                if (session_name() !== $desiredSessionName) {
                    // Store current session data before switching
                    $userData = $_SESSION;

                    // Close current session
                    session_write_close();

                    // Start new session with tab-specific name
                    session_name($desiredSessionName);
                    session_start();

                    // Restore user session data to the new tab session
                    $_SESSION = $userData;
                }
            }

            // Handle Remember Me
            if ($remember_me) {
                $_SESSION['remember_me'] = true;
                // Set cookie to last for 30 days
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + (30 * 24 * 60 * 60), $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            } else {
                $_SESSION['remember_me'] = false;
                // Ensure cookie is session-only (expires when browser closes)
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), 0, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }

            // Set redirect URL for JavaScript redirect
            $user = getCurrentUser();
            if ($user['role'] === 'agent') {
                $redirect_url = base_url('dashboard.php') . '?loggedin=1';
            } else {
                $redirect_url = base_url('index.php') . '?loggedin=1';
            }
        } else {
            // Check if user exists in users table
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $userExists = $stmt->fetch();

            if (!$userExists) {
                // Check if user exists in agents table
                $stmt = $conn->prepare("SELECT agent_id FROM agents WHERE username = ? AND status = 'active'");
                $stmt->execute([$username]);
                $userExists = $stmt->fetch();
            }

            if ($userExists) {
                // User exists but authentication failed - wrong password
                $error_message = 'Invalid password.';
            } else {
                $error_message = 'Invalid username or password.';
            }
        }
    }
    
    // Store username for repopulating form
    $username_value = htmlspecialchars($username);
}
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link rel="icon" href="assets/images/KTSB Logo.jpeg" type="image/png"/>
    <title>Login - Kuala Terengganu Support Base</title>
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
        
        /* Custom Soft Shadows - Enhanced Visibility */
        .shadow-soft {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3) !important;
        }
        .shadow-soft-sm {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Widget animation */
        .widget-animate {
            opacity: 0;
            transform: translateY(60px);
            transition: opacity 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
                        transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .widget-visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="font-display h-screen w-full flex items-center justify-center p-4 relative bg-[url('assets/images/KTSB%20Background.jpg')] bg-cover bg-center bg-no-repeat bg-fixed">
<!-- Dark Overlay for subtle contrast -->
<div class="absolute inset-0 bg-black/10"></div>

<!-- Card Container -->
<div class="relative w-full max-w-[1400px] h-[85vh] grid grid-cols-1 lg:grid-cols-5 gap-4">

    <!-- Left Column: Login Form -->
    <div class="flex flex-col justify-center bg-white dark:bg-gray-900 h-full overflow-y-auto rounded-2xl shadow-soft overflow-hidden lg:col-span-2">
        <div class="w-full max-w-md mx-auto p-6 space-y-8">
            <div class="flex items-center justify-center gap-4 mb-2">
                <img src="assets/images/KSB Logo.JPG" alt="KSB Logo" class="h-24 w-auto object-contain">
                <div class="text-left">
                    <h2 class="text-3xl font-bold text-[#212529] dark:text-gray-100 leading-tight">Kuala Terengganu<br>Support Base</h2>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">Sign in to your account</p>
            </div>

            <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-red-600 dark:text-red-400 text-sm"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="mt-8 space-y-6">
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

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="form-checkbox h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"/>
                        <label for="remember-me" class="ml-2 block text-sm text-[#212529] dark:text-gray-200">Remember me for 30 days</label>
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

    <!-- Right Column: Welcome & Announcements -->
    <div class="hidden lg:flex flex-col h-full relative lg:col-span-3">
        <div class="relative w-full h-full flex flex-col px-6 xl:px-8 z-10">
            <div class="w-full h-full flex flex-col gap-4">
                <!-- Welcome Title Card -->
                <div class="p-5 w-full flex-shrink-0">
                    <div class="text-left text-white" style="text-shadow: 0 4px 16px rgba(0,0,0,0.6);">
                        <h2 class="text-3xl lg:text-4xl font-bold leading-tight mb-2">Welcome to</h2>
                        <h1 class="text-4xl lg:text-5xl font-bold leading-tight whitespace-nowrap tracking-tight">
                            Kuala Terengganu Support Base
                        </h1>
                    </div>
                </div>
                
                <!-- Announcements Widget -->
                <div id="announcement-root" class="widget-animate w-full flex-1 h-full min-h-0"></div>
            </div>
        </div>
    </div>

</div>

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
                container.style.borderColor = '#9CA3AF';
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
    
    // Widget animation
    function animateWidgets() {
        const widgets = document.querySelectorAll('.widget-animate');
        widgets.forEach((widget, index) => {
            setTimeout(() => {
                widget.classList.add('widget-visible');
                // Cleanup after transition (800ms)
                setTimeout(() => {
                    widget.classList.remove('widget-animate', 'widget-visible');
                }, 800);
            }, index * 100);
        });
    }
    
    // Load announcements dynamically
    function loadAnnouncements() {
        const announcementRoot = document.getElementById('announcement-root');
        if (!announcementRoot) return;
        
        // Sample announcements data
        const announcements = [
            { 
                id: 1, 
                title: 'Scheduled Maintenance', 
                content: 'Servers will be down for upgrades this Sunday from 2AM to 4AM UTC. Please save your work beforehand.',
                time: '2 hours ago',
            },
            { 
                id: 2, 
                title: 'New Dashboard Features', 
                content: 'Check out the new dark mode and customizable widgets in your settings! You can now drag and drop items.',
                time: '1 day ago',
            },
            { 
                id: 3, 
                title: 'Q3 Town Hall Meeting', 
                content: 'Join us for the quarterly all-hands meeting. We will be discussing Q4 goals and new benefit packages.',
                time: '2 days ago',
            }
        ];
        
        // Create announcements HTML
        announcementRoot.innerHTML = `
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 w-full flex flex-col shadow-soft overflow-hidden h-full">
                <div class="flex items-center justify-between mb-6 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                            <span class="material-symbols-outlined text-white">campaign</span>
                        </div>
                        <h2 class="text-lg font-semibold text-white tracking-wide drop-shadow-sm">User Announcements</h2>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 bg-white/20 text-white rounded-full backdrop-blur-sm border border-white/10">
                        Page 1 of ${Math.ceil(announcements.length / 2)}
                    </span>
                </div>
                
                <div class="flex-grow overflow-hidden">
                    <div class="space-y-4">
                        ${announcements.map(announcement => `
                            <div class="bg-white backdrop-blur-sm rounded-xl p-4 shadow-soft-sm border-l-4 border-l-orange-500">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-slate-800 text-base leading-tight">${announcement.title}</h3>
                                </div>
                                <p class="text-slate-600 text-sm mb-2">${announcement.content}</p>
                                <div class="flex items-center text-xs text-slate-500 font-medium">
                                    <span class="material-symbols-outlined text-xs mr-1">schedule</span>
                                    ${announcement.time}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Main initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form elements
        initFloatingLabels();
        
        // Generate tab ID
        const tabId = generateTabId();
        document.getElementById('tab_id').value = tabId;
        
        // Set cookie for persistence
        document.cookie = 'ktsb_tab_id=' + encodeURIComponent(tabId) + '; path=/; max-age=86400';
        
        // Load announcements
        loadAnnouncements();
        
        // Animate widgets
        animateWidgets();
        
        // Check for redirect URL
        const redirectUrl = <?php echo json_encode($redirect_url); ?>;
        if (redirectUrl) {
            // Append tab_id to redirect URL if it exists
            if (tabId && !redirectUrl.includes('tab_id=')) {
                const separator = redirectUrl.includes('?') ? '&' : '?';
                window.location.href = redirectUrl + separator + 'tab_id=' + encodeURIComponent(tabId);
            } else {
                window.location.href = redirectUrl;
            }
        }
    });
</script>
</body>
</html>