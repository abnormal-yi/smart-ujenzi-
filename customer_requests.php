<?php
$pageTitle = 'Customer Requests';
require_once __DIR__ . '/includes/functions.php';
requireRole(['super_admin', 'admin', 'project_manager']);
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_pm'])) {
    $reqId = (int)$_POST['request_id'];
    $pmId = (int)$_POST['pm_id'];
    runQuery("UPDATE customer_requests SET assigned_pm_id = ?, status = 'Reviewed' WHERE id = ?", [$pmId, $reqId]);
    runQuery("INSERT INTO notifications (user_id, message) VALUES (?, 'New request assigned to you')", [$pmId]);
    $success = 'Project Manager assigned!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_budget'])) {
    $reqId = (int)$_POST['request_id'];
    runQuery("UPDATE customer_requests SET budget_amount = ?, proposed_timeline = ?, budget_status = 'pending' WHERE id = ? AND assigned_pm_id = ?",
        [$_POST['budget_amount'], $_POST['proposed_timeline'], $reqId, $userId]);
    $success = 'Budget sent to client!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_budget_status'])) {
    $reqId = (int)$_POST['request_id'];
    $status = $_POST['budget_status'];
    runQuery("UPDATE customer_requests SET budget_status = ?, status = ? WHERE id = ?", [$status, $status === 'accepted' ? 'Accepted' : 'Rejected', $reqId]);
    if ($status === 'accepted') {
        $req = runQuery("SELECT * FROM customer_requests WHERE id = ?", [$reqId])[0];
        runQuery("INSERT INTO projects (name, description, status, project_manager_id, customer_id, start_date) VALUES (?, ?, 'Pending', ?, ?, CURDATE())",
            [$req['project_type'] . ' - ' . $req['location'], $req['description'], $req['assigned_pm_id'], $req['customer_id']]);
    }
    $success = 'Budget ' . $status . '!';
}

if ($role === 'project_manager') {
    $requests = runQuery("SELECT cr.*, u.name as customer_name, c.name as company_name, pm.name as pm_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id LEFT JOIN users pm ON cr.assigned_pm_id = pm.id WHERE cr.assigned_pm_id = ? ORDER BY cr.id DESC", [$userId]);
} else {
    $requests = runQuery("SELECT cr.*, u.name as customer_name, c.name as company_name, pm.name as pm_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id LEFT JOIN users pm ON cr.assigned_pm_id = pm.id ORDER BY cr.id DESC");
}

$projectManagers = runQuery("SELECT id, name FROM users WHERE role = 'project_manager'");
$statusColors = ['Pending' => 'badge-yellow', 'Reviewed' => 'badge-blue', 'Accepted' => 'badge-green', 'Rejected' => 'badge-red'];
?>

<?php if ($success): ?>
<div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500"><?= count($requests) ?> requests</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Company</th>
                    <th class="pb-3 font-semibold text-gray-600">Customer</th>
                    <th class="pb-3 font-semibold text-gray-600">Type</th>
                    <th class="pb-3 font-semibold text-gray-600">Location</th>
                    <th class="pb-3 font-semibold text-gray-600">Budget Range</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Assigned PM</th>
                    <th class="pb-3 font-semibold text-gray-600">Budget Amount</th>
                    <th class="pb-3 font-semibold text-gray-600">Timeline</th>
                    <th class="pb-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3"><?= htmlspecialchars($r['company_name'] ?? '—') ?></td>
                    <td class="py-3 font-medium"><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td class="py-3"><?= htmlspecialchars($r['project_type']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['location']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['budget_range'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$r['status']] ?? 'badge-gray' ?>"><?= $r['status'] ?></span>
                    </td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['pm_name'] ?? '—') ?></td>
                    <td class="py-3 text-gray-600"><?= $r['budget_amount'] ? number_format((float)$r['budget_amount'], 2) : '—' ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['proposed_timeline'] ?? '—') ?></td>
                    <td class="py-3">
                        <?php if (in_array($role, ['admin', 'super_admin'])): ?>
                            <?php if (!$r['assigned_pm_id']): ?>
                            <form method="POST" class="flex items-center space-x-1">
                                <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                <select name="pm_id" required class="text-xs px-2 py-1 border border-gray-300 rounded">
                                    <option value="">PM</option>
                                    <?php foreach ($projectManagers as $pm): ?>
                                    <option value="<?= $pm['id'] ?>"><?= htmlspecialchars($pm['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="assign_pm" class="text-xs px-2 py-1 bg-slate-900 text-white rounded hover:bg-slate-800">Go</button>
                            </form>
                            <?php elseif ($r['budget_status'] === 'pending'): ?>
                            <div class="flex space-x-1">
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="budget_status" value="accepted">
                                    <button type="submit" name="update_budget_status" class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">Accept</button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="budget_status" value="rejected">
                                    <button type="submit" name="update_budget_status" class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        <?php elseif ($role === 'project_manager' && $r['assigned_pm_id'] == $userId && !$r['budget_amount']): ?>
                            <form method="POST" class="flex flex-col space-y-1">
                                <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                <input type="text" name="budget_amount" placeholder="Amount" required class="text-xs px-2 py-1 border border-gray-300 rounded w-24">
                                <input type="text" name="proposed_timeline" placeholder="Timeline" required class="text-xs px-2 py-1 border border-gray-300 rounded w-24">
                                <button type="submit" name="submit_budget" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Submit Budget</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
