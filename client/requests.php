<?php
$pageTitle = 'My Requests';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

$requests = runQuery("SELECT cr.*, c.name as company_name, pm.name as pm_name FROM customer_requests cr LEFT JOIN companies c ON cr.company_id = c.id LEFT JOIN users pm ON cr.assigned_pm_id = pm.id WHERE cr.customer_id = ? ORDER BY cr.id DESC", [$userId]);
?>

<?php if (empty($requests)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
    <h3 class="text-xl font-bold text-gray-800 mb-2">No Requests Yet</h3>
    <p class="text-gray-500">Submit a request to a contractor to get started.</p>
    <a href="dashboard.php" class="inline-block mt-4 px-6 py-2 bg-yellow-500 text-black font-semibold rounded-xl">Browse Contractors</a>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($requests as $r): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($r['company_name']) ?></h3>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($r['project_type']) ?> — <?= htmlspecialchars($r['location']) ?></p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold self-start
                <?= $r['status'] === 'Accepted' ? 'bg-green-100 text-green-700' : ($r['status'] === 'Reviewed' ? 'bg-blue-100 text-blue-700' : ($r['status'] === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')) ?>">
                <?= $r['status'] ?? 'Pending' ?>
            </span>
        </div>

        <?php if (!empty($r['description'])): ?>
        <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($r['description']) ?></p>
        <?php endif; ?>

        <div class="flex flex-wrap gap-4 text-sm text-gray-500">
            <span><span class="font-medium text-gray-700">Budget Range:</span> <?= htmlspecialchars($r['budget_range'] ?? 'N/A') ?></span>
            <?php if ($r['assigned_pm_id']): ?>
            <span><span class="font-medium text-gray-700">PM:</span> <?= htmlspecialchars($r['pm_name'] ?? 'Assigned') ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
