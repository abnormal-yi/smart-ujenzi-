<?php
$pageTitle = 'Projects';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $res = executeQuery('INSERT INTO projects (name, description, project_manager_id, start_date, end_date, icon) VALUES (?, ?, ?, ?, ?, ?)',
                [$_POST['name'], $_POST['description'], $_POST['project_manager_id'] ?: null, $_POST['start_date'] ?: null, $_POST['end_date'] ?: null, $_POST['icon'] ?? '🏗️']);
            logActivity('project_created', 'project', $res['id'], "Created project: {$_POST['name']}");
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Project created successfully'];
        } elseif ($_POST['action'] === 'status') {
            executeQuery('UPDATE projects SET status = ? WHERE id = ?', [$_POST['status'], $_POST['id']]);
            logActivity('project_status', 'project', (int)$_POST['id'], "Status changed to {$_POST['status']}");
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Project status updated'];
        }
        redirect('projects.php');
    }
}

require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

if ($role === 'super_admin' || $role === 'admin') {
    $projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id ORDER BY p.id DESC");
} elseif ($role === 'project_manager') {
    $projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id WHERE p.project_manager_id = ? ORDER BY p.id DESC", [$userId]);
} elseif ($role === 'fundi') {
    $projects = runQuery("SELECT DISTINCT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id JOIN tasks t ON t.project_id = p.id WHERE t.fundi_id = ? ORDER BY p.id DESC", [$userId]);
} elseif ($role === 'client') {
    $projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id WHERE p.customer_id = ? ORDER BY p.id DESC", [$userId]);
}

$projectManagers = runQuery("SELECT id, name FROM users WHERE role = 'project_manager'");
$statuses = ['Pending', 'Ongoing', 'In Progress', 'Completed', 'On Hold'];
$statusColors = ['Pending' => 'badge-yellow', 'Ongoing' => 'badge-blue', 'In Progress' => 'badge-indigo', 'Completed' => 'badge-green', 'On Hold' => 'badge-red'];
$canCreate = in_array($role, ['super_admin', 'admin', 'project_manager']);
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-500"><?= count($projects) ?> total projects</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="gantt.php" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">📊 Gantt</a>
            <?php if ($canCreate): ?>
            <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                + New Project
            </button>
            <?php endif; ?>
        </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Name</th>
                    <th class="pb-3 font-semibold text-gray-600">PM</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Start</th>
                    <th class="pb-3 font-semibold text-gray-600">End</th>
                    <th class="pb-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 font-medium">
                        <span class="text-blue-500 mr-1"><?= htmlspecialchars($p['icon'] ?? '🏗️') ?></span>
                        <?= htmlspecialchars($p['name']) ?>
                    </td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($p['pm_name'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$p['status']] ?? 'badge-gray' ?>"><?= $p['status'] ?></span>
                    </td>
                    <td class="py-3 text-gray-600"><?= $p['start_date'] ?? '—' ?></td>
                    <td class="py-3 text-gray-600"><?= $p['end_date'] ?? '—' ?></td>
                    <td class="py-3">
                        <button onclick="openModal('status-modal-<?= $p['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Status</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="create-modal" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('create-modal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 mt-20 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-4">New Project</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Manager</label>
                    <select name="project_manager_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">Select PM</option>
                        <?php foreach ($projectManagers as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Icon</label>
                    <select name="icon" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="🏗️">🏗️ Construction</option>
                        <option value="🏠">🏠 House</option>
                        <option value="🔧">🔧 Tools</option>
                        <option value="📋">📋 Planning</option>
                        <option value="🎯">🎯 Project</option>
                        <option value="🧱">🧱 Masonry</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('create-modal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Create</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($projects as $p): ?>
<div id="status-modal-<?= $p['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('status-modal-<?= $p['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-32 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Update Status</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($p['name']) ?></p>
        <form method="POST">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $s === $p['status'] ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('status-modal-<?= $p['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Update</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
document.querySelectorAll('form').forEach(f => {
    f.addEventListener('submit', () => {
        f.querySelector('button[type="submit"]')?.setAttribute('disabled', 'disabled');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
