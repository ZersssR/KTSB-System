<?php
require_once __DIR__ . '/../../../config/app.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get admin details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Set current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Get statistics for pending/unprocessed requests

// Marine Requests - Pending (including 'pending', 'assign', 'in progress', 'pending endorsement')
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM marine_requests WHERE status IN ('pending', 'assign', 'in progress', 'pending endorsement')");
$stmt->execute();
$marineCount = $stmt->fetch()['count'];

// Port Dues Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM light_port_requests WHERE request_type = 'port_dues' AND status = 'pending'");
$stmt->execute();
$portDuesCount = $stmt->fetch()['count'];

// Light Dues Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM light_port_requests WHERE request_type = 'light_dues' AND status = 'pending'");
$stmt->execute();
$lightDuesCount = $stmt->fetch()['count'];

// Port Clearance Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM port_clearance_requests WHERE status = 'pending'");
$stmt->execute();
$portClearanceCount = $stmt->fetch()['count'];

// Marine Overtime Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM marine_overtime_requests WHERE status = 'pending'");
$stmt->execute();
$marineOvertimeCount = $stmt->fetch()['count'];

// Crew Sign On Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM crew_sign_on_requests WHERE status = 'pending'");
$stmt->execute();
$crewSignOnCount = $stmt->fetch()['count'];

// Crew Sign Off Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM crew_sign_off_requests WHERE status = 'pending'");
$stmt->execute();
$crewSignOffCount = $stmt->fetch()['count'];

// Fuel & Water Requests - Pending
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM fuel_water_requests WHERE status = 'pending'");
$stmt->execute();
$fuelWaterCount = $stmt->fetch()['count'];

// Total Pending Requests
$totalCount = $marineCount + $portDuesCount + $lightDuesCount + $portClearanceCount + $marineOvertimeCount;

