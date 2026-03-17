<?php
require_once __DIR__ . '/../../../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin details
$conn = getDBConnection();
$pdo = $conn; // Alias for compatibility with existing code in this file
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user_id = $_SESSION['admin_id'];
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Set current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_event') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO calendar_events (title, start_date, end_date, event_type, is_ramadhan, user_id, created_at)
            VALUES (?, ?, ?, 'holiday', ?, ?, NOW())
        ");
        
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        // Ensure standard datetime format
        if (strpos($start_date, 'T') !== false) {
            $start_date = str_replace('T', ' ', $start_date) . ':00';
            // Convert 12:00 to 00:00 for midnight
            $start_date = str_replace(' 12:00:00', ' 00:00:00', $start_date);
        }
        if (strpos($end_date, 'T') !== false) {
            $end_date = str_replace('T', ' ', $end_date) . ':00';
            // Convert 12:00 to 00:00 for midnight
            $end_date = str_replace(' 12:00:00', ' 00:00:00', $end_date);
        }

        // Check if the user_id exists in the users table
        $stmt_check_user = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
        $stmt_check_user->execute([$user_id]);
        $user_exists_in_admin_db = $stmt_check_user->fetch();

        // If user_id does not exist, set it to NULL
        $calendar_user_id = $user_exists_in_admin_db ? $user_id : null;

        $stmt->execute([
            $_POST['title'],
            $start_date,
            $end_date,
            isset($_POST['is_ramadhan']) ? 1 : 0,
            $calendar_user_id
        ]);

        $message = 'Event added successfully!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Fetch celebration days from database
$celebrationDays = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM calendar_events 
        WHERE event_type = 'holiday' 
        ORDER BY start_date ASC
    ");
    $celebrationDays = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error quietly or log
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Holiday Calendar</title>
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
        <!-- Sidebar -->
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
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($admin['username'] ?? 'Admin'); ?></span>
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
                <div class="w-full">
                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <span>Master Data</span>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200">Calendar</span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <div>
                            <h2 class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">Calendar</h2>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Manage celebration days and holidays.</p>
                        </div>
                        <button class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/90 inline-flex items-center gap-2 shadow-lg shadow-gray-200/20 dark:shadow-gray-900/20 transition-all font-semibold" onclick="openAddEventModal()">
                            <span class="material-symbols-outlined">add</span>
                            Add Event
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="mb-6 p-4 rounded-xl <?php echo $message_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?> shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined"><?php echo $message_type === 'success' ? 'check_circle' : 'error'; ?></span>
                                <p class="font-medium"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white mb-4">Celebration Days</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Celebration Day Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Start Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">End Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Total Day</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">Ramadhan Month</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($celebrationDays)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">No events found. Add one to get started.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($celebrationDays as $day): 
                            $start = new DateTime($day['start_date']);
                            $end = new DateTime($day['end_date']);
                            $interval = $start->diff($end);
                            $total_days = $interval->days + 1; // Inclusive
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($day['title']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $start->format('d/m/Y H:i'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $end->format('d/m/Y H:i'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $total_days; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    <input type="checkbox" <?php echo $day['is_ramadhan'] ? 'checked' : ''; ?> disabled class="form-checkbox h-4 w-4 text-primary rounded focus:ring-primary dark:bg-slate-700 dark:border-slate-600">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div id="addEventModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Add Celebration Event</h3>
                <button onclick="closeAddEventModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_event">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Event Name</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Start Date</label>
                    <input type="datetime-local" name="start_date" id="start_date" required
                           class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">End Date</label>
                    <input type="datetime-local" name="end_date" id="end_date" required
                           class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Ramadhan Month</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_ramadhan" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-600 rounded-full peer peer-focus:ring-2 peer-focus:ring-primary peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" onclick="closeAddEventModal()" class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors font-medium">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg shadow hover:shadow-lg transition-all font-semibold">
                        Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

            </main>
        </div>
    </div>

<script>
    function updateTime() {
        const date = new Date();
        document.getElementById('date').innerText = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase();
        document.getElementById('time').innerText = date.toLocaleTimeString('en-US', { hour12: false });
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Profile dropdown
    document.addEventListener('DOMContentLoaded', function () {
        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileDropdown = document.getElementById('profile-dropdown');

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', function (e) { e.stopPropagation(); profileDropdown.classList.toggle('hidden'); });
            document.addEventListener('click', function (e) { if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) profileDropdown.classList.add('hidden'); });
        }
    });

function openAddEventModal() {
    document.getElementById('addEventModal').classList.remove('hidden');

    // Set default time to 00:00 (midnight) for new events
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Format dates as YYYY-MM-DDTHH:MM (required by datetime-local input)
    const startDateValue = tomorrow.toISOString().slice(0, 16); // Remove seconds and timezone
    const endDateValue = startDateValue; // Same date and time for end

    // Set default values with 00:00 time
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (!startDateInput.value) {
        startDateInput.value = startDateValue.replace('T', 'T00:00');
    }
    if (!endDateInput.value) {
        endDateInput.value = endDateValue.replace('T', 'T00:00');
    }
}

function closeAddEventModal() {
    document.getElementById('addEventModal').classList.add('hidden');
}

// Function to ensure 12:00 is converted to 00:00 in the form
function normalizeTimeInputs() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    [startDateInput, endDateInput].forEach(input => {
        if (input.value) {
            // Replace T12:00 with T00:00 to ensure midnight representation
            input.value = input.value.replace(/T12:00$/, 'T00:00');
        }
    });
}

// Add event listeners to normalize time on change
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (startDateInput) {
        startDateInput.addEventListener('change', normalizeTimeInputs);
    }
    if (endDateInput) {
        endDateInput.addEventListener('change', normalizeTimeInputs);
    }
});

// Close modal when clicking outside
document.getElementById('addEventModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddEventModal();
    }
});
</script>
</body>
</html>
