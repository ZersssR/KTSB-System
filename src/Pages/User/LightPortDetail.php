<?php
// Protected light-port-detail page - requires authentication
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

$currentPage = 'light-port-history.php';
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Light Port Detail</title>
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

        /* --- Base SVG Setup --- */
        .cool-mode-icon path {
            stroke-dasharray: 100;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 0s;
        }

        /* --- Wrapper Animation (Elastic Expand) --- */
        .cool-mode-wrapper {
            background-color: #16a34a !important;
            /* Force Green-600 */
            transform: scale(0);
            animation: scaleElastic 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            position: relative;
            z-index: 10;
        }

        @keyframes scaleElastic {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* --- Pulse Effect (Ripple Behind) --- */
        .cool-mode-wrapper::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background-color: rgba(74, 222, 128, 0.6);
            /* Green-400 opacity 0.6 */
            z-index: -1;
            animation: pulseRing 2s infinite;
        }

        @keyframes pulseRing {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }

            70% {
                transform: scale(1.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        /* --- Icon Animation (White Tick, Slow Start / Fast Finish) --- */
        .cool-mode-icon {
            color: white !important;
        }

        .cool-mode-icon path {
            /* Precise length for timing calculations (~20px) */
            stroke-dasharray: 20;
            stroke-dashoffset: 20;

            /* Delay 0.3s (starts as circle pops), Duration 0.4s */
            animation: drawVariableSpeed 0.4s linear 0.3s forwards;
        }

        @keyframes drawVariableSpeed {
            0% {
                stroke-dashoffset: 20;
            }

            60% {
                /* Slow Start: 60% of time to draw the short first leg */
                stroke-dashoffset: 14;
            }

            100% {
                /* Fast Finish: Remaining 40% of time to draw the long leg */
                stroke-dashoffset: 0;
            }
        }

        /* Standardized typography for light port detail page */
        .marine-detail-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }

        .marine-detail-section h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .marine-detail-section p,
        .marine-detail-section span {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #FEF3C7;
            color: #B45309;
        }

        .status-supplied {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-approved {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-rejected {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-default {
            background-color: #F3F4F6;
            color: #374151;
        }
    </style>
    <script src="tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <?php
        if ($currentUser['role'] === 'admin') {
            $activeMenu = 'light_port_requests.php';
        }
        include __DIR__ . '/../../Components/Layout/UserSidebar.php';
        ?>

        <!-- Header Bar -->
        <header
            class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
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
                    <button type="button" id="profile-dropdown-btn"
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
        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    <?php
                    if (!isset($_GET['id'])) {
                        echo "<p>Invalid request.</p>";
                        exit;
                    }

                    $id = $_GET['id']; // String ID
                    
                    require_once __DIR__ . '/../../../config/app.php';
                    $conn = getDBConnection();

                    try {
                        $query = "SELECT lpr.*, a.full_name as agent_name, a.username as agent_username FROM light_port_requests lpr LEFT JOIN agents a ON lpr.assigned_agent_id = a.agent_id WHERE lpr.lightport_id = ?";
                        $params = [$id];

                        // If user is agent, restrict to their assigned requests
                        if ($currentUser['role'] === 'agent') {
                            $query .= " AND lpr.assigned_agent_id = ?";
                            $params[] = $currentUser['agent_id'];
                        }

                        $stmt = $conn->prepare($query);
                        $stmt->execute($params);
                        $request = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$request) {
                            echo "<p>Light port request not found.</p>";
                            exit;
                        }

                        $assignedAgent = $request['agent_name'] ?: ($request['agent_username'] ?: 'Not Assigned');
                    } catch (PDOException $e) {
                        echo "<p>Error loading details: " . htmlspecialchars($e->getMessage()) . "</p>";
                        exit;
                    }
                    ?>

                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="admin/light_port_requests.php" class="hover:text-primary">Request List</a>
                                <span class="mx-2">/</span>
                                <a href="admin/light_port_requests.php" class="hover:text-primary">Light Port</a>
                            <?php else: ?>
                                <a href="light-port-history.php" class="hover:text-primary">History</a>
                                <span class="mx-2">/</span>
                                <a href="light-port-history.php" class="hover:text-primary">Light Port</a>
                            <?php endif; ?>
                            <span class="mx-2">/</span>
                            <span
                                class="text-[#212529] dark:text-gray-200"><?php echo htmlspecialchars($request['vessel_name']); ?></span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Light Port Request Details</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">Detailed
                            information for <?php echo htmlspecialchars($request['vessel_name']); ?></p>
                    </div>

                    <!-- Details Card -->
                    <div class="w-full bg-white rounded-xl shadow-sm p-6 md:p-8">
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- Section 1: Request Information -->
                            <section>
                                <h3 class="text-lg font-semibold text-gray-900 mb-5 border-b pb-2">Request Information
                                </h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Assigned Agent</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($assignedAgent); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Vessel Name</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['vessel_name']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Request Date</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars(date('Y-m-d', strtotime($request['created_at']))); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Booking Date</span>
                                        <span
                                            class="text-sm font-semibold text-gray-900 col-span-2"><?php echo htmlspecialchars($request['request_date']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 items-center">
                                        <span class="text-sm text-gray-600 col-span-1">Status</span>
                                        <div class="col-span-2">
                                            <span
                                                class="status-pill status-<?php echo strtolower($request['status']); ?>"><?php echo htmlspecialchars(ucfirst($request['status'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Section 2: Light Port Service Details -->
                            <section class="marine-detail-section">
                                <h3>Light Port Service Details</h3>
                                <div class="space-y-4">
                                    <div
                                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                                        <h4
                                            class="text-sm font-semibold text-amber-900 dark:text-amber-100 capitalize mb-2">
                                            Port Light Service
                                        </h4>
                                        <div class="space-y-1 text-xs">
                                            <p><span class="text-gray-600 dark:text-gray-400">Services:</span>
                                                <?php echo htmlspecialchars($request['services']); ?></p>
                                            <p><span class="text-gray-600 dark:text-gray-400">Volume/Unit:</span>
                                                <?php echo htmlspecialchars($request['volume_unit']); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-4">
                                        <p><span class="font-medium">Created:</span>
                                            <?php echo htmlspecialchars($request['created_at']); ?></p>
                                        <p><span class="font-medium">Updated:</span>
                                            <?php echo htmlspecialchars($request['updated_at']); ?></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- File Upload Section -->
                    <div class="mt-8 grid md:grid-cols-2 gap-6">
                        <!-- Upload File Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload File</h3>
                            <div class="space-y-4">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg text-center hover:bg-gray-50 transition-colors cursor-pointer"
                                    id="drop-zone">
                                    <input type="file" id="file-upload" class="hidden"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" multiple>
                                    <label for="file-upload" class="cursor-pointer block w-full h-full p-6">
                                        <span
                                            class="material-symbols-outlined text-4xl text-gray-400 mb-2">cloud_upload</span>
                                        <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, Images, Office Docs (Max 10MB)</p>
                                    </label>
                                </div>

                                <!-- Selected Files Display -->
                                <div id="selected-files-container" class="hidden space-y-2">
                                    <!-- Files will be added here dynamically -->
                                </div>

                                <button id="submit-file-btn"
                                    class="w-full bg-[#212180] text-white rounded-lg py-2 px-4 text-sm font-medium hover:bg-[#212180]/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    disabled>
                                    Submit File
                                </button>
                            </div>
                        </div>

                        <!-- Uploaded Files Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Files</h3>
                            <div id="uploaded-files-list" class="space-y-3 max-h-[300px] overflow-y-auto">
                                <!-- Files will be populated here -->
                                <div class="text-center text-gray-500 py-8">
                                    <p class="text-sm">Loading files...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation Modal -->
                    <div id="upload-confirmation-modal" class="fixed inset-0 z-50 hidden overflow-y-auto"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true">
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div
                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <span class="material-symbols-outlined text-red-600">warning</span>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                Confirm Upload</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">Are you sure you want to upload this
                                                    file? You cannot delete it once submitted.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" id="confirm-upload-btn"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">Submit</button>
                                    <button type="button" id="cancel-upload-btn"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Success Modal -->
                    <div id="upload-success-modal" class="fixed inset-0 z-50 hidden overflow-y-auto"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true">
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <!-- Wrapper Div (Green Circle Background) -->
                                        <div
                                            class="cool-mode-wrapper mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <!-- SVG Icon -->
                                            <svg class="cool-mode-icon w-6 h-6 text-green-600" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <!-- Single Line Path with stroke-width 4 -->
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                Upload Successful</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">File uploaded successfully.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" id="success-modal-close-btn"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#212180] text-base font-medium text-white hover:bg-[#212180]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#212180] sm:ml-3 sm:w-auto sm:text-sm">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const fileInput = document.getElementById('file-upload');
                            const dropZone = document.getElementById('drop-zone');
                            const selectedFilesContainer = document.getElementById('selected-files-container');
                            const submitFileBtn = document.getElementById('submit-file-btn');
                            const uploadedFilesList = document.getElementById('uploaded-files-list');

                            const requestId = '<?php echo $id; ?>';
                            const requestType = 'light_port';
                            let selectedFiles = [];

                            const modal = document.getElementById('upload-confirmation-modal');
                            const confirmBtn = document.getElementById('confirm-upload-btn');
                            const cancelBtn = document.getElementById('cancel-upload-btn');

                            const successModal = document.getElementById('upload-success-modal');
                            const successModalCloseBtn = document.getElementById('success-modal-close-btn');

                            // Load existing files
                            loadUploadedFiles();

                            // File Selection
                            fileInput.addEventListener('change', handleFileSelect);

                            // Drag and Drop
                            dropZone.addEventListener('dragover', (e) => {
                                e.preventDefault();
                                dropZone.classList.add('border-blue-500', 'bg-blue-50');
                            });

                            dropZone.addEventListener('dragleave', (e) => {
                                e.preventDefault();
                                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                            });

                            dropZone.addEventListener('drop', (e) => {
                                e.preventDefault();
                                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                                if (e.dataTransfer.files.length) {
                                    handleFiles(e.dataTransfer.files);
                                }
                            });

                            function handleFileSelect(e) {
                                if (e.target.files.length) {
                                    handleFiles(e.target.files);
                                }
                            }

                            function handleFiles(files) {
                                const newFiles = Array.from(files);
                                const validFiles = [];

                                newFiles.forEach(file => {
                                    if (file.size > 10 * 1024 * 1024) {
                                        alert(`File ${file.name} is too large. Max 10MB.`);
                                    } else {
                                        // Check for duplicates in currently selected files
                                        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                                            validFiles.push(file);
                                        }
                                    }
                                });

                                selectedFiles = [...selectedFiles, ...validFiles];
                                renderSelectedFiles();
                                updateSubmitButton();

                                // Reset input so same file can be selected again if removed
                                fileInput.value = '';
                            }

                            function renderSelectedFiles() {
                                if (selectedFiles.length === 0) {
                                    selectedFilesContainer.classList.add('hidden');
                                    selectedFilesContainer.innerHTML = '';
                                    return;
                                }

                                selectedFilesContainer.classList.remove('hidden');
                                selectedFilesContainer.innerHTML = selectedFiles.map((file, index) => `
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <span class="material-symbols-outlined text-gray-500">description</span>
                                        <span class="text-sm text-gray-700 truncate">${file.name}</span>
                                        <span class="text-xs text-gray-400">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                                    </div>
                                    <button type="button" class="remove-file-btn text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50" data-index="${index}">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            `).join('');

                                // Add event listeners to remove buttons
                                document.querySelectorAll('.remove-file-btn').forEach(btn => {
                                    btn.addEventListener('click', function () {
                                        const index = parseInt(this.dataset.index);
                                        removeFile(index);
                                    });
                                });
                            }

                            function removeFile(index) {
                                selectedFiles.splice(index, 1);
                                renderSelectedFiles();
                                updateSubmitButton();
                            }

                            function updateSubmitButton() {
                                submitFileBtn.disabled = selectedFiles.length === 0;
                            }

                            // Submit File Button Click
                            submitFileBtn.addEventListener('click', () => {
                                if (selectedFiles.length === 0) return;
                                modal.classList.remove('hidden');
                            });

                            // Modal Confirm Button
                            confirmBtn.addEventListener('click', () => {
                                modal.classList.add('hidden');
                                uploadFiles();
                            });

                            // Modal Cancel Button
                            cancelBtn.addEventListener('click', () => {
                                modal.classList.add('hidden');
                            });

                            // Success Modal Close Button
                            successModalCloseBtn.addEventListener('click', () => {
                                successModal.classList.add('hidden');
                            });

                            // Actual Upload Function
                            async function uploadFiles() {
                                submitFileBtn.disabled = true;
                                submitFileBtn.textContent = 'Uploading...';

                                let successCount = 0;
                                let failCount = 0;
                                let errors = [];

                                for (const file of selectedFiles) {
                                    const formData = new FormData();
                                    formData.append('file', file);
                                    formData.append('request_id', requestId);
                                    formData.append('request_type', requestType);

                                    try {
                                        const response = await fetch('api/upload_file.php', {
                                            method: 'POST',
                                            body: formData
                                        });
                                        const data = await response.json();

                                        if (response.ok) {
                                            successCount++;
                                        } else {
                                            failCount++;
                                            errors.push(`${file.name}: ${data.error || 'Upload failed'}`);
                                        }
                                    } catch (error) {
                                        failCount++;
                                        errors.push(`${file.name}: ${error.message}`);
                                    }
                                }

                                // Reset UI
                                selectedFiles = [];
                                renderSelectedFiles();
                                updateSubmitButton();
                                submitFileBtn.textContent = 'Submit File';
                                loadUploadedFiles();

                                if (failCount === 0) {
                                    // All success
                                    successModal.classList.remove('hidden');

                                    // Trigger animation
                                    const wrapper = successModal.querySelector('.cool-mode-wrapper');
                                    if (wrapper) {
                                        wrapper.style.animation = 'none';
                                        wrapper.offsetHeight; /* trigger reflow */
                                        wrapper.style.animation = 'scaleElastic 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards';
                                    }
                                    const iconPath = successModal.querySelector('.cool-mode-icon path');
                                    if (iconPath) {
                                        iconPath.style.animation = 'none';
                                        iconPath.offsetHeight; /* trigger reflow */
                                        iconPath.style.animation = 'drawVariableSpeed 0.4s linear 0.3s forwards';
                                    }
                                } else {
                                    // Some failed
                                    let message = `Uploaded ${successCount} files successfully.\nFailed to upload ${failCount} files:\n${errors.join('\n')}`;
                                    alert(message);
                                }
                            }

                            // Load Uploaded Files
                            async function loadUploadedFiles() {
                                try {
                                    const response = await fetch(`api/get_uploaded_files.php?request_id=${requestId}&request_type=${requestType}`);
                                    const data = await response.json();

                                    if (data.success) {
                                        renderFiles(data.documents);
                                    }
                                } catch (error) {
                                    console.error('Error loading files:', error);
                                    uploadedFilesList.innerHTML = '<p class="text-sm text-red-500 text-center">Failed to load files</p>';
                                }
                            }

                            function renderFiles(files) {
                                if (files.length === 0) {
                                    uploadedFilesList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No files uploaded yet</p>';
                                    return;
                                }

                                uploadedFilesList.innerHTML = files.map(file => `
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="bg-white p-2 rounded shadow-sm">
                                            <span class="material-symbols-outlined text-blue-600 text-xl">description</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" title="${file.file_name}">${file.file_name}</p>
                                            <p class="text-xs text-gray-500">By ${file.uploader_name} • ${new Date(file.created_at).toLocaleDateString()}</p>
                                        </div>
                                    </div>
                                    <a href="${file.file_path}" target="_blank" class="text-gray-400 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors" title="Download">
                                        <span class="material-symbols-outlined">download</span>
                                    </a>
                                </div>
                            `).join('');
                            }
                        });
                    </script>

                    <!-- Back Button -->
                    <div class="mt-6">
                        <a href="light-port-history.php"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#212121] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#212121]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#212121] transition-colors">
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            Back to History
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Get page name for unique localStorage keys
        const page = window.location.pathname.split('/').pop().split('.')[0] || 'index';

        // Persist toggle states globally across all pages
        const toggles = ['history-toggle', 'other-services-toggle', 'agent-toggle'];

        function loadToggleStates() {
            toggles.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    const state = localStorage.getItem(id);
                    if (state !== null) {
                        element.checked = state === 'true';
                    }
                }
            });

            // Force history toggle to be checked and expanded for detail pages
            const historyToggle = document.getElementById('history-toggle');
            const historySubmenu = document.getElementById('history-submenu');
            const historyIcon = document.querySelector('.expand-icon-history-toggle');
            if (historyToggle && historySubmenu && historyIcon) {
                historyToggle.checked = true;
                historySubmenu.classList.remove('hidden');
                historyIcon.classList.add('rotate-180');
            }
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

        setupToggle('history-toggle', 'history-submenu', 'expand-icon-history-toggle');
        setupToggle('other-services-toggle', 'other-services-submenu', 'expand-icon-other-services-toggle');
        setupToggle('agent-toggle', 'agent-submenu', 'expand-icon-agent-toggle');

        function updateTime() {
            const date = new Date();
            document.getElementById('date').innerText = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase();
            document.getElementById('time').innerText = date.toLocaleTimeString('en-US', { hour12: false });
        }
        setInterval(updateTime, 1000);
        updateTime(); // initial

        // Save and restore sidebar scroll position
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                const scrollTop = localStorage.getItem('sidebarScrollTop');
                if (scrollTop) {
                    sidebar.scrollTop = parseInt(scrollTop);
                }
            }
        });

        window.addEventListener('beforeunload', function () {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                localStorage.setItem('sidebarScrollTop', sidebar.scrollTop);
            }
        });

        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function () {
            const profileBtn = document.getElementById('profile-dropdown-btn');
            const profileDropdown = document.getElementById('profile-dropdown');

            if (profileBtn && profileDropdown) {
                // Toggle dropdown on button click
                profileBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                });

                // Close dropdown when pressing Escape
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        profileDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>

</html>