// Get Active Users Count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
$stmt->execute();
$activeUsersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get Active Agents Count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM agents WHERE status = 'active'");
$stmt->execute();
$activeAgentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link rel="icon" href="../assets/images/KTSB Logo.jpeg" type="image/png" />
    <title>Admin Dashboard</title>
    <script src="../assets/js/tailwindcss.js"></script>
    <link href="../assets/css/fonts.css" rel="stylesheet" />
    <link href="../assets/css/material-icons.css" rel="stylesheet" />
    <!-- Babel Standalone for React -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
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
        .stat-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }
    </style>
    <script src="../tab-session.js"></script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="relative flex h-screen w-full">
        <!-- Collapsible SideNavBar -->
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
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($admin['username']); ?></span>
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
            <!-- Dashboard Content -->
            <main class="relative flex-1 overflow-y-auto p-4 md:p-8">
                <!-- Notification Widget Mounting Point -->
                <div id="notification-root" class="absolute top-4 right-4 md:right-8 z-30"></div>
                <div class="mx-auto max-w-7xl">
                    <!-- Welcome Section -->
                    <div class="text-left mb-6">
                        <h2
                            class="text-[#212529] dark:text-gray-100 text-4xl font-black leading-tight tracking-[-0.033em]">
                            Welcome back, <?php echo htmlspecialchars($admin['username']); ?>!</h2>
                    </div>

                    <!-- Pending Requests Card - Full Width -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div
                            class="p-6 border-b border-orange-500 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Pending Requests
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Click on any card to view details</p>
                                </div>
                                <div
                                    class="flex flex-col items-center justify-center h-12 w-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg border border-orange-200 dark:border-orange-800/30 shadow-sm">
                                    <span class="material-symbols-outlined text-orange-600">pending_actions</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <!-- Marine - Clickable -->
                                <a href="../admin/marine_requests.php?status=pending" class="block">
                                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-3">
                                        <div class="flex flex-col justify-between h-full gap-2">
                                            <div class="flex items-start justify-between">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Marine</p>
                                                <span
                                                    class="material-symbols-outlined text-xl text-orange-600">directions_boat</span>
                                            </div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                <?php echo $marineCount; ?>
                                            </p>
                                            <p class="text-xs text-gray-400">pending requests</p>
                                        </div>
                                    </div>
                                </a>

                                <!-- Port Dues - Clickable -->
                                <a href="../admin/port_dues_requests.php?type=port_dues&status=pending" class="block">
                                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-3">
                                        <div class="flex flex-col justify-between h-full gap-2">
                                            <div class="flex items-start justify-between">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Port Dues</p>
                                                <span
                                                    class="material-symbols-outlined text-xl text-orange-600">anchor</span>
                                            </div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                <?php echo $portDuesCount; ?>
                                            </p>
                                            <p class="text-xs text-gray-400">pending requests</p>
                                        </div>
                                    </div>
                                </a>

                                <!-- Light Dues - Clickable -->
                                <a href="../admin/light_dues_requests.php?type=light_dues&status=pending" class="block">
                                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-3">
                                        <div class="flex flex-col justify-between h-full gap-2">
                                            <div class="flex items-start justify-between">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Light Dues</p>
                                                <span
                                                    class="material-symbols-outlined text-xl text-orange-600">lightbulb</span>
                                            </div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                <?php echo $lightDuesCount; ?>
                                            </p>
                                            <p class="text-xs text-gray-400">pending requests</p>
                                        </div>
                                    </div>
                                </a>

                                <!-- Port Clearance - Clickable -->
                                <a href="../admin/port_clearance_requests.php?status=pending" class="block">
                                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-3">
                                        <div class="flex flex-col justify-between h-full gap-2">
                                            <div class="flex items-start justify-between">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Port Clearance</p>
                                                <span
                                                    class="material-symbols-outlined text-xl text-orange-600">description</span>
                                            </div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                <?php echo $portClearanceCount; ?>
                                            </p>
                                            <p class="text-xs text-gray-400">pending requests</p>
                                        </div>
                                    </div>
                                </a>

                                <!-- Marine Overtime - Clickable -->
                                <a href="../admin/marine_overtime_requests.php?status=pending" class="block">
                                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-3">
                                        <div class="flex flex-col justify-between h-full gap-2">
                                            <div class="flex items-start justify-between">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Marine OT</p>
                                                <span
                                                    class="material-symbols-outlined text-xl text-orange-600">schedule</span>
                                            </div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                <?php echo $marineOvertimeCount; ?>
                                            </p>
                                            <p class="text-xs text-gray-400">pending requests</p>
                                        </div>
                                    </div>
                                </a>

                                <!-- Total Pending - Clickable to all pending -->
                                <a class="block">
                                    <div class="stat-card bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-100 dark:border-orange-800/30 p-3">
                                        <div class="flex flex-col justify-between h-full gap-2">
                                            <div class="flex items-start justify-between">
                                                <p
                                                    class="text-xs font-semibold text-orange-600 dark:text-orange-400 uppercase tracking-wider">
                                                    Total Pending</p>
                                                <span
                                                    class="material-symbols-outlined text-xl text-orange-600">analytics</span>
                                            </div>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                <?php echo $totalCount; ?>
                                            </p>
                                            <p class="text-xs text-orange-600 dark:text-orange-400">awaiting action</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Active Personnel Card - Full Width -->
                    <div class="mt-8">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-orange-500 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Active
                                            Personnel</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Currently active users and agents</p>
                                    </div>
                                    <span class="material-symbols-outlined text-xl text-gray-400">group</span>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                                    <!-- Active Users Stat - Clickable -->
                                    <a href="../users.php?status=active" class="block">
                                        <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-6 flex items-center justify-between">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Users</p>
                                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">
                                                    <?php echo $activeUsersCount; ?>
                                                </p>
                                            </div>
                                            <div class="h-16 w-16 bg-gray-50/50 dark:bg-gray-700/50 rounded-full flex items-center justify-center text-primary shadow-sm border border-gray-100 dark:border-gray-600">
                                                <span class="material-symbols-outlined text-3xl">person</span>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- Active Agents Stat - Clickable -->
                                    <a href="../agents.php?status=active" class="block">
                                        <div class="stat-card bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 p-6 flex items-center justify-between">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Agents</p>
                                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">
                                                    <?php echo $activeAgentsCount; ?>
                                                </p>
                                            </div>
                                            <div class="h-16 w-16 bg-orange-50 dark:bg-orange-900/10 rounded-full flex items-center justify-center text-orange-600 shadow-sm border border-orange-100 dark:border-orange-800/20">
                                                <span class="material-symbols-outlined text-3xl">support_agent</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
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
        updateTime(); // initial

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

    <!-- React Notification Widget Script (unchanged) -->
    <script type="text/babel" data-type="module">
        import React, { useState, useEffect, useRef } from 'https://esm.sh/react@18.2.0';
        import { createRoot } from 'https://esm.sh/react-dom@18.2.0/client';
        import { Bell, X, CheckCircle2, XCircle, FileText, ChevronRight, Clock } from 'https://esm.sh/lucide-react@0.292.0';

        const NotificationWidget = ({ notifications, onDismiss, onClearAll }) => {
            const [isOpen, setIsOpen] = useState(false);
            const widgetRef = useRef(null);

            useEffect(() => {
                const handleClickOutside = (event) => {
                    if (widgetRef.current && !widgetRef.current.contains(event.target)) {
                        setIsOpen(false);
                    }
                };

                document.addEventListener('mousedown', handleClickOutside);
                return () => {
                    document.removeEventListener('mousedown', handleClickOutside);
                };
            }, []);

            // Modern Icon Logic
            const getIcon = (type) => {
                switch (type) {
                    case 'marine': return <span className="material-symbols-outlined text-orange-500">directions_boat</span>;
                    case 'port_dues': return <span className="material-symbols-outlined text-blue-600">anchor</span>;
                    case 'light_dues': return <span className="material-symbols-outlined text-yellow-500">lightbulb</span>;
                    case 'port_clearance': return <span className="material-symbols-outlined text-purple-500">description</span>;
                    case 'marine_overtime': return <span className="material-symbols-outlined text-red-500">schedule</span>;
                    case 'fuel_water': return <span className="material-symbols-outlined text-blue-500">water_drop</span>;
                    case 'crew_sign_on': return <span className="material-symbols-outlined text-green-500">person_add</span>;
                    case 'crew_sign_off': return <span className="material-symbols-outlined text-red-500">person_remove</span>;
                    case 'light_port': return <span className="material-symbols-outlined text-yellow-500">lightbulb</span>;
                    default: return <Bell className="w-5 h-5 text-gray-500" />;
                }
            };

            return (
                <div
                    ref={widgetRef}
                    className={`bg-white border border-gray-200 shadow-xl overflow-hidden transition-all duration-500 ease-in-out font-sans ${isOpen ? 'w-96 rounded-2xl' : 'w-48 rounded-2xl hover:bg-gray-50 cursor-pointer'}`}
                    style={{ maxHeight: isOpen ? '600px' : '64px' }}
                    onClick={() => !isOpen && setIsOpen(true)}
                >
                    {/* Header with Modern Icon */}
                    <div className={`flex items-center whitespace-nowrap h-full ${isOpen ? 'justify-between p-3 border-b border-gray-100 bg-gray-50' : 'p-3 pr-4 gap-3'}`}>
                        <div className={`flex items-center transition-all duration-300 ${isOpen ? 'gap-3 px-1' : 'gap-3'}`}>

                            {/* Animated Orange Icon Container */}
                            <div className={`relative flex items-center justify-center w-10 h-10 rounded-full flex-shrink-0 shadow-sm border ${isOpen ? 'bg-orange-50 border-orange-100 text-orange-600 ring-2 ring-orange-50 ring-offset-1' : 'bg-white border-gray-100 text-gray-500 hover:bg-gray-50 hover:border-gray-200'} transition-all duration-300`}>
                                <Bell className={`w-5 h-5 ${isOpen ? 'fill-current' : ''}`} strokeWidth={2} />
                                {!isOpen && notifications.length > 0 && (
                                    <span className="absolute top-2 right-2.5 flex h-2.5 w-2.5">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                        <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-500 border border-white"></span>
                                    </span>
                                )}
                            </div>

                            <div className="flex flex-col overflow-hidden">
                                <span className={`font-semibold text-sm truncate ${isOpen ? 'text-gray-900' : 'text-gray-700'}`}>
                                    {notifications.length} {isOpen ? 'New Requests' : 'Notifications'}
                                </span>
                                {isOpen && <span className="text-xs text-gray-500 truncate">Review pending items</span>}
                            </div>
                        </div>

                        {isOpen ? (
                            <button onClick={(e) => { e.stopPropagation(); setIsOpen(false); }} className="p-1 rounded-full hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                                <X className="w-5 h-5" />
                            </button>
                        ) : (
                            <ChevronRight className="w-4 h-4 text-gray-400 flex-shrink-0 ml-auto" />
                        )}
                    </div>

                    {/* List Content */}
                    <div className={`flex flex-col bg-white transition-all duration-300 ${isOpen ? 'opacity-100 visible' : 'opacity-0 invisible'}`}>
                        <div className="w-96 max-h-[320px] overflow-y-auto">
                            {notifications.length === 0 ? (
                                <div className="flex flex-col items-center justify-center py-12 text-gray-400"><CheckCircle2 className="w-12 h-12 mb-2 opacity-20" /><p className="text-sm">No new notifications</p></div>
                            ) : (
                                notifications.map((note) => (
                                    <div key={note.id} className="group relative flex gap-4 p-4 border-b border-gray-50 hover:bg-gray-50 transition-colors cursor-pointer" onClick={() => window.location.href = '../' + note.link}>
                                        <div className="mt-1 flex-shrink-0">{getIcon(note.type)}</div>
                                        <div className="flex-1 pr-6">
                                            <h4 className="text-sm font-semibold leading-none mb-1 text-gray-900">
                                                {note.type.replace('_', ' ').toUpperCase()}
                                                {(() => {
                                                    const match = note.link ? note.link.match(/[?&]id=([^&]+)/) : null;
                                                    return match ? ` (${match[1]})` : '';
                                                })()}
                                            </h4>
                                            <p className="text-sm text-gray-600 mb-2 leading-snug">{note.message}</p>
                                            <div className="flex items-center gap-1 text-xs text-gray-400 font-medium"><Clock className="w-3 h-3" />{new Date(note.created_at).toLocaleString()}</div>
                                        </div>
                                        <button onClick={(e) => onDismiss(note.id, e)} className="absolute top-2 right-2 p-1 text-gray-300 opacity-0 group-hover:opacity-100 hover:text-orange-500 transition-all"><X className="w-3 h-3" /></button>
                                    </div>
                                ))
                            )}
                        </div>
                        {notifications.length > 0 && isOpen && (
                            <div className="p-3 bg-gray-50 border-t border-gray-100 text-center">
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onClearAll(e);
                                    }}
                                    className="text-xs font-semibold text-gray-500 hover:text-orange-600 transition-colors uppercase tracking-wider"
                                >
                                    Clear All
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            );
        };

        const App = () => {
            const [notifications, setNotifications] = useState([]);

            const fetchNotifications = async () => {
                try {
                    const response = await fetch('api/get_notifications.php');
                    const data = await response.json();
                    if (data.success) {
                        setNotifications(data.notifications);
                    }
                } catch (error) {
                    console.error('Error fetching notifications:', error);
                }
            };

            useEffect(() => {
                fetchNotifications();
                const interval = setInterval(fetchNotifications, 30000); // Poll every 30 seconds
                return () => clearInterval(interval);
            }, []);

            const handleDismiss = async (id, e) => {
                e.stopPropagation();
                try {
                    const response = await fetch('api/mark_notification_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await response.json();
                    if (data.success) {
                        setNotifications(prev => prev.filter(n => n.id !== id));
                    }
                } catch (error) {
                    console.error('Error dismissing notification:', error);
                }
            };

            const handleClearAll = async (e) => {
                e.stopPropagation();
                try {
                    const response = await fetch('api/mark_all_notifications_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.success) {
                        setNotifications([]);
                    }
                } catch (error) {
                    console.error('Error clearing all notifications:', error);
                }
            };

            return (
                <div className="relative z-50 w-48 h-16">
                    <div className="absolute top-0 right-0">
                        <NotificationWidget notifications={notifications} onDismiss={handleDismiss} onClearAll={handleClearAll} />
                    </div>
                </div>
            );
        };

        const root = createRoot(document.getElementById('notification-root'));
        root.render(<App />);
    </script>
</body>

</html>