<?php
$pageTitle = 'Customer Requests';
require_once __DIR__ . '/includes/functions.php';
requireRole(['super_admin', 'admin', 'project_manager']);
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$success = '';

$docCounts = [];
$docs = runQuery("SELECT request_id, COUNT(*) as cnt FROM request_documents GROUP BY request_id");
foreach ($docs as $d) $docCounts[$d['request_id']] = $d['cnt'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_pm'])) {
    $reqId = (int)$_POST['request_id'];
    $pmId = (int)$_POST['pm_id'];
    runQuery("UPDATE customer_requests SET assigned_pm_id = ?, status = 'Reviewed' WHERE id = ?", [$pmId, $reqId]);
    runQuery("INSERT INTO notifications (user_id, message, link) VALUES (?, 'New request assigned to you', '/customer_requests.php')", [$pmId]);
    $req = runQuery("SELECT customer_id FROM customer_requests WHERE id = ?", [$reqId]);
    if ($req) {
        runQuery("INSERT INTO notifications (user_id, message, link) VALUES (?, 'Your request has been reviewed. A project manager has been assigned.', '/client/requests.php')", [$req[0]['customer_id']]);
    }
    logActivity('request_assigned', 'customer_request', $reqId, "PM #{$pmId} assigned to request #{$reqId}");
    $success = 'Project Manager assigned! Client notified.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_request'])) {
    $reqId = (int)$_POST['request_id'];
    runQuery("UPDATE customer_requests SET status = 'Accepted' WHERE id = ?", [$reqId]);
    $req = runQuery("SELECT customer_id FROM customer_requests WHERE id = ?", [$reqId]);
    if ($req) {
        runQuery("INSERT INTO notifications (user_id, message, link) VALUES (?, 'Your request has been accepted! You can now upload project documents.', '/client/upload-documents.php?id=" . ((int)$_POST['request_id']) . "')", [$req[0]['customer_id']]);
    }
    $success = 'Request accepted! Client can now upload documents.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    runQuery("UPDATE customer_requests SET status = 'Rejected' WHERE id = ?", [(int)$_POST['request_id']]);
    $success = 'Request rejected.';
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
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Docs</th>
                    <th class="pb-3 font-semibold text-gray-600">Assigned PM</th>
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
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$r['status']] ?? 'badge-gray' ?>"><?= $r['status'] ?></span>
                    </td>
                    <td class="py-3">
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-500"><?= (int)($docCounts[$r['id']] ?? 0) ?> files</span>
                            <a href="client/upload-documents.php?request_id=<?= $r['id'] ?>" class="text-xs text-blue-600 hover:underline ml-1">Manage</a>
                        </div>
                    </td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['pm_name'] ?? '—') ?></td>
                    <td class="py-3">
                        <?php if (in_array($role, ['admin', 'super_admin']) && !$r['assigned_pm_id']): ?>
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
                        <?php elseif ($role === 'project_manager' && $r['status'] === 'Reviewed'): ?>
                        <form method="POST" class="flex items-center gap-1">
                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                            <button type="submit" name="accept_request" class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">Accept</button>
                            <button type="submit" name="reject_request" class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
                        </form>
                        <?php else: ?>
                        <span class="text-gray-400 text-xs"><?= htmlspecialchars($r['pm_name'] ?? '—') ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
