<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$totalProjects = runQuery('SELECT COUNT(*) as c FROM projects')[0]['c'];
$projectsByStatus = runQuery('SELECT status, COUNT(*) as c FROM projects GROUP BY status');
$totalTasks = runQuery('SELECT COUNT(*) as c FROM tasks')[0]['c'];
$tasksByStatus = runQuery('SELECT status, COUNT(*) as c FROM tasks GROUP BY status');
$lowStock = runQuery('SELECT * FROM materials WHERE quantity <= low_stock_threshold');

$statusColors = ['Ongoing' => 'bg-blue-500', 'Pending' => 'bg-yellow-500', 'In Progress' => 'bg-indigo-500', 'Completed' => 'bg-green-500', 'Not Started' => 'bg-gray-400'];
$taskStatusColors = ['In Progress' => 'bg-indigo-500', 'Completed' => 'bg-green-500', 'Not Started' => 'bg-gray-400'];
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalProjects ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-2xl">🏗️</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Tasks</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalTasks ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600 text-2xl">✅</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Low Stock Items</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= count($lowStock) ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600 text-2xl">⚠️</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= array_sum(array_column(array_filter($projectsByStatus, fn($p) => $p['status'] !== 'Completed'), 'c')) ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 text-2xl">📈</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Projects by Status -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Projects by Status</h3>
        <div class="space-y-3">
            <?php $maxVal = max(array_column($projectsByStatus, 'c') ?: [1]); ?>
            <?php foreach ($projectsByStatus as $item): ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600"><?= $item['status'] ?></span>
                        <span class="font-semibold"><?= $item['c'] ?></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="<?= $statusColors[$item['status']] ?? 'bg-gray-500' ?> h-3 rounded-full transition-all duration-500" style="width: <?= ($item['c'] / $maxVal) * 100 ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tasks by Status -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Tasks by Status</h3>
        <div class="space-y-3">
            <?php $maxVal = max(array_column($tasksByStatus, 'c') ?: [1]); ?>
            <?php foreach ($tasksByStatus as $item): ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600"><?= $item['status'] ?></span>
                        <span class="font-semibold"><?= $item['c'] ?></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="<?= $taskStatusColors[$item['status']] ?? 'bg-gray-500' ?> h-3 rounded-full transition-all duration-500" style="width: <?= ($item['c'] / $maxVal) * 100 ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Low Stock Alerts -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">⚠️ Low Stock Alerts</h3>
    <?php if (empty($lowStock)): ?>
        <p class="text-gray-500 text-sm">All materials are well stocked.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
