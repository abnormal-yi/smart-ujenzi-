<?php
$pageTitle = 'Approve Fundi';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager', 'admin', 'super_admin']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['fundi_id'])) {
    $fundiId = (int)$_POST['fundi_id'];
    if ($_POST['action'] === 'approve') {
        executeQuery("UPDATE users SET approved = 1 WHERE id = ? AND role = 'fundi'", [$fundiId]);
        logActivity('user_approved', 'user', $fundiId, "Fundi approved by {$userId}");
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Fundi approved'];
    } elseif ($_POST['action'] === 'reject') {
        executeQuery("UPDATE users SET approved = 0 WHERE id = ? AND role = 'fundi'", [$fundiId]);
        logActivity('user_rejected', 'user', $fundiId, "Fundi rejected by {$userId}");
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Fundi rejected'];
    }
    redirect('fundi-approve.php');
}

$pendingFundis = [];
$approvedFundis = [];
try {
    $pendingFundis = runQuery("SELECT id, name, email, skills, location FROM users WHERE role = 'fundi' AND approved = 0 ORDER BY id DESC");
    $approvedFundis = runQuery("SELECT id, name, email, skills, location FROM users WHERE role = 'fundi' AND approved = 1 ORDER BY name");
} catch (Exception $e) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Fundi Approvals</h1>
    <p class="text-gray-500">Approve or reject fundi registrations</p>
</div>

<?php if ($flash): ?>
<div class="p-4 mb-6 rounded-lg text-sm <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Pending Approvals (<?= count($pendingFundis) ?>)</h2>
    <?php if (empty($pendingFundis)): ?>
        <p class="text-gray-400 text-sm">No pending fundi registrations.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-3 pr-4">Name</th>
                    <th class="pb-3 pr-4">Email</th>
                    <th class="pb-3 pr-4">Skills</th>
                    <th class="pb-3 pr-4">Location</th>
                    <th class="pb-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingFundis as $f): ?>
                <tr class="border-b border-gray-50">
                    <td class="py-3 pr-4 font-medium"><?= htmlspecialchars($f['name']) ?></td>
                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($f['email']) ?></td>
                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($f['skills'] ?: '-') ?></td>
                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($f['location'] ?: '-') ?></td>
                    <td class="py-3 flex space-x-2">
                        <form method="POST" class="inline">
                            <input type="hidden" name="fundi_id" value="<?= $f['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors">Approve</button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="fundi_id" value="<?= $f['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition-colors">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Approved Fundi (<?= count($approvedFundis) ?>)</h2>
    <?php if (empty($approvedFundis)): ?>
        <p class="text-gray-400 text-sm">No approved fundi yet.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-3 pr-4">Name</th>
                    <th class="pb-3 pr-4">Email</th>
                    <th class="pb-3 pr-4">Skills</th>
                    <th class="pb-3">Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approvedFundis as $f): ?>
                <tr class="border-b border-gray-50">
                    <td class="py-3 pr-4 font-medium"><?= htmlspecialchars($f['name']) ?></td>
                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($f['email']) ?></td>
                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($f['skills'] ?: '-') ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($f['location'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
