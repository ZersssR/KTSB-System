<?php
// Protected create agent page - requires authentication and user role
require_once __DIR__ . '/../../Utils/CheckAuth.php';

$currentUser = getCurrentUser();

// Check if user has permission to access this page
if ($currentUser['user_type'] !== 'user') {
    header('Location: index.php');
    exit;
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    // Company is now derived from the user's company
    $company = $currentUser['company'];

    // Validation
    if (empty($name) || empty($email) || empty($username) || empty($companyName) || empty($phoneNumber)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif (!preg_match('/^[0-9-\s\+]+$/', $phoneNumber)) {
        $message = 'Please enter a valid phone number.';
        $messageType = 'error';
    } else {
        // Create agent
        require_once __DIR__ . '/../../../config/app.php';
        $conn = getDBConnection();

        try {
            // Check if email or username already exists in agents or users table
            $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? UNION SELECT username FROM agents WHERE username = ?");
            $stmt->execute([$username, $username]);
            
            if ($stmt->fetch()) {
                $message = 'Username already exists.';
                $messageType = 'error';
            } else {
                $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM agents WHERE email = ?");
                $stmt->execute([$email, $email]);

                if ($stmt->fetch()) {
                    $message = 'Email already exists.';
                    $messageType = 'error';
                } else {
                    // Hash a default password
                    $defaultPassword = 'password123'; 
                    $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

                    // Insert new agent
                    $stmt = $conn->prepare("INSERT INTO agents (username, email, password_hash, full_name, company_name, customer_code, phone_number, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?)");
                    $stmt->execute([$username, $email, $passwordHash, $name, $companyName, $currentUser['customer_code'] ?? null, $phoneNumber, $currentUser['id']]);

                    $successData = [
                        'username' => $username,
                        'password' => $defaultPassword
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("Agent creation error: " . $e->getMessage());
            $message = 'Failed to create agent. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Create New Agent</title>
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

        /* ========================================= */
        /* CSS ANIMATION: User (Blue) -> Check (Green) */
        /* ========================================= */

        /* 1. Base Transitions */
        #agent-success-icon, #agent-user-icon { 
            transition: all 0.3s ease; 
        }
        #agent-success-icon path { 
            stroke-dasharray: 100; 
            stroke-dashoffset: 0; 
            transition: stroke-dashoffset 0s; 
        }

        /* 2. Wrapper Animation (Deep Blue #212180 -> Green) */
        .agent-mode-wrapper {
            position: relative; 
            z-index: 10;
            animation: bgMorph 1.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes bgMorph {
            0% { transform: scale(0); background-color: #212180; }
            20% { transform: scale(1); background-color: #212180; }
            60% { transform: scale(1); background-color: #212180; } /* Hold Blue */
            70% { transform: scale(0.8); background-color: #212180; } /* Shrink */
            85% { transform: scale(1.1); background-color: #16a34a; /* Pop Green */ }
            100% { transform: scale(1); background-color: #16a34a; }
        }

        /* 3. Pulse Ring (Blue -> Green) */
        .agent-mode-wrapper::before {
            content: ''; position: absolute; inset: -4px; border-radius: 50%; z-index: -1;
            animation: pulseMorph 2s infinite;
        }

        @keyframes pulseMorph {
            0% { transform: scale(1); opacity: 0.6; background-color: #5c5cbe; } /* Lighter Blue */
            45% { background-color: #5c5cbe; }
            55% { background-color: #86efac; } /* Light Green */
            70% { transform: scale(1.5); opacity: 0; background-color: #86efac; }
            100% { transform: scale(1); opacity: 0; }
        }

        /* 4. User Icon (Exit) */
        .agent-mode-user {
            display: block; color: white;
            animation: userExit 1.4s ease forwards;
        }

        @keyframes userExit {
            0% { opacity: 0; transform: scale(0.5); }
            20% { opacity: 1; transform: scale(1); }
            60% { opacity: 1; transform: scale(1); }
            70% { opacity: 0; transform: scale(0); }
            100% { opacity: 0; transform: scale(0); }
        }

        /* 5. Check Icon (Entry & Draw) */
        .agent-mode-check {
            color: white !important; opacity: 0; transform: scale(0);
            animation: checkPopIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) 1.1s forwards;
        }

        @keyframes checkPopIn {
            0% { opacity: 0; transform: scale(0); }
            100% { opacity: 1; transform: scale(1); }
        }

        .agent-mode-check path {
            stroke-dasharray: 20; stroke-dashoffset: 20;
            animation: drawVariableSpeed 0.4s linear 1.4s forwards;
        }

        @keyframes drawVariableSpeed {
            0% { stroke-dashoffset: 20; }
            60% { stroke-dashoffset: 14; }
            100% { stroke-dashoffset: 0; }
        }
    </style>
    <script src="tab-session.js"></script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
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
            <!-- Main content area -->
            <div class="flex flex-1 flex-col lg:ml-64 pt-16">
                <!-- Form Content -->
                <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Create New Agent</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Add a new agent to the system with their personal details.</p>
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

                    <!-- Create Agent Form -->
                    <div class="rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col">
                                    <label class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" required class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="Enter full name"/>
                                </div>
                                <div class="flex flex-col">
                                    <label class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" required class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="Enter email address"/>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div class="flex flex-col">
                                    <label class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                                    <input type="text" name="username" required class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="Enter username"/>
                                </div>
                                <div class="flex flex-col">
                                    <label class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Company Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="company_name" required class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="Enter company name"/>
                                </div>
                                <div class="flex flex-col">
                                    <label class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Phone Number <span class="text-red-500">*</span></label>
                                    <input type="tel" name="phone_number" required class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary" placeholder="e.g. +60123456789"/>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
                                <button type="reset" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">Reset</button>
                                <button type="submit" class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121]">Create Agent</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ========================================= -->
    <!-- AGENT CREATED MODAL HTML                  -->
    <!-- ========================================= -->
    <div id="agent-created-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal Panel -->
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
                <div class="bg-white px-4 pt-6 pb-6 sm:p-6">
                    <div class="flex flex-col items-center">
                        
                        <!-- ICON WRAPPER -->
                        <div id="agent-icon-wrapper" class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-green-100 sm:mx-0 mb-5 shadow-inner">
                            
                            <!-- 1. Tick Icon (Hidden initially) -->
                            <svg id="agent-success-icon" class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                            </svg>

                            <!-- 2. User Icon (Visible initially) -->
                            <svg id="agent-user-icon" class="w-8 h-8 absolute hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>

                        </div>

                        <div class="text-center w-full">
                            <h3 class="text-xl font-bold text-gray-900" id="modal-title">
                                Agent Created
                            </h3>
                            <p class="text-xs text-gray-400 mt-1 mb-5">
                                Please copy these credentials securely.
                            </p>
                            
                            <!-- Credentials Display (Clean Stacked Style) -->
                            <div class="space-y-3 w-full">
                                
                                <!-- Username Field (Read Only) -->
                                <div class="flex flex-col text-left p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-0.5">Username</span>
                                    <span id="display-username" class="font-mono text-slate-800 font-semibold text-sm truncate select-all"></span>
                                </div>

                                <!-- Password Field (Read Only) -->
                                <div class="flex flex-col text-left p-3 bg-slate-50 border border-slate-200 rounded-lg">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-0.5">Password</span>
                                    <span id="display-password" class="font-mono text-slate-800 font-semibold text-sm truncate select-all"></span>
                                </div>

                                <!-- SINGLE COPY BUTTON -->
                                <button onclick="copyAllCredentials(this)" class="group w-full flex items-center justify-center p-2.5 bg-blue-50 border border-blue-100 rounded-lg text-blue-700 hover:bg-blue-100 hover:border-blue-200 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span class="material-symbols-outlined text-lg mr-2 group-hover:scale-110 transition-transform">content_copy</span>
                                    <span class="text-sm font-bold">Copy Credentials</span>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:px-6">
                    <button type="button" onclick="closeAgentModal()" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-3 bg-[#212180] text-sm font-bold text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] transition-colors">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Input for Copy Logic -->
    <textarea id="clipboard-helper" class="absolute -left-9999px opacity-0 pointer-events-none"></textarea>
<script>
// Get page name for unique localStorage keys
const page = window.location.pathname.split('/').pop().split('.')[0] || 'index';

// Persist toggle states with page prefix
const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

function loadToggleStates() {
    toggles.forEach(id => {
        const key = id;
        const state = localStorage.getItem(key);
        const element = document.getElementById(id);
        if (state === 'true' && element) {
            element.checked = true;
        }
    });
}

function saveToggleState(id) {
    const checkbox = document.getElementById(id);
    const key = id;
    localStorage.setItem(key, checkbox.checked);
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

setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

function updateTime() {
    const date = new Date();
    document.getElementById('date').innerText = date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}).toUpperCase();
    document.getElementById('time').innerText = date.toLocaleTimeString('en-US', {hour12: false});
}
setInterval(updateTime, 1000);
updateTime(); // initial

// Profile dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const profileBtn = document.getElementById('profile-dropdown-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileBtn && profileDropdown) {
        // Toggle dropdown on button click
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                profileDropdown.classList.add('hidden');
            }
        });
    }
});

// Save and restore sidebar scroll position
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const scrollTop = localStorage.getItem('sidebarScrollTop');
        if (scrollTop) {
            sidebar.scrollTop = parseInt(scrollTop);
        }
    }
});

window.addEventListener('beforeunload', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
    }
});
    </script>
    <script>
        const agentModal = document.getElementById('agent-created-modal');
        const agentWrapper = document.getElementById('agent-icon-wrapper');
        const agentSuccessIcon = document.getElementById('agent-success-icon');
        const agentUserIcon = document.getElementById('agent-user-icon');
        
        const displayUser = document.getElementById('display-username');
        const displayPass = document.getElementById('display-password');

        function triggerAgentSuccess(username, password) {
            // 1. Set Text
            displayUser.textContent = username;
            displayPass.textContent = password;

            // 2. Reset Animation
            agentWrapper.classList.remove('agent-mode-wrapper');
            agentUserIcon.classList.remove('agent-mode-user');
            agentUserIcon.classList.add('hidden');
            agentSuccessIcon.classList.remove('agent-mode-check');
            void agentWrapper.offsetWidth; // Force Reflow

            // 3. Trigger Animation
            agentWrapper.classList.add('agent-mode-wrapper');
            agentUserIcon.classList.remove('hidden');
            agentUserIcon.classList.add('agent-mode-user');
            agentSuccessIcon.classList.add('agent-mode-check');

            // 4. Show Modal
            agentModal.classList.remove('hidden');
        }

        function closeAgentModal() {
            agentModal.classList.add('hidden');
            // Optional: Redirect or refresh after closing
            // window.location.href = 'user_management.php';
        }

        // New "Copy All" Function with Robust Fallback
        function copyAllCredentials(btnElement) {
            const username = displayUser.textContent;
            const password = displayPass.textContent;
            
            // Format the text exactly as requested
            const textToCopy = `Username : ${username}\nPassword : ${password}`;
            
            // Define fallback strategy first
            function useFallback() {
                const textArea = document.getElementById('clipboard-helper');
                textArea.value = textToCopy;
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopyFeedback(btnElement);
                } catch (err) {
                    console.error('Fallback: Unable to copy', err);
                    alert('Unable to copy to clipboard automatically. Please select the text and copy manually.');
                }
            }

            // Modern Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        showCopyFeedback(btnElement);
                    })
                    .catch((err) => {
                        console.warn('Clipboard API blocked or failed, attempting fallback.', err);
                        // Explicitly handle the error by trying the fallback
                        useFallback();
                    });
            } else {
                // Fallback for older browsers or non-secure contexts
                useFallback();
            }
        }

        function showCopyFeedback(btnElement) {
            const iconSpan = btnElement.querySelector('.material-symbols-outlined');
            const textSpan = btnElement.querySelectorAll('span')[1];
            
            const originalIcon = iconSpan.textContent;
            const originalText = textSpan.textContent;
            
            // Change UI to success state
            iconSpan.textContent = 'check_circle';
            textSpan.textContent = 'Copied!';
            
            btnElement.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-100');
            btnElement.classList.add('bg-green-50', 'text-green-700', 'border-green-200');

            // Revert after 2 seconds
            setTimeout(() => {
                iconSpan.textContent = originalIcon;
                textSpan.textContent = originalText;
                
                btnElement.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-100');
                btnElement.classList.remove('bg-green-50', 'text-green-700', 'border-green-200');
            }, 2000);
        }

        <?php if (isset($successData)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            triggerAgentSuccess('<?php echo htmlspecialchars($successData['username']); ?>', '<?php echo htmlspecialchars($successData['password']); ?>');
        });
        <?php endif; ?>
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.querySelector('input[name="name"]');
            if (nameInput) {
                nameInput.addEventListener('input', function(e) {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    
                    // Store current value
                    const currentVal = this.value;
                    
                    // Convert to Title Case:
                    // 1. Lowercase everything
                    // 2. Capitalize first letter of each word (handling spaces, hyphens, apostrophes via \b)
                    const formattedVal = currentVal.toLowerCase().replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    });
                    
                    // Only update if changed to avoid unnecessary reflows
                    if (currentVal !== formattedVal) {
                        this.value = formattedVal;
                        
                        // Restore cursor position
                        this.setSelectionRange(start, end);
                    }
                });
            }
        });
    </script>
</body>
