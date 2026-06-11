<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager']);
require_once __DIR__ . '/includes/header.php';

$projects = runQuery('SELECT p.*, u.name as manager_name FROM projects p LEFT JOIN users u ON p.manager_id = u.id');
$tasks = runQuery('SELECT t.*, p.name as project_name FROM tasks t LEFT JOIN projects p ON t.project_id = p.id');
$materials = runQuery('SELECT * FROM materials');
$resources = runQuery('SELECT * FROM resources');
$lowStock = array_filter($materials, fn($m) => $m['quantity'] <= $m['low_stock_threshold']);

$overdue = [];
foreach ($tasks as $t) {
    if ($t['deadline'] && $t['status'] !== 'Completed' && $t['deadline'] < date('Y-m-d')) {
        $overdue[] = $t;
    }
}
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Summary</h3>
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Total Projects</span>
                <span class="font-bold"><?= count($projects) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Total Tasks</span>
                <span class="font-bold"><?= count($tasks) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Materials Tracked</span>
                <span class="font-bold"><?= count($materials) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Total Workers & Equipment</span>
                <span class="font-bold"><?= count($resources) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Low Stock Items</span>
                <span class="font-bold text-red-600"><?= count($lowStock) ?></span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-600">Overdue Tasks</span>
                <span class="font-bold text-red-600"><?= count($overdue) ?></span>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">⚠️ Overdue Tasks</h3>
        <?php if (empty($overdue)): ?>
            <p class="text-gray-500 text-sm">No overdue tasks. All tasks are on track!</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($overdue as $t): ?>
                    <div class="p-3 bg-red-50 border border-red-100 rounded-lg">
                        <p class="font-medium text-red-800"><?= htmlspecialchars($t['name']) ?></p>
                        <p class="text-xs text-red-600">Project: <?= htmlspecialchars($t['project_name'] ?? '—') ?> | Deadline: <?= $t['deadline'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Projects Detail Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">🏗️ Projects Overview</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Project</th>
                    <th class="pb-3 font-semibold text-gray-600">Manager</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <?php
                    $totalTasks = runQuery('SELECT COUNT(*) as c FROM tasks WHERE project_id = ?', [$p['id']])[0]['c'];
                    $completedTasks = runQuery('SELECT COUNT(*) as c FROM tasks WHERE project_id = ? AND status = "Completed"', [$p['id']])[0]['c'];
                    $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                ?>
                <tr class="border-b border-gray-100">
                    <td class="py-3 font-medium"><?= htmlspecialchars($p['name']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($p['manager_name'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $p['status'] === 'Completed' ? 'badge-green' : ($p['status'] === 'Pending' ? 'badge-yellow' : 'badge-blue') ?>"><?= $p['status'] ?></span>
                    </td>
                    <td class="py-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-100 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: <?= $progress ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-500"><?= $progress ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Low Stock Report -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">📦 Materials Stock Report</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Material</th>
                    <th class="pb-3 font-semibold text-gray-600">Current Stock</th>
                    <th class="pb-3 font-semibold text-gray-600">Unit</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $m): ?>
                <tr class="border-b border-gray-100">
                    <td class="py-3"><?= htmlspecialchars($m['name']) ?></td>
                    <td class="py-3 font-medium"><?= $m['quantity'] ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($m['unit']) ?></td>
                    <td class="py-3">
                        <?php if ($m['quantity'] <= $m['low_stock_threshold']): ?>
                            <span class="badge badge-red">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-green">In Stock</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
