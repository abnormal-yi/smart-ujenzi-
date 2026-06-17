<?php
$pageTitle = 'Project Progress';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    runQuery("UPDATE projects SET status = ? WHERE id = ? AND project_manager_id = ?", [$_POST['status'], (int)$_POST['project_id'], $userId]);
    $success = 'Project status updated!';
}

$projects = runQuery("SELECT p.*, (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as total_tasks, (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status='Completed') as done_tasks FROM projects p WHERE p.project_manager_id = ? ORDER BY p.status, p.start_date DESC", [$userId]);
$statuses = ['Pending', 'Ongoing', 'In Progress', 'Completed', 'On Hold'];
?>
<?php if (isset($success)): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
<?php endif; ?>

<?php if (empty($projects)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    <h3 class="text-xl font-bold text-gray-800 mb-2">No Projects</h3>
    <p class="text-gray-500">You have no projects assigned to you yet.</p>
</div>
<?php else: ?>
<div class="grid gap-6">
    <?php foreach ($projects as $proj):
        $total = (int)$proj['total_tasks'];
        $done = (int)$proj['done_tasks'];
        $prog = $total > 0 ? round(($done / $total) * 100) : 0;
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($proj['name']) ?></h3>
                    <?php if ($proj['description']): ?>
                        <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($proj['description']) ?></p>
                    <?php endif; ?>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold self-start
                    <?= $proj['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($proj['status'] === 'Ongoing' || $proj['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : ($proj['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) ?>">
                    <?= $proj['status'] ?>
                </span>
            </div>

            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Task Progress</span>
                    <span class="font-semibold"><?= $prog ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-yellow-500 h-3 rounded-full transition-all" style="width: <?= $prog ?>%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1"><?= $done ?> of <?= $total ?> tasks completed</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <span class="text-gray-500">Start Date:</span>
                    <p class="font-medium text-gray-800"><?= $proj['start_date'] ? date('M j, Y', strtotime($proj['start_date'])) : 'N/A' ?></p>
                </div>
                <div>
                    <span class="text-gray-500">End Date:</span>
                    <p class="font-medium text-gray-800"><?= $proj['end_date'] ? date('M j, Y', strtotime($proj['end_date'])) : 'N/A' ?></p>
                </div>
            </div>

            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-4 border-t border-gray-100">
                <input type="hidden" name="project_id" value="<?= $proj['id'] ?>">
                <input type="hidden" name="update_status" value="1">
                <label class="text-sm font-medium text-gray-700">Update Status:</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $s === $proj['status'] ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                    Update
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
