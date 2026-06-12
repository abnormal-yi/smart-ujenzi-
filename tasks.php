<?php
// Tasks management page: create tasks, update task statuses, and notify users
$pageTitle = 'Tasks';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager', 'supervisor', 'constructor']);
require_once __DIR__ . '/includes/header.php';

// Handle POST actions: create task, update status, or upload media
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create') {
        // Insert a new task linked to a project and optional supervisor
        executeQuery('INSERT INTO tasks (project_id, name, description, supervisor_id, deadline) VALUES (?, ?, ?, ?, ?)',
            [$_POST['project_id'], $_POST['name'], $_POST['description'], $_POST['supervisor_id'] ?: null, $_POST['deadline'] ?: null]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task created successfully'];
    } elseif ($_POST['action'] === 'status') {
        // Update task status and notify admin/manager users about the change
        executeQuery('UPDATE tasks SET status = ? WHERE id = ?', [$_POST['status'], $_POST['id']]);
        $task = runQuery('SELECT * FROM tasks WHERE id = ?', [$_POST['id']]);
        if ($task) {
            // Insert a notification for all admin and manager users
            executeQuery('INSERT INTO notifications (user_id, message, is_read) SELECT id, ?, 0 FROM users WHERE role IN ("admin", "manager")',
                ["Task '{$task[0]['name']}' status updated to {$_POST['status']}"]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Task status updated'];
    } elseif ($_POST['action'] === 'upload_media') {
        // Handle photo/video upload from supervisor
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
                executeQuery('INSERT INTO project_media (project_id, task_id, uploaded_by, file_path, type, caption) VALUES (?, ?, ?, ?, ?, ?)',
                    [$projectId, $taskId, $_SESSION['user_id'], 'public/uploads/' . $filename, $type, $caption]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Media uploaded'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid file type. Allowed: jpg, png, gif, webp, mp4, mov'];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Upload failed'];
        }
    }
    redirect('tasks.php');
}

// Fetch all tasks with project and supervisor names; fetch related data for forms
$tasks = runQuery('SELECT t.*, p.name as project_name, u.name as supervisor_name FROM tasks t LEFT JOIN projects p ON t.project_id = p.id LEFT JOIN users u ON t.supervisor_id = u.id ORDER BY t.id DESC');
$projects = runQuery('SELECT id, name FROM projects');
$supervisors = runQuery('SELECT id, name FROM users WHERE role IN ("admin", "manager", "supervisor")');
$role = $_SESSION['role'];
$statuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];
$statusColors = ['Not Started' => 'badge-gray', 'In Progress' => 'badge-blue', 'Completed' => 'badge-green', 'On Hold' => 'badge-red'];
?>

<!-- Tasks listing with table and create button -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500"><?= count($tasks) ?> total tasks</p>
        <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">+ New Task</button>
    </div>

    <!-- Tasks data table -->
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
                        <!-- Button to open status update modal for this task -->
                        <button onclick="openModal('status-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Status</button>
                        <?php if ($role === 'supervisor' || $role === 'admin' || $role === 'manager'): ?>
                        <button onclick="openModal('media-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-blue-300 text-blue-600 hover:bg-blue-50 transition-colors ml-1">📷</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create New Task -->
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

<!-- Modal per task: Update Status -->
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

<!-- Media upload modal -->
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
<?php endforeach; ?>

<!-- Modal toggle helper functions -->
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
