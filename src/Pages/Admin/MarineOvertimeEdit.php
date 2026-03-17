<?php
// Protected marine overtime edit page - requires authentication
require_once __DIR__ . '/../../Utils/CheckAuth.php';

// Get current user data
$currentUser = getCurrentUser();

// Only admin can access this page
if ($currentUser['role'] !== 'admin') {
    header('Location: ../unauthorized.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['overtime_id'])) {
    echo "<p>Invalid request. No ID provided.</p>";
    exit;
}

$overtime_id = $_GET['overtime_id'];

require_once __DIR__ . '/../../../config/app.php';
$conn = getDBConnection();

// Fetch marine overtime request details
try {
    $query = "SELECT mor.*, u.full_name as request_by_name, u.username as request_by_username
              FROM marine_overtime_requests mor
              LEFT JOIN users u ON mor.user_id = u.user_id
              WHERE mor.overtime_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$overtime_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo "<p>Marine overtime request not found.</p>";
        exit;
    }

    // Decode Vessels Data
    $vessels = json_decode($request['vessels_data'], true);
    if (!is_array($vessels)) {
        $vessels = [];
    }

    // Fetch all customers for dropdown
    $customersStmt = $conn->query("SELECT id, name as company_name FROM customers ORDER BY name");
    $customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Also fetch the current company from marine_overtime_requests to ensure it's available
    if (!empty($request['company_name'])) {
        $currentCompanyExists = false;
        foreach ($customers as $customer) {
            if ($customer['company_name'] === $request['company_name']) {
                $currentCompanyExists = true;
                break;
            }
        }
        
        // If current company doesn't exist in customers table, add it to the list
        if (!$currentCompanyExists) {
            $customers[] = [
                'id' => 0,
                'company_name' => $request['company_name']
            ];
        }
    }

} catch (PDOException $e) {
    echo "<p>Error loading details: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare update data
        $company_name = $_POST['company_name'] ?? '';
        $request_date = $_POST['request_date'] ?? '';
        $request_time = $_POST['request_time'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $status = $_POST['status'] ?? 'pending';
        $receipt_no = $_POST['receipt_no'] ?? '';
        
        // Handle vessels - convert from comma-separated string back to array and JSON
        $vessels_input = $_POST['vessels'] ?? '';
        $vessels_array = array_filter(array_map('trim', explode("\n", $vessels_input)));
        $vessels_json = json_encode($vessels_array);
        
        // Update query
        $updateQuery = "UPDATE marine_overtime_requests SET 
                        company_name = ?, 
                        request_date = ?, 
                        request_time = ?, 
                        remarks = ?, 
                        status = ?, 
                        receipt_no = ?,
                        vessels_data = ?
                        WHERE overtime_id = ?";

        $updateStmt = $conn->prepare($updateQuery);
        $success = $updateStmt->execute([
            $company_name,
            $request_date,
            $request_time,
            $remarks,
            $status,
            $receipt_no,
            $vessels_json,
            $overtime_id
        ]);
        
        if ($success) {
            // Success message
            $successMessage = "Marine overtime request updated successfully!";
            
            // Refresh the data
            $stmt->execute([$overtime_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Redirect back to detail page after 2 seconds
            header("Refresh: 2; URL=marineovertimedetail.php?id=" . $overtime_id);
        } else {
            $error = "Failed to update request.";
        }
        
    } catch (PDOException $e) {
        $error = "Error updating request: " . htmlspecialchars($e->getMessage());
    }
}

$currentPage = 'marine-overtime-history.php';
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Edit Marine Overtime Request</title>
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

        .status-approved {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-rejected {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-completed {
            background-color: #E0F2FE;
            color: #075985;
        }

        .status-default {
            background-color: #F3F4F6;
            color: #374151;
        }

        /* Form styling */
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 2px #3B82F6;
        }

        .form-input:disabled {
            background-color: #F3F4F6;
            color: #6B7280;
            cursor: not-allowed;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "expand_more";
            font-family: 'Material Symbols Outlined';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6B7280;
        }

        select.form-input {
            appearance: none;
            padding-right: 40px;
        }
        
        /* Style for temporary company option */
        .temporary-company {
            background-color: #fef3c7;
            color: #92400e;
            font-style: italic;
        }

        /* Vessels list styling */
        .vessels-container {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .vessel-item {
            display: flex;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #e5e7eb;
        }

        .vessel-item:last-child {
            border-bottom: none;
        }
    </style>
    <script src="tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
        <?php
        if ($currentUser['role'] === 'admin') {
            $activeMenu = 'marine_overtime_requests.php';
        }
        include __DIR__ . '/../../Components/Layout/AdminSidebar.php';
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
                <div class="mx-auto max-w-4xl">
                    <!-- Success/Error Messages -->
                    <?php if (isset($success) && $success): ?>
                        <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200">
                            <div class="flex items-center">
                                <span class="material-symbols-outlined text-green-600 mr-3">check_circle</span>
                                <p class="text-green-800 font-medium"><?php echo $successMessage; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200">
                            <div class="flex items-center">
                                <span class="material-symbols-outlined text-red-600 mr-3">error</span>
                                <p class="text-red-800 font-medium"><?php echo $error; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Breadcrumb -->
                    <div class="mb-4">
                        <nav class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="admin/marine_overtime_requests.php" class="hover:text-primary">Request List</a>
                            <span class="mx-2">/</span>
                            <a href="admin/marine_overtime_requests.php" class="hover:text-primary">Marine Overtime</a>
                            <span class="mx-2">/</span>
                            <a href="marineovertimedetail.php?id=<?php echo htmlspecialchars($request['overtime_id'] ?? ''); ?>" 
                               class="hover:text-primary">Marine Overtime Detail</a>
                            <span class="mx-2">/</span>
                            <span class="text-[#212529] dark:text-gray-200">Edit</span>
                        </nav>
                    </div>

                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Edit Marine Overtime Request</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2 text-base font-normal leading-normal">
                            Edit details for <?php echo htmlspecialchars($request['overtime_id'] ?? 'Unknown Request'); ?>
                        </p>
                    </div>

                    <!-- Edit Form -->
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?overtime_id=' . $overtime_id; ?>" class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6 border-b pb-3">Request Details</h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Request Number (Non-editable) -->
                                <div>
                                    <label class="form-label">Request No.</label>
                                    <input type="text" 
                                           value="<?php echo htmlspecialchars($request['overtime_id'] ?? ''); ?>" 
                                           class="form-input" 
                                           disabled>
                                </div>

                                <!-- Company (Editable Dropdown) -->
                                <div>
                                    <label for="company_name" class="form-label">Company *</label>
                                    <div class="select-wrapper">
                                        <select id="company_name" name="company_name" class="form-input" required>
                                            <option value="">Select Company</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <?php 
                                                $isCurrent = (($request['company_name'] ?? '') == $customer['company_name']);
                                                $isTemporary = ($customer['id'] == 0);
                                                $optionClass = $isTemporary ? 'temporary-company' : '';
                                                ?>
                                                <option value="<?php echo htmlspecialchars($customer['company_name']); ?>"
                                                    <?php echo $isCurrent ? 'selected' : ''; ?>
                                                    class="<?php echo $optionClass; ?>"
                                                    <?php if ($isTemporary) echo 'title="Temporary company from marine_overtime_requests"'; ?>>
                                                    <?php echo htmlspecialchars($customer['company_name']); ?>
                                                    <?php if ($isTemporary) echo ' (from request)'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Date (Editable) -->
                                <div>
                                    <label for="request_date" class="form-label">Date *</label>
                                    <input type="date" 
                                           id="request_date" 
                                           name="request_date" 
                                           value="<?php echo htmlspecialchars($request['request_date'] ?? ''); ?>" 
                                           class="form-input" 
                                           required>
                                </div>

                                <!-- Time (Editable) -->
                                <div>
                                    <label for="request_time" class="form-label">Time *</label>
                                    <input type="time" 
                                           id="request_time" 
                                           name="request_time" 
                                           value="<?php echo htmlspecialchars($request['request_time'] ?? ''); ?>" 
                                           class="form-input" 
                                           required>
                                </div>

                                <!-- Receipt No (Editable) -->
                                <div>
                                    <label for="receipt_no" class="form-label">Receipt No.</label>
                                    <input type="text" 
                                           id="receipt_no" 
                                           name="receipt_no" 
                                           value="<?php echo htmlspecialchars($request['receipt_no'] ?? ''); ?>" 
                                           class="form-input">
                                </div>

                                <!-- Status (Editable Dropdown) -->
                                <div>
                                    <label for="status" class="form-label">Status *</label>
                                    <div class="select-wrapper">
                                        <select id="status" name="status" class="form-input" required>
                                            <option value="pending" <?php echo (($request['status'] ?? '') == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo (($request['status'] ?? '') == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo (($request['status'] ?? '') == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                            <option value="completed" <?php echo (($request['status'] ?? '') == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Vessels (Editable Textarea) -->
                                <div class="md:col-span-2">
                                    <label for="vessels" class="form-label">Vessels (One per line) *</label>
                                    <textarea id="vessels" 
                                              name="vessels" 
                                              rows="5" 
                                              class="form-input" 
                                              required><?php 
                                        $vessels_text = '';
                                        if (!empty($vessels)) {
                                            $vessels_text = implode("\n", $vessels);
                                        }
                                        echo htmlspecialchars($vessels_text); 
                                    ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Enter each vessel name on a new line</p>
                                </div>

                                <!-- Remark (Editable Textarea) -->
                                <div class="md:col-span-2">
                                    <label for="remarks" class="form-label">Remark</label>
                                    <textarea id="remarks" 
                                              name="remarks" 
                                              rows="3" 
                                              class="form-input"><?php echo htmlspecialchars($request['remarks'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Non-editable Information -->
                        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6 border-b pb-3">Request Information</h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Request By (Non-editable) -->
                                <div>
                                    <label class="form-label">Request By</label>
                                    <?php
                                    $requestBy = '';
                                    if (!empty($request['request_by_name'])) {
                                        $requestBy = $request['request_by_name'];
                                    } elseif (!empty($request['request_by_username'])) {
                                        $requestBy = $request['request_by_username'];
                                    } else {
                                        $requestBy = 'N/A';
                                    }
                                    ?>
                                    <input type="text" 
                                           value="<?php echo htmlspecialchars($requestBy); ?>" 
                                           class="form-input" 
                                           disabled>
                                </div>

                                <!-- Created Date (Non-editable) -->
                                <div>
                                    <label class="form-label">Created Date</label>
                                    <?php
                                    $createdDate = !empty($request['created_at']) ? 
                                        date('Y-m-d H:i:s', strtotime($request['created_at'])) : 
                                        'N/A';
                                    ?>
                                    <input type="text" 
                                           value="<?php echo htmlspecialchars($createdDate); ?>" 
                                           class="form-input" 
                                           disabled>
                                </div>

                                <!-- Updated At (Non-editable) -->
                                <div>
                                    <label class="form-label">Updated At</label>
                                    <?php
                                    $updatedAt = !empty($request['updated_at']) ? 
                                        date('Y-m-d H:i:s', strtotime($request['updated_at'])) : 
                                        'Not updated yet';
                                    ?>
                                    <input type="text" 
                                           value="<?php echo htmlspecialchars($updatedAt); ?>" 
                                           class="form-input" 
                                           disabled>
                                </div>

                                <!-- Updated By (Non-editable) -->
                                <div>
                                    <label class="form-label">Updated By</label>
                                    <?php
                                    $updatedBy = !empty($currentUser['full_name']) ? 
                                        $currentUser['full_name'] : 
                                        ($currentUser['username'] ?? 'N/A');
                                    ?>
                                    <input type="text" 
                                           value="<?php echo htmlspecialchars($updatedBy); ?>" 
                                           class="form-input" 
                                           disabled>
                                </div>
                            </div>

                            <!-- Initial Receipt Files Display (Non-editable) -->
                            <?php 
                            $initialReceipts = json_decode($request['receipt_files'] ?? '[]', true);
                            if (!empty($initialReceipts)): 
                            ?>
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Current Receipt Files</h4>
                                    <div class="space-y-2">
                                        <?php foreach ($initialReceipts as $file): ?>
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                                <div class="flex items-center gap-3 overflow-hidden">
                                                    <div class="bg-white p-2 rounded shadow-sm">
                                                        <span class="material-symbols-outlined text-blue-600 text-xl">receipt_long</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">
                                                            <?php echo basename($file); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($file); ?>"
                                                    target="_blank"
                                                    class="text-gray-400 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                    title="Download">
                                                    <span class="material-symbols-outlined">download</span>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Note: To add more receipt files, please upload new files in the detail page.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <button type="button" 
                                    onclick="window.location.href='marine_overtime_requests.php'"
                                    class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gray-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-sm">arrow_back</span>
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition-colors">
                                <span class="material-symbols-outlined text-sm">save</span>
                                Save Changes
                            </button>
                        </div>
                    </form>
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