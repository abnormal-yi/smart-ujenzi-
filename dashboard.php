<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

if ($role === 'super_admin' || $role === 'admin'):
    $stats = runQuery("SELECT
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM projects) as total_projects,
        (SELECT COUNT(*) FROM customer_requests) as total_requests,
        (SELECT COUNT(*) FROM companies) as total_companies,
        (SELECT COUNT(*) FROM users WHERE role='client') as total_clients,
        (SELECT COUNT(*) FROM users WHERE role='fundi') as total_fundi,
        (SELECT COUNT(*) FROM materials WHERE quantity <= low_stock_threshold) as low_stock
    ")[0];
    $recentRequests = runQuery("SELECT r.*, c.name as company_name FROM customer_requests r LEFT JOIN companies c ON r.company_id = c.id ORDER BY r.id DESC LIMIT 10");
    $lowStock = runQuery("SELECT * FROM materials WHERE quantity <= low_stock_threshold");
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_users'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_projects'] ?></p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Requests</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_requests'] ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Companies</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_companies'] ?></p>
            </div>
            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Clients</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_clients'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Fundi</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_fundi'] ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Low Stock Items</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= $stats['low_stock'] ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Requests</h3>
        <?php if (empty($recentRequests)): ?>
            <p class="text-gray-500 text-sm">No requests yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="pb-2 font-medium">Company</th>
                            <th class="pb-2 font-medium">Status</th>
                            <th class="pb-2 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRequests as $req): ?>
                        <tr class="border-b border-gray-50">
                            <td class="py-2 text-gray-800"><?= htmlspecialchars($req['company_name'] ?? 'N/A') ?></td>
                            <td class="py-2">
                                <span class="px-2 py-1 text-xs rounded-full <?= $req['status'] === 'Approved' ? 'bg-green-100 text-green-700' : ($req['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') ?>"><?= $req['status'] ?></span>
                            </td>
                            <td class="py-2 text-gray-500"><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Low Stock Alerts
        </h3>
        <?php if (empty($lowStock)): ?>
            <p class="text-gray-500 text-sm">All materials are well stocked.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($lowStock as $mat): ?>
                <div class="p-4 bg-red-50 border border-red-100 rounded-lg">
                    <p class="font-semibold text-red-800"><?= htmlspecialchars($mat['name']) ?></p>
                    <p class="text-sm text-red-600">Stock: <?= $mat['quantity'] ?> <?= $mat['unit'] ?></p>
                    <p class="text-xs text-red-500">Threshold: <?= $mat['low_stock_threshold'] ?> <?= $mat['unit'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($role === 'project_manager'):
    $myProjects = runQuery("SELECT * FROM projects WHERE project_manager_id = ?", [$userId]);
    $myRequests = runQuery("SELECT * FROM customer_requests WHERE assigned_pm_id = ?", [$userId]);
    $pendingCount = count(array_filter($myRequests, fn($r) => $r['status'] === 'Pending'));
    $upcomingDeadlines = array_filter($myProjects, fn($p) => isset($p['end_date']) && strtotime($p['end_date']) <= strtotime('+7 days') && strtotime($p['end_date']) >= time());
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">My Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= count($myProjects) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Requests</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $pendingCount ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Upcoming Deadlines</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= count($upcomingDeadlines) ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">My Projects</h3>
    <?php if (empty($myProjects)): ?>
        <p class="text-gray-500 text-sm">No projects assigned to you yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="pb-3 font-medium">Project Name</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myProjects as $p): ?>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($p['name']) ?></td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?= $p['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($p['status'] === 'Ongoing' ? 'bg-blue-100 text-blue-700' : ($p['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) ?>"><?= $p['status'] ?></span>
                        </td>
                        <td class="py-3 text-gray-500"><?= isset($p['end_date']) ? date('M j, Y', strtotime($p['end_date'])) : 'N/A' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php elseif ($role === 'fundi'):
    $myTasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ? ORDER BY t.deadline", [$userId]);
    $completedCount = runQuery("SELECT COUNT(*) as c FROM tasks WHERE fundi_id = ? AND status='Completed'", [$userId])[0]['c'];
    $pendingTasks = array_filter($myTasks, fn($t) => $t['status'] !== 'Completed');
    $upcomingDeadlines = array_filter($myTasks, fn($t) => $t['status'] !== 'Completed' && isset($t['deadline']) && strtotime($t['deadline']) <= strtotime('+7 days') && strtotime($t['deadline']) >= time());
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Tasks</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= count($pendingTasks) ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-3xl font-bold text-green-600 mt-1"><?= $completedCount ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Upcoming Deadlines</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= count($upcomingDeadlines) ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">My Tasks</h3>
    <?php if (empty($myTasks)): ?>
        <p class="text-gray-500 text-sm">No tasks assigned to you yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="pb-3 font-medium">Task</th>
                        <th class="pb-3 font-medium">Project</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Deadline</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myTasks as $t): ?>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($t['title']) ?></td>
                        <td class="py-3 text-gray-600"><?= htmlspecialchars($t['project_name']) ?></td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?= $t['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($t['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>"><?= $t['status'] ?></span>
                        </td>
                        <td class="py-3 text-gray-500"><?= isset($t['deadline']) ? date('M j, Y', strtotime($t['deadline'])) : 'N/A' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php elseif ($role === 'client'):
    $myProjects = runQuery("SELECT * FROM projects WHERE customer_id = ?", [$userId]);
    $myRequests = runQuery("SELECT * FROM customer_requests WHERE customer_id = ? ORDER BY id DESC", [$userId]);
    $companyInfo = runQuery("SELECT * FROM companies WHERE id = (SELECT company_id FROM users WHERE id = ?)", [$userId])[0] ?? null;
    $pendingRequests = count(array_filter($myRequests, fn($r) => $r['status'] === 'Pending'));
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">My Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= count($myProjects) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Requests</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $pendingRequests ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Projects</p>
                <p class="text-3xl font-bold text-green-600 mt-1"><?= count(array_filter($myProjects, fn($p) => $p['status'] !== 'Completed')) ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
    </div>
</div>

<?php if ($companyInfo): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">My Company</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Company Name</p>
            <p class="text-gray-800 font-medium"><?= htmlspecialchars($companyInfo['name']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Email</p>
            <p class="text-gray-800 font-medium"><?= htmlspecialchars($companyInfo['email'] ?? 'N/A') ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Phone</p>
            <p class="text-gray-800 font-medium"><?= htmlspecialchars($companyInfo['phone'] ?? 'N/A') ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Location</p>
            <p class="text-gray-800 font-medium"><?= htmlspecialchars($companyInfo['location'] ?? 'N/A') ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">My Requests</h3>
    <?php if (empty($myRequests)): ?>
        <p class="text-gray-500 text-sm">No requests submitted yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="pb-3 font-medium">Description</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myRequests as $req): ?>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-800"><?= htmlspecialchars(mb_substr($req['description'], 0, 60)) ?><?= strlen($req['description']) > 60 ? '...' : '' ?></td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?= $req['status'] === 'Approved' ? 'bg-green-100 text-green-700' : ($req['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') ?>"><?= $req['status'] ?></span>
                        </td>
                        <td class="py-3 text-gray-500"><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
