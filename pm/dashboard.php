<?php
$pageTitle = 'PM Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

$projectCount = runQuery("SELECT COUNT(*) as c FROM projects WHERE project_manager_id = ?", [$userId])[0]['c'];
$pendingRequests = runQuery("SELECT COUNT(*) as c FROM users WHERE role = 'fundi' AND approved = 0")[0]['c'];
$tasksDue = runQuery("SELECT COUNT(*) as c FROM tasks t JOIN projects p ON t.project_id = p.id WHERE p.project_manager_id = ? AND t.deadline < CURDATE() AND t.status != 'Completed'", [$userId])[0]['c'];

$myProjects = runQuery("SELECT * FROM projects WHERE project_manager_id = ? ORDER BY status, start_date DESC", [$userId]);
$myRequests = runQuery("SELECT cr.*, u.name as client_name, u.email as client_email, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.assigned_pm_id = ? ORDER BY cr.id DESC", [$userId]);

$docCounts = [];
$docs = runQuery("SELECT request_id, COUNT(*) as cnt FROM request_documents GROUP BY request_id");
foreach ($docs as $d) $docCounts[$d['request_id']] = $d['cnt'];
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">My Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $projectCount ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Reviews</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $pendingRequests ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Overdue Tasks</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= $tasksDue ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">My Projects</h3>
        <?php if (empty($myProjects)): ?>
            <p class="text-gray-500 text-sm">No projects assigned to you yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="pb-3 font-medium">Project</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium">End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myProjects as $p): ?>
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-800 font-medium">
                                <a href="../gantt.php" class="text-blue-600 hover:underline"><?= htmlspecialchars($p['name']) ?></a>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full <?= $p['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($p['status'] === 'Ongoing' || $p['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : ($p['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) ?>"><?= $p['status'] ?></span>
                            </td>
                            <td class="py-3 text-gray-500"><?= isset($p['end_date']) ? date('M j, Y', strtotime($p['end_date'])) : 'N/A' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
        <a href="fundi-approve.php" class="inline-block w-full bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-6 py-3 rounded-xl transition-colors">
            Manage Fundi Approvals (<?= $pendingRequests ?> pending)
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Assigned Requests</h3>
        <?php if (empty($myRequests)): ?>
            <p class="text-gray-500 text-sm">No customer requests assigned to you.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="pb-3 font-medium">Client</th>
                            <th class="pb-3 font-medium">Company</th>
                            <th class="pb-3 font-medium">Docs</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myRequests as $r): ?>
                        <tr class="border-b border-gray-50">
                            <td class="py-3 text-gray-800">
                                <?= htmlspecialchars($r['client_name']) ?>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($r['client_email'] ?? '') ?></div>
                            </td>
                            <td class="py-3 text-gray-600"><?= htmlspecialchars($r['company_name'] ?? 'N/A') ?></td>
                            <td class="py-3">
                                <a href="../client/upload-documents.php?request_id=<?= $r['id'] ?>" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                    📄 <?= (int)($docCounts[$r['id']] ?? 0) ?>
                                </a>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full <?= $r['status'] === 'Accepted' ? 'bg-green-100 text-green-700' : ($r['status'] === 'Reviewed' ? 'bg-blue-100 text-blue-700' : ($r['status'] === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')) ?>"><?= $r['status'] ?? 'Pending' ?></span>
                            </td>
                            <td class="py-3">
                                <?php if ($r['status'] === 'Reviewed'): ?>
                                <form method="POST" action="../customer_requests.php" class="flex items-center gap-1">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="accept_request" class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">Accept</button>
                                    <button type="submit" name="reject_request" class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Reject</button>
                                </form>
                                <?php elseif ($r['status'] === 'Accepted'): ?>
                                <span class="text-xs text-green-600">✓ Contact: <?= htmlspecialchars($r['client_email'] ?? '') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$clientDocs = runQuery("SELECT rd.*, cr.project_type, u.name as client_name FROM request_documents rd JOIN customer_requests cr ON rd.request_id = cr.id JOIN users u ON cr.customer_id = u.id WHERE cr.assigned_pm_id = ? ORDER BY rd.created_at DESC LIMIT 20", [$userId]);
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">📁 Client Documents</h3>
    <?php if (empty($clientDocs)): ?>
        <p class="text-gray-500 text-sm">No documents uploaded yet. Documents appear here once clients upload them.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-3 font-medium">File</th>
                    <th class="pb-3 font-medium">Client</th>
                    <th class="pb-3 font-medium">Project</th>
                    <th class="pb-3 font-medium">Date</th>
                    <th class="pb-3 font-medium">Download</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientDocs as $d): ?>
                <tr class="border-b border-gray-50">
                    <td class="py-3 text-gray-800"><?= htmlspecialchars($d['original_name']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($d['client_name']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($d['project_type']) ?></td>
                    <td class="py-3 text-gray-500"><?= date('M j, Y', strtotime($d['created_at'])) ?></td>
                    <td class="py-3">
                        <a href="../download-document.php?id=<?= $d['id'] ?>" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">⬇ Download</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
