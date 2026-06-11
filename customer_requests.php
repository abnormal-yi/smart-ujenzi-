<?php
// Customer Requests page: customers submit requests, admin/manager review and propose
$pageTitle = 'Customer Requests';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager', 'customer']);
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Handle POST actions: create new request or update existing one
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create') {
        // Customers can submit a new project request
        executeQuery('INSERT INTO customer_requests (customer_id, project_type, location, budget_range, description) VALUES (?, ?, ?, ?, ?)',
            [$userId, $_POST['project_type'], $_POST['location'], $_POST['budget_range'], $_POST['description']]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Request submitted'];
        redirect('customer_requests.php');
    } elseif ($_POST['action'] === 'update') {
        // Update request: status, proposal, budget, deadline (only non-empty values override)
        executeQuery('UPDATE customer_requests SET status = COALESCE(NULLIF(?, ""), status), company_proposal = COALESCE(NULLIF(?, ""), company_proposal), proposed_budget = COALESCE(NULLIF(?, ""), proposed_budget), proposed_deadline = COALESCE(NULLIF(?, ""), proposed_deadline) WHERE id = ?',
            [$_POST['status'], $_POST['company_proposal'], $_POST['proposed_budget'], $_POST['proposed_deadline'], $_POST['id']]);

        // If a customer accepts the proposal, automatically create a project
        if (($_POST['status'] ?? '') === 'Accepted' && $role === 'customer') {
            $reqData = runQuery('SELECT * FROM customer_requests WHERE id = ?', [$_POST['id']]);
            if ($reqData) {
                executeQuery('INSERT INTO projects (name, description, status, manager_id, start_date, end_date) VALUES (?, ?, "Pending", 1, CURDATE(), ?)',
                    [$reqData[0]['project_type'] . ' - ' . $reqData[0]['location'], $reqData[0]['description'], $reqData[0]['proposed_deadline'] ?? '']);
            }
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Request updated'];
        redirect('customer_requests.php');
    }
}

// Fetch requests: customers see only theirs; admins/managers see all
$query = 'SELECT cr.*, u.name as customer_name, u.email as customer_email FROM customer_requests cr JOIN users u ON cr.customer_id = u.id';
$params = [];
if ($role === 'customer') {
    $query .= ' WHERE cr.customer_id = ?';
    $params[] = $userId;
}
$requests = runQuery($query . ' ORDER BY cr.id DESC', $params);
$statusColors = ['Pending' => 'badge-yellow', 'Reviewed' => 'badge-blue', 'Accepted' => 'badge-green', 'Rejected' => 'badge-red'];
$statuses = ['Pending', 'Reviewed', 'Accepted', 'Rejected'];
$canManage = in_array($role, ['admin', 'manager']);
?>

<!-- Requests listing table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500"><?= count($requests) ?> requests</p>
        <?php if ($role === 'customer'): ?>
            <!-- Only customers see the "New Request" button -->
            <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">+ New Request</button>
        <?php endif; ?>
    </div>

    <!-- Requests data table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Customer</th>
                    <th class="pb-3 font-semibold text-gray-600">Project Type</th>
                    <th class="pb-3 font-semibold text-gray-600">Location</th>
                    <th class="pb-3 font-semibold text-gray-600">Budget</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 font-medium"><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td class="py-3"><?= htmlspecialchars($r['project_type']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['location']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($r['budget_range'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$r['status']] ?? 'badge-gray' ?>"><?= $r['status'] ?></span>
                    </td>
                    <td class="py-3">
                        <button onclick="openModal('detail-modal-<?= $r['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100">View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create New Request (customers only) -->
<div id="create-modal" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('create-modal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 mt-20 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-4">New Request</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Type</label>
                    <input type="text" name="project_type" required placeholder="e.g. Residential House" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" required placeholder="e.g. Dar es Salaam" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget Range</label>
                    <input type="text" name="budget_range" placeholder="e.g. 50M - 100M TZS" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('create-modal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal per request: View Details & Update -->
<?php foreach ($requests as $r): ?>
<div id="detail-modal-<?= $r['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('detail-modal-<?= $r['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 mt-12 p-6 z-10 max-h-[80vh] overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Request Details</h3>

        <!-- Read-only summary of the request -->
        <div class="space-y-3 mb-6">
            <div><span class="text-sm text-gray-500">Customer:</span> <span class="font-medium"><?= htmlspecialchars($r['customer_name']) ?></span></div>
            <div><span class="text-sm text-gray-500">Project Type:</span> <span class="font-medium"><?= htmlspecialchars($r['project_type']) ?></span></div>
            <div><span class="text-sm text-gray-500">Location:</span> <?= htmlspecialchars($r['location']) ?></div>
            <div><span class="text-sm text-gray-500">Budget:</span> <?= htmlspecialchars($r['budget_range'] ?? '—') ?></div>
            <div><span class="text-sm text-gray-500">Description:</span>
                <p class="text-gray-700 mt-1"><?= htmlspecialchars($r['description'] ?? '—') ?></p>
            </div>
            <?php if ($r['company_proposal']): ?>
                <div><span class="text-sm text-gray-500">Company Proposal:</span>
                    <p class="text-gray-700 mt-1"><?= htmlspecialchars($r['company_proposal']) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($r['proposed_budget']): ?>
                <div><span class="text-sm text-gray-500">Proposed Budget:</span> <?= htmlspecialchars($r['proposed_budget']) ?></div>
            <?php endif; ?>
            <?php if ($r['proposed_deadline']): ?>
                <div><span class="text-sm text-gray-500">Proposed Deadline:</span> <?= htmlspecialchars($r['proposed_deadline']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Update form: admin/manager can set status, proposal, budget, deadline -->
        <form method="POST" class="border-t border-gray-200 pt-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">

            <?php if ($canManage): ?>
                <!-- Admin/Manager fields: status change, company proposal, budget, deadline -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="">Keep current</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $s === $r['status'] ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Proposal</label>
                        <textarea name="company_proposal" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"><?= htmlspecialchars($r['company_proposal'] ?? '') ?></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proposed Budget</label>
                            <input type="text" name="proposed_budget" value="<?= htmlspecialchars($r['proposed_budget'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proposed Deadline</label>
                            <input type="date" name="proposed_deadline" value="<?= $r['proposed_deadline'] ?? '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Customer decision field: accept or reject the company proposal -->
            <?php if ($role === 'customer' && $r['status'] !== 'Accepted' && $r['status'] !== 'Rejected'): ?>
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your decision</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">No change</option>
                        <option value="Accepted">✅ Accept Proposal</option>
                        <option value="Rejected">❌ Reject</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('detail-modal-<?= $r['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
                <?php if ($canManage || $role === 'customer'): ?>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">Update</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal toggle helper functions -->
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
