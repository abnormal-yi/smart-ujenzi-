<?php
$pageTitle = 'Manage Tasks';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $fundiId = !empty($_POST['fundi_id']) ? (int)$_POST['fundi_id'] : null;
    runQuery("INSERT INTO tasks (project_id, name, description, fundi_id, deadline) VALUES (?,?,?,?,?)",
        [(int)$_POST['project_id'], $_POST['name'], $_POST['description'], $fundiId, $_POST['deadline']]);
    $success = 'Task created!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_fundi'])) {
    runQuery("UPDATE tasks SET fundi_id = ? WHERE id = ?", [(int)$_POST['fundi_id'], (int)$_POST['task_id']]);
    $success = 'Fundi assigned!';
}

$myProjects = runQuery("SELECT id, name FROM projects WHERE project_manager_id = ?", [$userId]);
$fundis = runQuery("SELECT id, name FROM users WHERE role = 'fundi'");
$tasks = runQuery("SELECT t.*, p.name as project_name, u.name as fundi_name FROM tasks t JOIN projects p ON t.project_id = p.id LEFT JOIN users u ON t.fundi_id = u.id WHERE p.project_manager_id = ? ORDER BY t.deadline", [$userId]);
?>
<?php if (isset($success)): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Add Task</h3>
            <form method="POST">
                <input type="hidden" name="add_task" value="1">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <select name="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="">Select Project</option>
                            <?php foreach ($myProjects as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Task Name</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assign Fundi</label>
                        <select name="fundi_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="">Select Fundi (optional)</option>
                            <?php foreach ($fundis as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                        <input type="date" name="deadline" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">All Tasks</h3>
            <?php if (empty($tasks)): ?>
                <p class="text-gray-500 text-sm">No tasks created yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-gray-100">
                                <th class="pb-3 font-medium">Task</th>
                                <th class="pb-3 font-medium">Project</th>
                                <th class="pb-3 font-medium">Fundi</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium">Deadline</th>
                                <th class="pb-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $t): ?>
                            <tr class="border-b border-gray-50">
                                <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($t['name']) ?></td>
                                <td class="py-3 text-gray-600"><?= htmlspecialchars($t['project_name']) ?></td>
                                <td class="py-3 text-gray-600"><?= htmlspecialchars($t['fundi_name'] ?? 'Unassigned') ?></td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $t['status'] === 'Completed' ? 'bg-green-100 text-green-700' : ($t['status'] === 'In Progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>"><?= $t['status'] ?? 'Not Started' ?></span>
                                </td>
                                <td class="py-3 text-gray-500"><?= isset($t['deadline']) ? date('M j, Y', strtotime($t['deadline'])) : 'N/A' ?></td>
                                <td class="py-3">
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="assign_fundi" value="1">
                                        <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                        <select name="fundi_id" class="text-xs px-2 py-1 border border-gray-300 rounded">
                                            <option value="">Assign Fundi</option>
                                            <?php foreach ($fundis as $f): ?>
                                                <option value="<?= $f['id'] ?>" <?= $f['id'] == $t['fundi_id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="text-xs px-2 py-1 bg-slate-900 text-white rounded hover:bg-slate-800 transition-colors">Assign</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
