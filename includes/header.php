<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartUjenzi - Construction Management</title>
    <!-- Tailwind CSS via CDN for utility-first styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom application styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-50 flex">

<?php
// Load shared functions and enforce authentication before rendering any page
require_once __DIR__ . '/functions.php';
requireAuth();

// Extract session data for display in the header
$role = $_SESSION['role'] ?? '';
$userName = $_SESSION['user_name'] ?? 'User';

// Fetch notifications and unread count for the notification dropdown
$notifications = getNotifications($_SESSION['user_id']);
$unreadCount = getUnreadCount($_SESSION['user_id']);

// Define the sidebar navigation items with role-based access control (Heroicons SVGs)
$navItems = [
    ['name' => 'Dashboard', 'path' => 'dashboard.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'roles' => ['admin', 'manager', 'supervisor', 'constructor', 'customer']],
    ['name' => 'Customer Requests', 'path' => 'customer_requests.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>', 'roles' => ['admin', 'manager', 'customer']],
    ['name' => 'Projects', 'path' => 'projects.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', 'roles' => ['admin', 'manager']],
    ['name' => 'Tasks', 'path' => 'tasks.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>', 'roles' => ['admin', 'manager', 'supervisor', 'constructor']],
    ['name' => 'Mafundi & Equipment', 'path' => 'workers.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>', 'roles' => ['admin', 'manager', 'supervisor']],
    ['name' => 'Materials', 'path' => 'materials.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'roles' => ['admin', 'manager', 'supervisor']],
    ['name' => 'Discussions', 'path' => 'messages.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>', 'roles' => ['admin', 'manager', 'supervisor']],
    ['name' => 'Reports', 'path' => 'reports.php', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'roles' => ['admin', 'manager']],
];

// Filter nav items to only those the current user's role can access
$filteredNav = array_filter($navItems, fn($item) => in_array($role, $item['roles']));
?>

<!-- Sidebar navigation panel (collapsible on mobile) -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-white transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto transition-transform duration-300">
    <div class="flex items-center justify-center h-16 border-b border-gray-800">
        <h1 class="text-xl font-bold tracking-wider">SMART UJENZI</h1>
    </div>
    <nav class="p-4 space-y-2">
        <?php foreach ($filteredNav as $item): ?>
            <!-- Sidebar link: highlights current page based on filename -->
            <a href="<?= $item['path'] ?>"
               class="flex items-center p-3 rounded-lg hover:bg-slate-800 text-gray-300 hover:text-white transition-colors <?= basename($_SERVER['PHP_SELF']) === $item['path'] ? 'bg-slate-800 text-white' : '' ?>">
                <span class="mr-3 flex-shrink-0"><?= $item['icon'] ?></span>
                <?= $item['name'] ?>
            </a>
        <?php endforeach; ?>
        <!-- Logout link styled distinctly at the bottom of sidebar -->
        <a href="logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-900/50 text-red-400 w-full transition-colors mt-8">
            <span class="mr-3 flex-shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></span> Logout
        </a>
    </nav>
</aside>

<!-- Dark overlay behind sidebar when open on mobile screens -->
<div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- Main content area: header bar + page content -->
<div class="flex-1 flex flex-col min-w-0">
    <!-- Top header bar with hamburger menu, notifications, and user avatar -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-8">
        <!-- Hamburger menu button visible only on mobile -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-500 hover:bg-gray-100 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <!-- Right-side header icons: bell + user avatar -->
        <div class="flex items-center space-x-4 ml-auto relative">
            <!-- Notification bell with unread indicator dot -->
            <div class="relative">
                <button onclick="toggleNotifications()" class="p-2 text-gray-500 hover:bg-gray-100 rounded-full relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <?php if ($unreadCount > 0): ?>
                        <!-- Red dot indicating unread notifications -->
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    <?php endif; ?>
                </button>

                <!-- Notification dropdown panel -->
                <div id="notif-dropdown" class="hidden absolute top-12 right-0 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-96 flex flex-col">
                    <div class="p-3 border-b border-gray-100 font-bold text-gray-800 flex justify-between items-center">
                        <span>Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full"><?= $unreadCount ?> unread</span>
                        <?php endif; ?>
                    </div>
                    <div class="overflow-y-auto flex-1">
                        <?php if (empty($notifications)): ?>
                            <!-- Empty state when no notifications exist -->
                            <div class="p-4 text-center text-gray-500 text-sm">No notifications</div>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <!-- Clickable notification item: marks as read on click -->
                                <div onclick="markRead(<?= $n['id'] ?>)" class="p-3 border-b border-gray-50 cursor-pointer hover:bg-gray-50 transition-colors <?= $n['is_read'] ? 'opacity-50' : 'bg-blue-50/30' ?>">
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 <?= $n['is_read'] ? 'text-gray-400' : 'text-blue-500' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                        <div>
                                            <!-- Notification message text and timestamp -->
                                            <p class="text-sm <?= $n['is_read'] ? 'text-gray-600' : 'text-gray-800 font-medium' ?>"><?= htmlspecialchars($n['message']) ?></p>
                                            <span class="text-xs text-gray-400"><?= date('M j, Y g:i A', strtotime($n['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User avatar circle showing first 2 letters of name -->
            <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-semibold text-sm">
                <?= strtoupper(substr($userName, 0, 2)) ?>
            </div>
        </div>
    </header>

    <!-- Main content area: page title, flash messages, then child page content -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto relative z-0">
        <!-- Dynamic page title set by each page's $pageTitle variable -->
        <?php if (isset($pageTitle)): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $pageTitle ?></h2>
        <?php endif; ?>
        <!-- Flash message banner: success (green) or error (red) -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-4 rounded-lg text-sm <?= $_SESSION['flash']['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                <?= $_SESSION['flash']['message'] ?>
            </div>
        <?php unset($_SESSION['flash']); endif; ?>
