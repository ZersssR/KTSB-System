<?php
// Protected index page - requires authentication and appropriate role
require_once __DIR__ . '/../../Utils/CheckAuth.php';
require_once __DIR__ . '/../../../config/app.php';

// Get current user data
$currentUser = getCurrentUser();

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Handle Request Submission
$message = '';
$messageType = '';

$conn = getDBConnection();
$companiesStmt = $conn->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $companiesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Vessels
$vesselsStmt = $conn->query("SELECT id, vessel_name FROM vessels ORDER BY vessel_name ASC");
$vessels = $vesselsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $conn is already established above

    // Sanitize Inputs
    $vesselName = sanitize($_POST['vessel'] ?? '');
    $companyName = sanitize($_POST['company'] ?? '');
    $requestDate = sanitize($_POST['date'] ?? '');
    $requestTime = sanitize($_POST['time'] ?? '');
    $receiptNo = sanitize($_POST['receipt_no'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');
    $userId = $currentUser['id'];

    if (empty($vesselName) || empty($companyName) || empty($requestDate) || empty($requestTime)) {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } else {
        // Handle File Upload
        $receiptFile = null;
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../uploads/receipts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $fileName = 'LD_RECEIPT_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetPath)) {
                $receiptFile = 'uploads/receipts/' . $fileName;
            } else {
                $message = "Failed to upload receipt file.";
                $messageType = "error";
            }
        }

        if (empty($message)) {
            try {
                // Generate ID (e.g., LPR-202312-001)
                $prefix = "LPR-" . date("Ym") . "-";
                $stmt = $conn->prepare("SELECT COUNT(*) FROM light_port_requests WHERE lightport_id LIKE ?");
                $stmt->execute([$prefix . "%"]);
                $count = $stmt->fetchColumn();
                $requestId = $prefix . str_pad($count + 1, 3, "0", STR_PAD_LEFT);

                // Insert into light_port_requests
                $stmt = $conn->prepare("INSERT INTO light_port_requests (
                    lightport_id, user_id, request_type, vessel_name, company_name, 
                    request_date, request_time, receipt_no, receipt_file, remarks, status
                ) VALUES (?, ?, 'light_dues', ?, ?, ?, ?, ?, ?, ?, 'pending')");

                $stmt->execute([
                    $requestId,
                    $userId,
                    $vesselName,
                    $companyName,
                    $requestDate,
                    $requestTime,
                    $receiptNo,
                    $receiptFile,
                    $remarks
                ]);

                $message = "Light Dues Request ($requestId) submitted successfully!";
                $messageType = "success";

            } catch (PDOException $e) {
                error_log("Database Error: " . $e->getMessage());
                $message = "An error occurred while submitting your request. Please try again.";
                $messageType = "error";
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
    <link rel="icon" href="assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Light Dues Request</title>
    <script src="assets/js/tailwindcss.js"></script>
    <link href="assets/css/fonts.css" rel="stylesheet" />
    <link href="assets/css/material-icons.css" rel="stylesheet" />
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
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>

        <!-- Header Bar -->
        <header
            class="fixed top-0 left-0 right-0 z-10 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <img src="assets/images/KSB Logo.JPG" alt="KSB Logo"
                    class="h-10 w-auto object-contain mr-1 hidden md:block">
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Kuala Terengganu Support
                    Base</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profile-dropdown-btn"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 dark:hover:bg-primary/20 transition-colors"
                        title="Profile">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span
                            class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['username']); ?></span>
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
            <main class="flex-1 overflow-y-auto p-4 md:p-8 pb-40">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Light Dues Request</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Submit a
                            new request for light dues.</p>
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

                    <form method="POST" enctype="multipart/form-data"
                        class="space-y-6 rounded-lg border border-[#DEE2E6] bg-white p-6 dark:border-gray-700 dark:bg-gray-800/20">

                        <!-- Vessel & Company -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label class="flex flex-col">
                                <span
                                    class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Vessel
                                    Name</span>
                                <select name="vessel"
                                    class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
                                    <option value="" disabled selected>Select Vessel</option>
                                    <?php foreach ($vessels as $vessel): ?>
                                        <option value="<?php echo htmlspecialchars($vessel['vessel_name']); ?>">
                                            <?php echo htmlspecialchars($vessel['vessel_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="flex flex-col">
                                <span
                                    class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Company</span>
                                <select name="company"
                                    class="form-select w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary">
                                    <option value="">Select Company</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo htmlspecialchars($customer['name']); ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>

                        <!-- Date & Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label class="flex flex-col">
                                <span
                                    class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Date</span>
                                <input type="date" name="date"
                                    class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary"
                                    required>
                            </label>

                            <label class="flex flex-col">
                                <span
                                    class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Time</span>
                                <input type="time" name="time"
                                    class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary"
                                    required>
                            </label>
                        </div>

                        <!-- Receipt No -->
                        <label class="flex flex-col">
                            <span
                                class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Receipt
                                No</span>
                            <input type="text" name="receipt_no"
                                class="form-input w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary"
                                placeholder="Enter receipt number">
                        </label>

                        <!-- Remark -->
                        <label class="flex flex-col">
                            <span
                                class="pb-2 text-sm font-medium leading-normal text-[#212529] dark:text-gray-300">Remarks</span>
                            <textarea name="remarks"
                                class="form-textarea w-full rounded-lg border border-[#DEE2E6] bg-background-light px-3 py-2 text-base text-[#212529] placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 dark:focus:border-primary"
                                rows="3" placeholder="Additional remarks..."></textarea>
                        </label>

                        <!-- Upload Receipt -->
                        <!-- Upload Receipt Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Upload Area -->
                            <div
                                class="bg-white rounded-xl border border-[#DEE2E6] shadow-sm p-6 dark:border-gray-700 dark:bg-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Upload File</h3>
                                <div class="space-y-4">
                                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer relative"
                                        id="drop-zone">
                                        <input type="file" name="receipt" id="file-upload"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                            accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="p-6">
                                            <span
                                                class="material-symbols-outlined text-4xl text-gray-400 mb-2">cloud_upload</span>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Click to upload or drag
                                                and drop</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">PDF, JPG, or PNG
                                                (MAX. 2MB)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected File Preview -->
                            <div
                                class="bg-white rounded-xl border border-[#DEE2E6] shadow-sm p-6 dark:border-gray-700 dark:bg-gray-800">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Selected File
                                </h3>
                                <div id="file-preview-container" class="space-y-3">
                                    <div class="text-center text-gray-500 py-8">
                                        <p class="text-sm">No file selected</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-end gap-4 border-t border-[#DEE2E6] pt-6 dark:border-gray-700">
                            <button type="reset"
                                class="rounded-lg px-6 py-2.5 text-sm font-semibold text-primary hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                                Reset
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121]">
                                Submit Request
                            </button>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('file-upload');
            const dropZone = document.getElementById('drop-zone');
            const previewContainer = document.getElementById('file-preview-container');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
            ['dragenter', 'dragover'].forEach(eventName => dropZone.addEventListener(eventName, highlight, false));
            ['dragleave', 'drop'].forEach(eventName => dropZone.addEventListener(eventName, unhighlight, false));
            function highlight() { dropZone.classList.add('border-primary', 'bg-primary/5'); }
            function unhighlight() { dropZone.classList.remove('border-primary', 'bg-primary/5'); }
            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const files = e.dataTransfer.files;
                fileInput.files = files;
                handleFiles(files);
            }

            fileInput.addEventListener('change', function () { handleFiles(this.files); });

            function handleFiles(files) {
                if (files.length > 0) { renderPreview(files[0]); }
                else { previewContainer.innerHTML = '<div class="text-center text-gray-500 py-8"><p class="text-sm">No file selected</p></div>'; }
            }

            function renderPreview(file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileType = file.type.split('/')[1]?.toUpperCase() || 'FILE';
                previewContainer.innerHTML = `
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="bg-white dark:bg-gray-600 p-2 rounded shadow-sm">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl">description</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="${file.name}">${file.name}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${fileType} • ${fileSize} MB</p>
                            </div>
                        </div>
                        <button type="button" id="remove-file-btn" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Remove">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>`;
                document.getElementById('remove-file-btn').addEventListener('click', function () {
                    fileInput.value = ''; handleFiles([]);
                });
            }
        });

        function updateTime() {
            const date = new Date();
            document.getElementById('date').innerText = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase();
            document.getElementById('time').innerText = date.toLocaleTimeString('en-US', { hour12: false });
        }
        setInterval(updateTime, 1000);
        updateTime();

        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileDropdown = document.getElementById('profile-dropdown');

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', (e) => {
                if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        }

        const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];
        function loadToggleStates() {
            toggles.forEach(id => {
                const state = localStorage.getItem(id);
                const element = document.getElementById(id);
                if (state !== null && element) { element.checked = state === 'true'; }
            });
        }
        function saveToggleState(id) {
            const checkbox = document.getElementById(id);
            if (checkbox) { localStorage.setItem(id, checkbox.checked); }
        }
        loadToggleStates();
        toggles.forEach(id => {
            const toggle = document.getElementById(id);
            if (toggle) { toggle.addEventListener('change', () => saveToggleState(id)); }
        });
        function setupToggle(toggleId, submenuId, iconClass) {
            const toggle = document.getElementById(toggleId);
            const submenu = document.getElementById(submenuId);
            const icon = document.querySelector('.' + iconClass);
            if (toggle && submenu && icon) {
                const updateToggle = () => {
                    if (toggle.checked) { submenu.classList.remove('hidden'); icon.classList.add('rotate-180'); }
                    else { submenu.classList.add('hidden'); icon.classList.remove('rotate-180'); }
                };
                toggle.addEventListener('change', updateToggle);
                updateToggle();
            }
        }
        setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
        setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
        setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');
    </script>

</html>