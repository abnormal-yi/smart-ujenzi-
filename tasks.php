<?php
$pageTitle = 'Tasks';
require_once __DIR__ . '/includes/functions.php';
requireRole(['super_admin', 'admin', 'project_manager', 'fundi']);
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_fundi'])) {
    executeQuery("UPDATE tasks SET fundi_id = ? WHERE id = ?", [$_POST['fundi_id'], $_POST['task_id']]);
    $success = 'Fundi assigned!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'status') {
    $taskId = (int)$_POST['id'];
    $status = $_POST['status'];
    if ($role === 'fundi') {
        executeQuery("UPDATE tasks SET status = ? WHERE id = ? AND fundi_id = ?", [$status, $taskId, $userId]);
    } else {
        executeQuery("UPDATE tasks SET status = ? WHERE id = ?", [$status, $taskId]);
    }
    $task = runQuery("SELECT * FROM tasks WHERE id = ?", [$taskId]);
    if ($task) {
        executeQuery("INSERT INTO notifications (user_id, message, is_read) SELECT id, ?, 0 FROM users WHERE role IN ('super_admin', 'admin', 'project_manager')",
            ["Task '{$task[0]['name']}' status updated to {$status}"]);
    }
    $success = 'Task status updated!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_media') {
    $projectId = $_POST['project_id'];
    $taskId = $_POST['task_id'] ?: null;
    $caption = $_POST['caption'] ?? '';
    $file = $_FILES['media_file'] ?? null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi'];
        if (in_array($ext, $allowed)) {
            $type = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
            $filename = uniqid('media_') . '.' . $ext;
            $dest = __DIR__ . '/public/uploads/' . $filename;
            move_uploaded_file($file['tmp_name'], $dest);
            executeQuery("INSERT INTO project_media (project_id, task_id, uploaded_by, file_path, type, caption) VALUES (?, ?, ?, ?, ?, ?)",
                [$projectId, $taskId, $_SESSION['user_id'], 'public/uploads/' . $filename, $type, $caption]);
            $success = 'Media uploaded!';
        } else {
            $success = 'Invalid file type.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    executeQuery("INSERT INTO tasks (project_id, name, description, fundi_id, deadline) VALUES (?, ?, ?, ?, ?)",
        [$_POST['project_id'], $_POST['name'], $_POST['description'], $_POST['fundi_id'] ?: null, $_POST['deadline'] ?: null]);
    $success = 'Task created!';
}

if ($role === 'super_admin' || $role === 'admin') {
    $tasks = runQuery("SELECT t.*, p.name as project_name, u.name as fundi_name FROM tasks t JOIN projects p ON t.project_id = p.id LEFT JOIN users u ON t.fundi_id = u.id ORDER BY t.deadline");
} elseif ($role === 'project_manager') {
    $tasks = runQuery("SELECT t.*, p.name as project_name, u.name as fundi_name FROM tasks t JOIN projects p ON t.project_id = p.id LEFT JOIN users u ON t.fundi_id = u.id WHERE p.project_manager_id = ? ORDER BY t.deadline", [$userId]);
} elseif ($role === 'fundi') {
    $tasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ? ORDER BY t.deadline", [$userId]);
}

$fundis = runQuery("SELECT id, name FROM users WHERE role = 'fundi'");
$projectsForForm = runQuery("SELECT id, name FROM projects ORDER BY name");
$statuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];
$statusColors = ['Not Started' => 'badge-gray', 'In Progress' => 'badge-blue', 'Completed' => 'badge-green', 'On Hold' => 'badge-red'];
$canManage = in_array($role, ['super_admin', 'admin', 'project_manager']);
?>

<?php if ($success): ?>
<div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500"><?= count($tasks) ?> total tasks</p>
        <?php if ($canManage): ?>
        <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">+ New Task</button>
        <?php endif; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Task</th>
                    <th class="pb-3 font-semibold text-gray-600">Project</th>
                    <th class="pb-3 font-semibold text-gray-600">Fundi</th>
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
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($t['fundi_name'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$t['status']] ?? 'badge-gray' ?>"><?= $t['status'] ?></span>
                    </td>
                    <td class="py-3 text-gray-600"><?= $t['deadline'] ?? '—' ?></td>
                    <td class="py-3">
                        <button onclick="openModal('status-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Status</button>
                        <?php if (in_array($role, ['fundi', 'admin', 'super_admin', 'project_manager'])): ?>
                        <button onclick="openModal('media-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-blue-300 text-blue-600 hover:bg-blue-50 transition-colors ml-1">📷</button>
                        <?php endif; ?>
                        <?php if ($canManage && !$t['fundi_id']): ?>
                        <button onclick="openModal('assign-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-green-300 text-green-600 hover:bg-green-50 transition-colors ml-1">Assign</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="create-modal" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('create-modal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 mt-16 p-6 z-10">
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
                        <?php foreach ($projectsForForm as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fundi</label>
                    <select name="fundi_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">Select fundi</option>
                        <?php foreach ($fundis as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
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

<?php foreach ($tasks as $t): ?>
<div id="status-modal-<?= $t['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('status-modal-<?= $t['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-32 p-6 z-10">
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

<div id="media-modal-<?= $t['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('media-modal-<?= $t['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-20 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Upload Media</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($t['name']) ?></p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_media">
            <input type="hidden" name="project_id" value="<?= $t['project_id'] ?>">
            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo / Video</label>
                    <input type="file" name="media_file" accept="image/*,video/*" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <input type="text" name="caption" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. Foundation digging progress">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('media-modal-<?= $t['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Upload</button>
            </div>
        </form>
    </div>
</div>

<?php if ($canManage && !$t['fundi_id']): ?>
<div id="assign-modal-<?= $t['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('assign-modal-<?= $t['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-32 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Assign Fundi</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($t['name']) ?></p>
        <form method="POST">
            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
            <select name="fundi_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                <option value="">Select fundi</option>
                <?php foreach ($fundis as $f): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('assign-modal-<?= $t['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" name="assign_fundi" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Assign</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
