<?php
$pageTitle = 'Project Requirements';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$requests = runQuery("SELECT cr.*, u.name as client_name, u.email, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.assigned_pm_id = ? AND cr.status != 'Accepted' ORDER BY cr.id DESC", [$userId]);
?>
<?php if (empty($requests)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <h3 class="text-xl font-bold text-gray-800 mb-2">No Requirements</h3>
    <p class="text-gray-500">No client requirements assigned to you yet.</p>
</div>
<?php else: ?>
<div class="grid gap-6">
    <?php foreach ($requests as $req): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($req['client_name']) ?></h3>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($req['email']) ?></p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold self-start
                <?= $req['status'] === 'Approved' ? 'bg-green-100 text-green-700' : ($req['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') ?>">
                <?= $req['status'] ?>
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 text-sm">
            <div>
                <span class="text-gray-500">Project Type:</span>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($req['project_type'] ?? 'N/A') ?></p>
            </div>
            <div>
                <span class="text-gray-500">Company:</span>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($req['company_name'] ?? 'N/A') ?></p>
            </div>
            <div>
                <span class="text-gray-500">Location:</span>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($req['location'] ?? 'N/A') ?></p>
            </div>
            <div>
                <span class="text-gray-500">Budget Range:</span>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($req['budget_range'] ?? 'N/A') ?></p>
            </div>
        </div>
        <div class="mb-4">
            <span class="text-sm text-gray-500">Description:</span>
            <p class="text-gray-700 mt-1"><?= htmlspecialchars($req['description']) ?></p>
        </div>
        <a href="budget.php?id=<?= $req['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Prepare Budget
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
