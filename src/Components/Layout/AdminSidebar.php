<?php
$base = isset($basePath) ? $basePath : '';
// If $activeMenu is set (manually by parent), use it. Otherwise use $currentPage (from parent) or derive from PHP_SELF.
$current = isset($activeMenu) ? $activeMenu : (isset($currentPage) ? $currentPage : basename($_SERVER['PHP_SELF']));
?>
<aside id="sidebar"
    class="absolute inset-y-0 left-0 z-50 flex h-[calc(100vh-4rem)] w-64 -translate-x-full flex-col border-r border-[#DEE2E6] bg-background-light transition-transform duration-300 ease-in-out dark:border-gray-700 dark:bg-background-dark overflow-y-auto peer-checked:translate-x-0 lg:fixed lg:translate-x-0 lg:top-16">
    <div class="flex-1">
        <div id="sidebar-scroll-container" class="flex flex-col p-4">
            <div class="flex flex-col gap-4">
                <nav class="flex flex-col gap-2 mt-4 pb-8">
                    <?php
                    // Helper to determine active states
                    $isRequestListPage = in_array($current, [
                        'marine_planner.php',
                        'marine_requests.php',
                        'marine_detail.php',
                        'marine_edit.php',
                        'marine_endorsed.php',
                        'port_dues_requests.php',
                        'light_dues_requests.php',
                        'port_clearance_requests.php',
                        'marine_overtime_requests.php',
                        'crew_sign_on_requests.php',
                        'crew_sign_off_requests.php',
                        'fuel_water_requests.php',
                        'light_port_requests.php'
                    ]);
                    $isMasterDataPage = in_array($current, ['user_management.php', 'calendar.php', 'customer.php', 'vessels.php', 'berth.php', 'agents.php', 'price_of_services.php', 'status.php']);
                    ?>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'dashboard.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                        href="<?php echo $base; ?>dashboard.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium leading-normal">Dashboard</p>
                    </a>

                    <!-- Master Data Submenu Section -->
                    <div class="flex flex-col gap-1">
                        <input id="master-data-toggle" type="checkbox" class="hidden peer" <?php echo $isMasterDataPage ? 'checked' : ''; ?> />
                        <label for="master-data-toggle"
                            class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                            <span class="flex items-center gap-3">
                                <span class="material-symbols-outlined">storage</span>
                                <p class="text-sm font-medium leading-normal">Master Data</p>
                            </span>
                            <span
                                class="material-symbols-outlined transition-transform duration-200 expand-icon-master-data-toggle rotate-180 peer-checked:rotate-0">expand_more</span>
                        </label>
                        <div id="master-data-submenu" class="ml-6 mt-1 space-y-1 border-l-2 border-orange-500 pl-4 hidden peer-checked:block">
                            <a href="<?php echo $base; ?>customer.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'customer.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">person</span>
                                <p class="text-sm font-medium leading-normal">Customer</p>
                            </a>
                            <a href="<?php echo $base; ?>user_management.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'user_management.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">manage_accounts</span>
                                <p class="text-sm font-medium leading-normal">User Management</p>
                            </a>
                            <a href="<?php echo $base; ?>vessels.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'vessels.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Vessels</p>
                            </a>
                            <a href="<?php echo $base; ?>berth.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'berth.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">anchor</span>
                                <p class="text-sm font-medium leading-normal">Berth</p>
                            </a>
                            <a href="<?php echo $base; ?>price_of_services.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'price_of_services.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">attach_money</span>
                                <p class="text-sm font-medium leading-normal">Price of Services</p>
                            </a>
                            <a href="<?php echo $base; ?>agents.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'agents.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">support_agent</span>
                                <p class="text-sm font-medium leading-normal">Agents</p>
                            </a>
                            <a href="<?php echo $base; ?>calendar.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'calendar.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">calendar_month</span>
                                <p class="text-sm font-medium leading-normal">Calendar</p>
                            </a>
                        </div>
                    </div>

                    <!-- Request Lists Submenu Section -->
                    <div class="flex flex-col gap-1">
                        <input id="request-list-toggle" type="checkbox" class="hidden peer" <?php echo $isRequestListPage ? 'checked' : ''; ?> />
                        <label for="request-list-toggle"
                            class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20">
                            <span class="flex items-center gap-3">
                                <span class="material-symbols-outlined">list_alt</span>
                                <p class="text-sm font-medium leading-normal">Operation</p>
                            </span>
                            <span
                                class="material-symbols-outlined transition-transform duration-200 expand-icon-request-list-toggle rotate-180 peer-checked:rotate-0">expand_more</span>
                        </label>
                        <div id="request-list-submenu" class="ml-6 mt-1 space-y-1 border-l-2 border-orange-500 pl-4 hidden peer-checked:block">
                            <a href="<?php echo $base; ?>marine_planner.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'marine_planner.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">sailing</span>
                                <p class="text-sm font-medium leading-normal">Marine Planner</p>
                            </a>
                            <a href="<?php echo $base; ?>marine_requests.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'marine_requests.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">directions_boat</span>
                                <p class="text-sm font-medium leading-normal">Marine Request</p>
                            </a>
                            <a href="<?php echo $base; ?>marine_endorsed.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'marine_endorsed.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">check_circle</span>
                                <p class="text-sm font-medium leading-normal">Marine Endorsed</p>
                            </a>
                            <a href="<?php echo $base; ?>port_dues_requests.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'port_dues_requests.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">anchor</span>
                                <p class="text-sm font-medium leading-normal">Port Dues</p>
                            </a>
                            <a href="<?php echo $base; ?>light_dues_requests.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'light_dues_requests.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">lightbulb</span>
                                <p class="text-sm font-medium leading-normal">Light Dues</p>
                            </a>
                            <a href="<?php echo $base; ?>port_clearance_requests.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'port_clearance_requests.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">description</span>
                                <p class="text-sm font-medium leading-normal">Port Clearance</p>
                            </a>
                            <a href="<?php echo $base; ?>marine_overtime_requests.php"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'marine_overtime_requests.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>">
                                <span class="material-symbols-outlined">schedule</span>
                                <p class="text-sm font-medium leading-normal">Marine Overtime</p>
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                <nav class="flex flex-col gap-2 pb-8">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing</p>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?php echo $current === 'bod_management.php' ? 'bg-primary text-white' : 'text-[#212529] hover:bg-primary/10 dark:text-gray-300 dark:hover:bg-primary/20'; ?>"
                        href="<?php echo $base; ?>bod_management.php">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium leading-normal">BOD Management</p>
                    </a>

            </div>
        </div>
    </div>
</aside>