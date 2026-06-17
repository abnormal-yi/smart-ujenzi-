<?php
$pageTitle = 'Fundi Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['fundi']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

$pendingTasks = runQuery("SELECT COUNT(*) as c FROM tasks WHERE fundi_id = ? AND status != 'Completed'", [$userId])[0]['c'];
$completedTasks = runQuery("SELECT COUNT(*) as c FROM tasks WHERE fundi_id = ? AND status = 'Completed'", [$userId])[0]['c'];
$overdueTasks = runQuery("SELECT COUNT(*) as c FROM tasks WHERE fundi_id = ? AND deadline < CURDATE() AND status != 'Completed'", [$userId])[0]['c'];
$activeProjects = runQuery("SELECT COUNT(DISTINCT p.id) as c FROM projects p JOIN tasks t ON t.project_id = p.id WHERE t.fundi_id = ?", [$userId])[0]['c'];

$upcomingTasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ? AND t.status != 'Completed' ORDER BY t.deadline ASC LIMIT 10", [$userId]);
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Tasks</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $pendingTasks ?></p>
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
                <p class="text-3xl font-bold text-green-600 mt-1"><?= $completedTasks ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Overdue</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= $overdueTasks ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Projects</p>
                <p class="text-3xl font-bold text-blue-600 mt-1"><?= $activeProjects ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Upcoming Tasks</h3>
    <?php if (empty($upcomingTasks)): ?>
        <p class="text-gray-500 text-sm">No upcoming tasks.</p>
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
                    <?php foreach ($upcomingTasks as $t): ?>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($t['name'] ?? $t['title']) ?></td>
                        <td class="py-3 text-gray-600"><?= htmlspecialchars($t['project_name']) ?></td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?= $t['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($t['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>"><?= $t['status'] ?></span>
                        </td>
                        <td class="py-3 <?= isset($t['deadline']) && strtotime($t['deadline']) < time() ? 'text-red-600 font-medium' : 'text-gray-500' ?>"><?= isset($t['deadline']) ? date('M j, Y', strtotime($t['deadline'])) : 'N/A' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
