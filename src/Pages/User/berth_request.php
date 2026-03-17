<?php
require_once __DIR__ . '/../../Utils/user_auth.php'; // Assuming this handles user check
require_once __DIR__ . '/../../../config/app.php';

// Get User ID/Company ID - typically stored in session from login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyName = $_SESSION['company_name'] ?? 'My Company';
$currentPage = 'berth_request.php';

// Form Handling
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vesselName = $_POST['vessel_name'] ?? '';
    $poNumber = $_POST['po_no'] ?? '';
    $eta = $_POST['eta_date'] . ' ' . $_POST['eta_time'];
    $etd = $_POST['etd_date'] . ' ' . $_POST['etd_time'];
    $remarks = $_POST['remarks'] ?? '';

    if ($vesselName && $poNumber) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("INSERT INTO marine_requests (user_id, vessel_name, po_number, eta, etd, company, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_assignment')");
            $stmt->execute([$userId, $vesselName, $poNumber, $eta, $etd, $companyName, $remarks]);
            $success = true;
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Request Berth</title>
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
                },
            },
        }
    </script>
    <script src="../tab-session.js"></script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../Components/Layout/UserSidebar.php'; ?>

        <!-- Header -->
        <header
            class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">Request Berth</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button type="button"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span class="text-sm font-medium">
                            <?php echo htmlspecialchars($companyName); ?>
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-4xl">

                    <?php if ($success): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Success</p>
                            <p>Berth request submitted successfully.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Error</p>
                            <p>
                                <?php echo htmlspecialchars($error); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Berth Allocation Request
                        </h2>

                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vessel
                                        Name</label>
                                    <input type="text" name="vessel_name" required
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PO
                                        Number</label>
                                    <input type="text" name="po_no" required
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ETA
                                        (Date & Time)</label>
                                    <div class="flex gap-2">
                                        <input type="date" name="eta_date" required
                                            class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                        <input type="time" name="eta_time" required
                                            class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ETD
                                        (Date & Time)</label>
                                    <div class="flex gap-2">
                                        <input type="date" name="etd_date" required
                                            class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                        <input type="time" name="etd_time" required
                                            class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</label>
                                <textarea name="remarks" rows="4"
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-sm"></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="bg-[#212180] text-white px-6 py-2.5 rounded-lg font-medium hover:bg-[#212180]/90 transition-colors">
                                    Submit Request
                                </button>
                            </div>
                        </form>
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
    </script>
</body>

</html>