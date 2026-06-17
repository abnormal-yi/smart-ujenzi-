<?php
$pageTitle = 'Prepare Budget';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_budget'])) {
    $reqId = (int)$_POST['request_id'];
    $amount = $_POST['budget_amount'];
    $timeline = $_POST['proposed_timeline'];
    runQuery("UPDATE customer_requests SET budget_amount = ?, proposed_timeline = ?, budget_status = 'pending' WHERE id = ? AND assigned_pm_id = ?", [$amount, $timeline, $reqId, $userId]);
    $success = 'Budget sent to client for review!';
}

$selectedReq = null;
if (isset($_GET['id'])) {
    $selectedReq = runQuery("SELECT cr.*, u.name as client_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id WHERE cr.id = ? AND cr.assigned_pm_id = ?", [(int)$_GET['id'], $userId])[0] ?? null;
}

$requests = runQuery("SELECT cr.*, u.name as client_name, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.assigned_pm_id = ? AND (cr.budget_status IS NULL OR cr.budget_status = 'pending') ORDER BY cr.id DESC", [$userId]);
?>
<?php if (isset($success)): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Requests</h3>
        <?php if (empty($requests)): ?>
            <p class="text-gray-500 text-sm">No requests eligible for budget submission.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="pb-3 font-medium">Client</th>
                            <th class="pb-3 font-medium">Company</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-800"><?= htmlspecialchars($r['client_name']) ?></td>
                            <td class="py-3 text-gray-600"><?= htmlspecialchars($r['company_name'] ?? 'N/A') ?></td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full <?= $r['budget_status'] === 'approved' ? 'bg-green-100 text-green-700' : ($r['budget_status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') ?>"><?= $r['budget_status'] ?? 'No Budget' ?></span>
                            </td>
                            <td class="py-3">
                                <a href="budget.php?id=<?= $r['id'] ?>" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Select</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Budget Form</h3>
        <?php if ($selectedReq): ?>
        <form method="POST">
            <input type="hidden" name="request_id" value="<?= $selectedReq['id'] ?>">
            <div class="mb-4">
                <p class="text-sm text-gray-500">Client</p>
                <p class="font-medium text-gray-800"><?= htmlspecialchars($selectedReq['client_name']) ?></p>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500">Description</p>
                <p class="text-gray-700"><?= htmlspecialchars($selectedReq['description']) ?></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Budget Amount (TZS)</label>
                <input type="number" name="budget_amount" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Proposed Timeline</label>
                <input type="text" name="proposed_timeline" placeholder="e.g. 3 months" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <button type="submit" name="submit_budget" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                Submit Budget
            </button>
        </form>
        <?php else: ?>
        <p class="text-gray-500 text-sm">Select a request from the list to prepare a budget.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
