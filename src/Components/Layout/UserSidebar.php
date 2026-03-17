<?php
// Main Sidebar Partial
// Handles dispatching to Admin sidebar or rendering User/Agent sidebar

if (isset($currentUser['role']) && $currentUser['role'] === 'admin') {
    // Admin Sidebar
    // Set base path for admin includes if not already set
    if (!isset($basePath)) {
        $basePath = 'admin/';
    }
    // Include the existing Admin sidebar
    // Note: admin/includes/sidebar.php contains its own <aside> tag.
    include __DIR__ . '/AdminSidebar.php';
} else {
    // User / Agent Sidebar
    ?>
    <aside id="sidebar"
        class="absolute inset-y-0 left-0 z-20 flex h-[calc(100vh-4rem)] w-64 -translate-x-full flex-col border-r border-[#DEE2E6] bg-background-light transition-transform duration-300 ease-in-out dark:border-gray-700 dark:bg-background-dark overflow-y-auto peer-checked:translate-x-0 lg:fixed lg:translate-x-0 lg:top-16">
        <div class="flex-1">
            <div id="sidebar-scroll-container" class="flex flex-col p-4">
                <div class="flex flex-col gap-4">
                    <nav class="flex flex-col gap-2 mt-4 pb-8">
                        <?php if (isset($currentUser['role']) && $currentUser['role'] === 'agent'): ?>
                            <!-- Agent navigation with Dashboard -->
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'dashboard.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                                href="dashboard.php">
                                <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"
                                    class="w-6 h-6">
                                    <rect x="10" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                    <rect x="34" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                    <rect x="10" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                    <rect x="34" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                </svg>
                                <p class="text-sm font-medium leading-normal">Dashboard</p>
                            </a>
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-request.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                                href="marine-request.php">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Marine Request</p>
                            </a>

                        <?php else: ?>
                            <?php
                            // Define arrays for submenu pages
                            $otherServicesPages = ['port-dues.php', 'light-dues.php', 'port-clearence.php', 'marine-overtime.php'];
                            $historyPages = ['marine-history.php', 'port-dues-history.php', 'light-dues-history.php', 'port-clearance-history.php', 'marine-overtime-history.php'];
                            $agentPages = ['create_agent.php', 'user_management.php'];
                            ?>
                            
                            <!-- Dashboard -->
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'index.php' || $currentPage === 'dashboard.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                                href="index.php">
                                <svg width="24" height="24" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"
                                    class="w-6 h-6">
                                    <rect x="10" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                    <rect x="34" y="10" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                    <rect x="10" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                    <rect x="34" y="34" width="20" height="20" rx="4" stroke="currentColor" stroke-width="5" />
                                </svg>
                                <p class="text-sm font-medium leading-normal">Dashboard</p>
                            </a>

                            <!-- Marine Request (Berth Request) -->
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-request.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                                href="marine-request.php">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Marine Request (Berth)</p>
                            </a>

                            <!-- Other Services -->
                            <div class="flex flex-col gap-1">
                                <input id="other-services-toggle" type="checkbox" class="hidden peer" <?php echo in_array($currentPage, $otherServicesPages) ? 'checked' : ''; ?> />
                                <label for="other-services-toggle"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer <?php echo in_array($currentPage, $otherServicesPages) ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                    <span class="flex items-center gap-3">
                                        <span class="material-symbols-outlined">build</span>
                                        <p class="text-sm font-medium leading-normal">Other Services</p>
                                    </span>
                                    <span
                                        class="material-symbols-outlined transition-transform duration-200 expand-icon-other-services-toggle">expand_more</span>
                                </label>
                                <div id="other-services-submenu"
                                    class="ml-6 mt-1 hidden peer-checked:block space-y-1 border-l-2 border-orange-500 pl-4">
                                    <a href="port-dues.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'port-dues.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">anchor</span>
                                        <p class="text-sm font-medium leading-normal">Port Dues Request</p>
                                    </a>
                                    <a href="light-dues.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'light-dues.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">lightbulb</span>
                                        <p class="text-sm font-medium leading-normal">Light Dues Request</p>
                                    </a>
                                    <a href="port-clearence.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'port-clearence.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">description</span>
                                        <p class="text-sm font-medium leading-normal">Port Clearance Request</p>
                                    </a>
                                    <a href="marine-overtime.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-overtime.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">timer</span>
                                        <p class="text-sm font-medium leading-normal">Marine Overtime Request</p>
                                    </a>
                                </div>
                            </div>

                            <!-- Endorsements (Pending Requests) -->
                            <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'endorsements.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                                href="endorsements.php">
                                <span class="material-symbols-outlined">verified</span>
                                <p class="text-sm font-medium leading-normal">Endorsement</p>
                            </a>

                            <!-- History -->
                            <div class="flex flex-col gap-1">
                                <input id="history-toggle" type="checkbox" class="hidden peer" <?php echo in_array($currentPage, $historyPages) ? 'checked' : ''; ?> />
                                <label for="history-toggle"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer <?php echo in_array($currentPage, $historyPages) ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                    <span class="flex items-center gap-3">
                                        <span class="material-symbols-outlined">history</span>
                                        <p class="text-sm font-medium leading-normal">History</p>
                                    </span>
                                    <span
                                        class="material-symbols-outlined transition-transform duration-200 expand-icon-history-toggle">expand_more</span>
                                </label>
                                <div id="history-submenu"
                                    class="ml-6 mt-1 hidden peer-checked:block space-y-1 border-l-2 border-orange-500 pl-4">
                                    
                                    <!-- Marine History (All Berth Requests) -->
                                    <a href="marine-history.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-history.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">directions_boat</span>
                                        <p class="text-sm font-medium leading-normal">Marine History</p>
                                    </a>
                                    
                                    <!-- Other Services History -->
                                    <p class="text-xs text-gray-500 mt-2 mb-1 px-2">Other Services History</p>
                                    
                                    <a href="port-dues-history.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'port-dues-history.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">anchor</span>
                                        <p class="text-sm font-medium leading-normal">Port Dues History</p>
                                    </a>
                                    <a href="light-dues-history.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'light-dues-history.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">lightbulb</span>
                                        <p class="text-sm font-medium leading-normal">Light Dues History</p>
                                    </a>
                                    <a href="port-clearance-history.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'port-clearance-history.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">description</span>
                                        <p class="text-sm font-medium leading-normal">Port Clearance History</p>
                                    </a>
                                    <a href="marine-overtime-history.php"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'marine-overtime-history.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="material-symbols-outlined">timer</span>
                                        <p class="text-sm font-medium leading-normal">Marine Overtime History</p>
                                    </a>
                                </div>
                            </div>

                            <!-- Agent Management -->
                            <?php if (isset($currentUser['user_type']) && $currentUser['user_type'] === 'user'): ?>
                                <div class="flex flex-col gap-1">
                                    <input id="agent-toggle" type="checkbox" class="hidden peer" <?php echo in_array($currentPage, $agentPages) ? 'checked' : ''; ?> />
                                    <label for="agent-toggle"
                                        class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer <?php echo in_array($currentPage, $agentPages) ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                        <span class="flex items-center gap-3">
                                            <span class="material-symbols-outlined">group</span>
                                            <p class="text-sm font-medium leading-normal">Agent Management</p>
                                        </span>
                                        <span
                                            class="material-symbols-outlined transition-transform duration-200 expand-icon-agent-toggle">expand_more</span>
                                    </label>
                                    <div id="agent-submenu"
                                        class="ml-6 mt-1 hidden peer-checked:block space-y-1 border-l-2 border-orange-500 pl-4">
                                        <a href="create_agent.php"
                                            class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'create_agent.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                            <span class="material-symbols-outlined">person_add</span>
                                            <p class="text-sm font-medium leading-normal">Add New Agent</p>
                                        </a>
                                        <a href="user_management.php"
                                            class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $currentPage === 'user_management.php' ? 'border-2 border-black text-black font-medium' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                            <span class="material-symbols-outlined">manage_accounts</span>
                                            <p class="text-sm font-medium leading-normal">Manage Agents</p>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </aside>
<?php } ?>