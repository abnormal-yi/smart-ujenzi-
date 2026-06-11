<?php
$pageTitle = 'Tasks';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager', 'supervisor', 'constructor']);
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create') {
        executeQuery('INSERT INTO tasks (project_id, name, description, supervisor_id, deadline) VALUES (?, ?, ?, ?, ?)',
            [$_POST['project_id'], $_POST['name'], $_POST['description'], $_POST['supervisor_id'] ?: null, $_POST['deadline'] ?: null]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task created successfully'];
    } elseif ($_POST['action'] === 'status') {
        executeQuery('UPDATE tasks SET status = ? WHERE id = ?', [$_POST['status'], $_POST['id']]);
        $task = runQuery('SELECT * FROM tasks WHERE id = ?', [$_POST['id']]);
        if ($task) {
            executeQuery('INSERT INTO notifications (user_id, message, is_read) SELECT id, ?, 0 FROM users WHERE role IN ("admin", "manager")',
                ["Task '{$task[0]['name']}' status updated to {$_POST['status']}"]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task status updated'];
    }
    redirect('tasks.php');
}

$tasks = runQuery('SELECT t.*, p.name as project_name, u.name as supervisor_name FROM tasks t LEFT JOIN projects p ON t.project_id = p.id LEFT JOIN users u ON t.supervisor_id = u.id ORDER BY t.id DESC');
$projects = runQuery('SELECT id, name FROM projects');
$supervisors = runQuery('SELECT id, name FROM users WHERE role IN ("admin", "manager", "supervisor")');
$statuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];
$statusColors = ['Not Started' => 'badge-gray', 'In Progress' => 'badge-blue', 'Completed' => 'badge-green', 'On Hold' => 'badge-red'];
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500"><?= count($tasks) ?> total tasks</p>
        <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">+ New Task</button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Task</th>
                    <th class="pb-3 font-semibold text-gray-600">Project</th>
                    <th class="pb-3 font-semibold text-gray-600">Supervisor</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Deadline</th>
                    <th class="pb-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 font-medium"><?= htmlspecialchars($t['name']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($t['project_name'] ?? '—') ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($t['supervisor_name'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$t['status']] ?? 'badge-gray' ?>"><?= $t['status'] ?></span>
                    </td>
                    <td class="py-3 text-gray-600"><?= $t['deadline'] ?? '—' ?></td>
                    <td class="py-3">
                        <button onclick="openModal('status-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Status</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="create-modal" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('create-modal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-auto mt-16 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-4">New Task</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select name="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">Select project</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor</label>
                    <select name="supervisor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">Select supervisor</option>
                        <?php foreach ($supervisors as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="date" name="deadline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('create-modal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Modals -->
<?php foreach ($tasks as $t): ?>
<div id="status-modal-<?= $t['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('status-modal-<?= $t['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-auto mt-32 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Update Status</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($t['name']) ?></p>
        <form method="POST">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $s === $t['status'] ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('status-modal-<?= $t['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Update</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
