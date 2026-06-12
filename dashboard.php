<?php
// Set page title and load the shared header (including auth guard)
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Aggregate counts and status breakdowns for dashboard summary cards
$totalProjects = runQuery('SELECT COUNT(*) as c FROM projects')[0]['c'];
$projectsByStatus = runQuery('SELECT status, COUNT(*) as c FROM projects GROUP BY status');
$totalTasks = runQuery('SELECT COUNT(*) as c FROM tasks')[0]['c'];
$tasksByStatus = runQuery('SELECT status, COUNT(*) as c FROM tasks GROUP BY status');
// Fetch materials that are at or below their low stock threshold
$lowStock = runQuery('SELECT * FROM materials WHERE quantity <= low_stock_threshold');

// Color mappings for project and task status bars
$statusColors = ['Ongoing' => 'bg-blue-500', 'Pending' => 'bg-yellow-500', 'In Progress' => 'bg-indigo-500', 'Completed' => 'bg-green-500', 'Not Started' => 'bg-gray-400'];
$taskStatusColors = ['In Progress' => 'bg-indigo-500', 'Completed' => 'bg-green-500', 'Not Started' => 'bg-gray-400'];
?>

<!-- Dashboard summary statistic cards row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalProjects ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Tasks</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalTasks ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Low Stock Items</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= count($lowStock) ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Projects</p>
                <!-- Count projects that are not yet completed -->
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= array_sum(array_column(array_filter($projectsByStatus, fn($p) => $p['status'] !== 'Completed'), 'c')) ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts row: project status breakdown and task status breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Horizontal bar chart showing project counts by status -->
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
                    <!-- Progress bar proportional to the highest count -->
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="<?= $statusColors[$item['status']] ?? 'bg-gray-500' ?> h-3 rounded-full transition-all duration-500" style="width: <?= ($item['c'] / $maxVal) * 100 ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Horizontal bar chart showing task counts by status -->
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
                    <!-- Progress bar proportional to the highest count -->
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="<?= $taskStatusColors[$item['status']] ?? 'bg-gray-500' ?> h-3 rounded-full transition-all duration-500" style="width: <?= ($item['c'] / $maxVal) * 100 ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Low Stock Alerts section: shows materials that need restocking -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        Low Stock Alerts
    </h3>
    <?php if (empty($lowStock)): ?>
        <!-- Empty state when all materials are sufficiently stocked -->
        <p class="text-gray-500 text-sm">All materials are well stocked.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($lowStock as $mat): ?>
                <!-- Individual low stock alert card -->
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
