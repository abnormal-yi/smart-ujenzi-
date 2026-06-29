<?php
$pageTitle = 'Project Requirements';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$requests = runQuery("SELECT cr.*, u.name as client_name, u.email, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.assigned_pm_id = ? AND cr.status != 'Accepted' ORDER BY cr.id DESC", [$userId]);

$docCounts = [];
$docs = runQuery("SELECT request_id, COUNT(*) as cnt FROM request_documents GROUP BY request_id");
foreach ($docs as $d) $docCounts[$d['request_id']] = $d['cnt'];
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
        </div>
        <div class="mb-4">
            <span class="text-sm text-gray-500">Description:</span>
            <p class="text-gray-700 mt-1"><?= htmlspecialchars($req['description']) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-gray-100">
            <a href="../client/upload-documents.php?request_id=<?= $req['id'] ?>" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                📄 Documents (<?= (int)($docCounts[$req['id']] ?? 0) ?> files)
            </a>
            <?php if ($req['status'] === 'Reviewed'): ?>
            <form method="POST" action="../customer_requests.php" class="flex items-center gap-2 ml-auto">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <button type="submit" name="accept_request" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">Accept</button>
                <button type="submit" name="reject_request" class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
