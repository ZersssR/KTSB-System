<?php
require_once __DIR__ . '/../../Utils/CheckAdminAuth.php';
require_once __DIR__ . '/../../../config/app.php';

// Get admin details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Get list of companies (users)
$stmt = $conn->query("SELECT id, username, company_name FROM users WHERE role = 'user' AND status = 'active' ORDER BY company_name ASC");
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentPage = 'bod_management.php';
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>BOD Management</title>
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
        <?php include __DIR__ . '/../../Components/Layout/AdminSidebar.php'; ?>

        <!-- Header -->
        <header
            class="fixed top-0 left-0 right-0 z-40 flex h-16 items-center justify-between border-b border-[#DEE2E6] bg-[#242424] px-4 backdrop-blur-sm dark:border-gray-700 dark:bg-background-dark/80 md:px-6">
            <div class="flex items-center gap-4">
                <input class="peer hidden" id="nav-toggle" type="checkbox" />
                <label class="cursor-pointer text-white lg:hidden" for="nav-toggle">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </label>
                <h1 class="hidden text-xl font-bold text-white dark:text-gray-200 md:block">BOD Management</h1>
            </div>
            <div class="flex items-center gap-6 text-white dark:text-gray-300">
                <div class="hidden text-right sm:block">
                    <p id="date" class="text-sm font-medium"></p>
                    <p id="time" class="text-xs text-gray-300 dark:text-gray-400"></p>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button type="button" id="profile-dropdown-btn"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/20 hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span class="text-sm font-medium">
                            <?php echo htmlspecialchars($admin['username']); ?>
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col lg:ml-64 pt-16">
            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="mx-auto max-w-7xl">

                    <div class="flex flex-col md:flex-row gap-6 mb-8">
                        <!-- Left Panel: Unassigned Requests -->
                        <div class="w-full md:w-1/2 flex flex-col gap-4">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Unassigned Requests
                                </h2>

                                <!-- Company Selector -->
                                <div class="mb-4">
                                    <label
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Select
                                        Company</label>
                                    <select id="company-select"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                        <option value="">-- Select Company --</option>
                                        <?php foreach ($companies as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>">
                                                <?php echo htmlspecialchars($comp['company_name'] ?: $comp['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Request List -->
                                <div id="request-list"
                                    class="space-y-3 min-h-[200px] max-h-[500px] overflow-y-auto pr-2">
                                    <p class="text-sm text-gray-500 text-center py-8">Select a company to load requests.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: BOD Composition -->
                        <div class="w-full md:w-1/2 flex flex-col gap-4">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 h-full flex flex-col">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">New BOD Composition
                                </h2>

                                <div class="flex-1 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 p-4 mb-4"
                                    id="bod-dropzone">
                                    <p id="empty-bod-msg" class="text-sm text-center text-gray-400 mt-8">
                                        Select requests from the left to add to this BOD.<br>
                                        <span class="text-xs text-orange-500 mt-2 block">Note: Crew Sign On/Off requests
                                            must be in their own BOD.</span>
                                    </p>
                                    <ul id="selected-requests-list" class="space-y-2"></ul>
                                </div>

                                <div class="mt-auto">
                                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                                        <span>BOD Type:</span>
                                        <span id="bod-type-badge" class="font-bold uppercase text-gray-400">Not
                                            Set</span>
                                    </div>
                                    <button id="create-bod-btn" disabled
                                        class="w-full bg-[#212180] text-white font-medium py-2.5 rounded-lg hover:bg-[#212180]/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        Create BOD
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // --- Logic Script ---
        const companySelect = document.getElementById('company-select');
        const requestListEl = document.getElementById('request-list');
        const selectedListEl = document.getElementById('selected-requests-list');
        const emptyMsgEl = document.getElementById('empty-bod-msg');
        const bodTypeBadge = document.getElementById('bod-type-badge');
        const createBtn = document.getElementById('create-bod-btn');

        let currentBodType = null; // 'general', 'crew_sign_on', 'crew_sign_off'
        let selectedRequests = [];

        // Formatting Helpers
        function formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('en-GB');
        }

        function getIcon(type) {
            const icons = {
                'marine_requests': 'directions_boat',
                'crew_sign_on_requests': 'person_add',
                'crew_sign_off_requests': 'person_remove',
                'fuel_water_requests': 'water_drop',
                'light_port_requests': 'anchor'
            };
            return icons[type] || 'description';
        }

        function formatType(type) {
            return type.replace('_requests', '').replace(/_/g, ' ').toUpperCase();
        }

        // Fetch Requests
        companySelect.addEventListener('change', async function () {
            const companyId = this.value;
            // Clear current selection
            selectedRequests = [];
            updateBodUI();

            if (!companyId) {
                requestListEl.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Select a company to load requests.</p>';
                return;
            }

            requestListEl.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Loading...</p>';

            try {
                const response = await fetch(`../../api/get_unassigned_requests.php?company_id=${companyId}`);
                const data = await response.json();

                if (data.success && data.requests.length > 0) {
                    renderRequests(data.requests);
                } else {
                    requestListEl.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">No unassigned requests found for this company.</p>';
                }
            } catch (err) {
                console.error(err);
                requestListEl.innerHTML = '<p class="text-sm text-red-500 text-center py-8">Error loading requests.</p>';
            }
        });

        function renderRequests(requests) {
            requestListEl.innerHTML = '';

            requests.forEach(req => {
                const isSelected = selectedRequests.some(r => r.unique_id === req.unique_id);
                const isDisabled = isSelectionDisabled(req);

                const div = document.createElement('div');
                div.className = `p-3 rounded-lg border transition-all cursor-pointer flex items-center gap-3 ${isSelected ? 'bg-blue-50 border-blue-500' :
                        isDisabled ? 'bg-gray-100 border-gray-200 opacity-50 cursor-not-allowed' :
                            'bg-white border-gray-200 hover:border-blue-300'
                    }`;

                if (!isDisabled) {
                    div.onclick = () => toggleSelection(req);
                }

                div.innerHTML = `
                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-lg">${getIcon(req.source_table)}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">${req.vessel_name}</p>
                        <p class="text-xs text-gray-500">${formatType(req.source_table)} • ${formatDate(req.created_at)}</p>
                    </div>
                    ${isSelected ? '<span class="material-symbols-outlined text-blue-600">check_circle</span>' : ''}
                `;
                requestListEl.appendChild(div);
            });
        }

        function getBodTypeFromRequest(req) {
            if (req.source_table === 'crew_sign_on_requests') return 'crew_sign_on';
            if (req.source_table === 'crew_sign_off_requests') return 'crew_sign_off';
            return 'general';
        }

        function toggleSelection(req) {
            const index = selectedRequests.findIndex(r => r.unique_id === req.unique_id);
            if (index >= 0) {
                selectedRequests.splice(index, 1);
            } else {
                selectedRequests.push(req);
            }
            updateBodUI();

            // Re-render request list to update disabled states
            // We need to fetch the existing list items logic or just re-request which is expensive.
            // Better to just grab the current data from DOM or keep it in memory.
            // For MVP, lets just re-trigger the visual update logic on existing DOM nodes?
            // Or simpler: just re-render from the fetched data if we stored it. 
            // Limitation: We didn't store the full list in a global var. Let's rely on the user logic to be fast enough? 
            // It's better to reload the list visually.
            // Actually, we can just iterate the children of requestListEl and update classes.
            updateRequestListVisuals();
        }

        function isSelectionDisabled(req) {
            if (selectedRequests.length === 0) return false;

            const newType = getBodTypeFromRequest(req);

            // If current selection is Crew Sign On, can only add other Crew Sign On (if allowed)
            // Stricter Rule: "Crew Sign On... need their own BOD number" 

            if (currentBodType === 'crew_sign_on' && newType !== 'crew_sign_on') return true;
            if (currentBodType === 'crew_sign_off' && newType !== 'crew_sign_off') return true;
            if (currentBodType === 'general' && (newType === 'crew_sign_on' || newType === 'crew_sign_off')) return true;

            return false;
        }

        function updateBodUI() {
            // Determine BOD Type
            if (selectedRequests.length > 0) {
                currentBodType = getBodTypeFromRequest(selectedRequests[0]);
                bodTypeBadge.innerText = currentBodType.replace(/_/g, ' ');
                bodTypeBadge.className = 'font-bold uppercase text-blue-600';
                emptyMsgEl.classList.add('hidden');
            } else {
                currentBodType = null;
                bodTypeBadge.innerText = 'Not Set';
                bodTypeBadge.className = 'font-bold uppercase text-gray-400';
                emptyMsgEl.classList.remove('hidden');
            }

            // Render Selected List
            selectedListEl.innerHTML = '';
            selectedRequests.forEach(req => {
                const li = document.createElement('li');
                li.className = "flex items-center justify-between p-2 bg-white rounded shadow-sm text-sm border-l-4 border-blue-500";
                li.innerHTML = `
                    <span>${req.vessel_name} - ${formatType(req.source_table)}</span>
                    <button class="text-red-500 hover:text-red-700" onclick='toggleSelection(${JSON.stringify(req)})'>
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                `;
                selectedListEl.appendChild(li);
            });

            createBtn.disabled = selectedRequests.length === 0;
        }

        function updateRequestListVisuals() {
            // Access the stored requests. Since we didn't store globally, lets just quick hack:
            // We can't easily re-render without the data. 
            // Ideally we should have stored `allRequests` global variable. 
            // I will leave this as "Logic exists in `isSelectionDisabled` but visual update on list needs refresh".
            // Let's trigger the fetch logic again? No that's bad.
            // We will assume the user clicks, `toggleSelection` runs, and we should ideally re-render only if we have data.
            // Let's modify the fetch to store data.

            // ... Wait, I can't modify the fetch code easily now without rewriting it. 
            // I will simply reload the company change event? No.
            // I will implement `allRequests` global in the next step or just trust the user click interaction for now.
            // Actually, I can just grab the click event to prevent action, but visual feedback is key.
            // Let's rely on the previous tool call being mostly correct and assume I'll fix minor JS logic if needed.
            // Actually, the `renderRequests` function is only called on fetch. I should store `window.currentCompanyRequests`.
        }

        // Hook up the missing piece: Store requests on fetch
        const originalRender = renderRequests;
        renderRequests = function (requests) {
            window.currentCompanyRequests = requests;
            originalRender(requests);
        }

        // Redefine updateVisuals to use window.currentCompanyRequests
        updateRequestListVisuals = function () {
            if (window.currentCompanyRequests) {
                originalRender(window.currentCompanyRequests);
            }
        }

        // Create BOD
        createBtn.addEventListener('click', async () => {
            if (selectedRequests.length === 0) return;

            createBtn.disabled = true;
            createBtn.innerText = 'Creating...';

            try {
                const response = await fetch('../../api/create_bod.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        company_id: companySelect.value,
                        bod_type: currentBodType,
                        requests: selectedRequests.map(r => ({ id: r.original_id, table: r.source_table }))
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('BOD Created Successfully!');
                    location.reload();
                } else {
                    alert('Failed: ' + (data.error || 'Unknown error'));
                    createBtn.disabled = false;
                    createBtn.innerText = 'Create BOD';
                }

            } catch (err) {
                alert('Error creating BOD');
                console.error(err);
                createBtn.disabled = false;
                createBtn.innerText = 'Create BOD';
            }
        });

    </script>
</body>

</html>