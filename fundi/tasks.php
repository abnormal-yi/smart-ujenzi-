<?php
$pageTitle = 'My Tasks';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['fundi']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $taskId = (int)$_POST['task_id'];
    $status = $_POST['status'];
    executeQuery("UPDATE tasks SET status = ? WHERE id = ? AND fundi_id = ?", [$status, $taskId, $userId]);
    $success = 'Task status updated!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $taskId = (int)$_POST['task_id'];
    $task = runQuery("SELECT project_id FROM tasks WHERE id = ? AND fundi_id = ?", [$taskId, $userId])[0] ?? null;
    if ($task) {
        $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . $ext;
        move_uploaded_file($_FILES['media']['tmp_name'], __DIR__ . '/../public/uploads/' . $filename);
        executeQuery("INSERT INTO project_media (project_id, task_id, uploaded_by, file_path, type, caption) VALUES (?,?,?,?,?,?)",
            [$task['project_id'], $taskId, $userId, 'public/uploads/' . $filename, in_array(strtolower($ext), ['mp4','webm','mov']) ? 'video' : 'image', $_POST['caption'] ?? '']);
        $success = 'Photo uploaded!';
    } else {
        $error = 'Task not found or not assigned to you.';
    }
}

$tasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ? ORDER BY t.deadline", [$userId]);
$statuses = ['Not Started', 'In Progress', 'Completed', 'On Hold'];
?>

<?php if ($success): ?>
<div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <p class="text-sm text-gray-500 mb-6"><?= count($tasks) ?> total tasks</p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Task</th>
                    <th class="pb-3 font-semibold text-gray-600">Project</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Deadline</th>
                    <th class="pb-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 font-medium"><?= htmlspecialchars($t['name']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($t['project_name']) ?></td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full <?= $t['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($t['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : ($t['status'] === 'On Hold' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) ?>"><?= $t['status'] ?></span>
                    </td>
                    <td class="py-3 <?= isset($t['deadline']) && strtotime($t['deadline']) < time() && $t['status'] !== 'Completed' ? 'text-red-600 font-medium' : 'text-gray-600' ?>"><?= $t['deadline'] ?? 'N/A' ?></td>
                    <td class="py-3 flex gap-1">
                        <button onclick="openModal('status-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Status</button>
                        <button onclick="openModal('media-modal-<?= $t['id'] ?>')" class="text-xs px-3 py-1 rounded border border-blue-300 text-blue-600 hover:bg-blue-50 transition-colors">Upload</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($tasks as $t): ?>
<div id="status-modal-<?= $t['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('status-modal-<?= $t['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-32 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Update Status</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($t['name']) ?></p>
        <form method="POST">
            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $s === $t['status'] ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('status-modal-<?= $t['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" name="update_status" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="media-modal-<?= $t['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('media-modal-<?= $t['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-20 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Upload Photo</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($t['name']) ?></p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo / Video</label>
                    <input type="file" name="media" accept="image/*,video/*" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
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

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
