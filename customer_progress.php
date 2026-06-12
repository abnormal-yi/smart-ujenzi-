<?php
$pageTitle = 'My Project Progress';
require_once __DIR__ . '/includes/functions.php';
requireRole(['customer']);
require_once __DIR__ . '/includes/header.php';

$userId = $_SESSION['user_id'];

// Get all projects belonging to this customer
$myProjects = runQuery('SELECT * FROM projects WHERE customer_id = ? ORDER BY created_at DESC', [$userId]);

// Get payments for all customer projects
$payments = runQuery('SELECT p.*, pr.name as project_name FROM payments p JOIN projects pr ON p.project_id = pr.id WHERE pr.customer_id = ? ORDER BY p.payment_date DESC', [$userId]);

// Get media for all customer projects
$media = runQuery('SELECT pm.*, u.name as uploaded_by_name, pr.name as project_name, t.name as task_name FROM project_media pm JOIN users u ON pm.uploaded_by = u.id JOIN projects pr ON pm.project_id = pr.id LEFT JOIN tasks t ON pm.task_id = t.id WHERE pr.customer_id = ? ORDER BY pm.created_at DESC', [$userId]);

// Get all tasks for customer projects
$allTasks = runQuery('SELECT t.*, pr.name as project_name FROM tasks t JOIN projects pr ON t.project_id = pr.id WHERE pr.customer_id = ? ORDER BY t.deadline ASC', [$userId]);

// Handle file upload from supervisor (also allow viewing, uploaded via tasks.php)
$projectStatusColors = ['Ongoing' => 'bg-blue-500', 'Pending' => 'bg-yellow-500', 'In Progress' => 'bg-indigo-500', 'Completed' => 'bg-green-500', 'On Hold' => 'bg-red-500'];
$taskStatusColors = ['In Progress' => 'bg-indigo-500', 'Completed' => 'bg-green-500', 'Not Started' => 'bg-gray-400', 'On Hold' => 'bg-red-400'];
?>

<!-- Project Progress Cards -->
<?php if (empty($myProjects)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    <h3 class="text-xl font-bold text-gray-800 mb-2">No Projects Yet</h3>
    <p class="text-gray-500">Submit a project request to get started.</p>
    <a href="customer_requests.php" class="inline-block mt-4 px-6 py-2 bg-yellow-500 text-black font-semibold rounded-xl">Submit Request</a>
</div>
<?php else: ?>
<div class="grid gap-6 mb-8">
    <?php foreach ($myProjects as $proj):
        $projTasks = array_filter($allTasks, fn($t) => $t['project_id'] == $proj['id']);
        $total = count($projTasks);
        $done = count(array_filter($projTasks, fn($t) => $t['status'] === 'Completed'));
        $prog = $total > 0 ? round(($done / $total) * 100) : 0;
        $projMedia = array_filter($media, fn($m) => $m['project_id'] == $proj['id']);
        $projPayments = array_filter($payments, fn($p) => $p['project_id'] == $proj['id']);
        $totalPaid = array_sum(array_column($projPayments, 'amount'));
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($proj['name']) ?></h3>
                    <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($proj['description']) ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold self-start
                    <?= $proj['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($proj['status'] === 'Ongoing' || $proj['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') ?>">
                    <?= $proj['status'] ?>
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Overall Progress</span>
                    <span class="font-semibold"><?= $prog ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-yellow-500 h-3 rounded-full transition-all" style="width: <?= $prog ?>%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1"><?= $done ?> of <?= $total ?> tasks completed</p>
            </div>

            <!-- Tasks List -->
            <?php if ($total > 0): ?>
            <div class="border-t border-gray-100 pt-4 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Tasks</h4>
                <div class="space-y-2">
                    <?php foreach ($projTasks as $task): ?>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full <?= $task['status'] === 'Completed' ? 'bg-green-500' : ($task['status'] === 'In Progress' ? 'bg-blue-500' : 'bg-gray-400') ?>"></span>
                            <span class="<?= $task['status'] === 'Completed' ? 'text-gray-500 line-through' : 'text-gray-800' ?>"><?= htmlspecialchars($task['name']) ?></span>
                        </div>
                        <span class="text-xs text-gray-400"><?= $task['status'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Media Gallery -->
            <?php $projectMedia = array_filter($media, fn($m) => $m['project_id'] == $proj['id']); ?>
            <?php if (!empty($projectMedia)): ?>
            <div class="border-t border-gray-100 pt-4 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Site Photos (<?= count($projectMedia) ?>)</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <?php foreach (array_slice($projectMedia, 0, 6) as $m): ?>
                    <a href="<?= $m['file_path'] ?>" target="_blank" class="block aspect-video bg-gray-100 rounded-lg overflow-hidden hover:opacity-90 transition-opacity">
                        <img src="<?= $m['file_path'] ?>" alt="<?= htmlspecialchars($m['caption'] ?? 'Site photo') ?>" class="w-full h-full object-cover">
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payments -->
            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-gray-700">Payments</h4>
                    <span class="text-sm font-bold text-green-600">TZS <?= number_format($totalPaid) ?></span>
                </div>
                <?php if (!empty($projPayments)): ?>
                <div class="space-y-1">
                    <?php foreach ($projPayments as $pmt): ?>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span><?= htmlspecialchars($pmt['description']) ?></span>
                        <span>TZS <?= number_format($pmt['amount']) ?> — <?= date('d M Y', strtotime($pmt['payment_date'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-xs text-gray-400">No payments recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
