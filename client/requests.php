<?php
$pageTitle = 'My Requests';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reqId = (int)$_POST['request_id'];
    $action = $_POST['action'];
    runQuery("UPDATE customer_requests SET budget_status = ? WHERE id = ? AND customer_id = ?", [$action === 'accept' ? 'accepted' : 'rejected', $reqId, $userId]);

    if ($action === 'accept') {
        $req = runQuery("SELECT * FROM customer_requests WHERE id = ?", [$reqId])[0];
        runQuery("INSERT INTO projects (name, description, status, project_manager_id, customer_id, start_date) VALUES (?, ?, 'Pending', ?, ?, CURDATE())",
            [$req['project_type'] . ' - ' . $req['location'], $req['description'], $req['assigned_pm_id'], $userId]);
        $success = 'Budget accepted! Your project is being set up.';
    } else {
        $success = 'Budget rejected. A PM will contact you.';
    }
}

$requests = runQuery("SELECT cr.*, c.name as company_name, pm.name as pm_name FROM customer_requests cr LEFT JOIN companies c ON cr.company_id = c.id LEFT JOIN users pm ON cr.assigned_pm_id = pm.id WHERE cr.customer_id = ? ORDER BY cr.id DESC", [$userId]);
?>

<?php if (isset($success)): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
<?php endif; ?>

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
                <?= $r['status'] === 'Approved' ? 'bg-green-100 text-green-700' : ($r['status'] === 'Reviewed' ? 'bg-blue-100 text-blue-700' : ($r['status'] === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')) ?>">
                <?= $r['status'] ?? 'Pending' ?>
            </span>
        </div>

        <?php if (!empty($r['description'])): ?>
        <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($r['description']) ?></p>
        <?php endif; ?>

        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-3">
            <span><span class="font-medium text-gray-700">Budget Range:</span> <?= htmlspecialchars($r['budget_range'] ?? 'N/A') ?></span>
            <?php if ($r['assigned_pm_id']): ?>
            <span><span class="font-medium text-gray-700">PM:</span> <?= htmlspecialchars($r['pm_name'] ?? 'Assigned') ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($r['budget_amount'])): ?>
        <div class="border-t border-gray-100 pt-3 mt-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-bold text-gray-900">Budget: TZS <?= number_format($r['budget_amount']) ?></span>
                    <?php if (!empty($r['timeline'])): ?>
                    <span class="ml-4 text-gray-500">Timeline: <?= htmlspecialchars($r['timeline']) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (($r['budget_status'] ?? '') === 'pending'): ?>
                    <div class="flex gap-2">
                        <form method="POST" class="inline">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="action" value="accept">
                            <button type="submit" class="px-4 py-1.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl text-sm transition-colors">Accept</button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="px-4 py-1.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl text-sm transition-colors">Reject</button>
                        </form>
                    </div>
                    <?php elseif (($r['budget_status'] ?? '') === 'accepted'): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Accepted
                    </span>
                    <?php elseif (($r['budget_status'] ?? '') === 'rejected'): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rejected
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
