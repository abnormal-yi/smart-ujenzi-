<?php
// Projects management page: list, create, and update project statuses
$pageTitle = 'Projects';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager']); // Only admin and manager can access this page
require_once __DIR__ . '/includes/header.php';

// Handle POST actions: create project or update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            // Insert a new project into the database
            executeQuery('INSERT INTO projects (name, description, manager_id, start_date, end_date) VALUES (?, ?, ?, ?, ?)',
                [$_POST['name'], $_POST['description'], $_POST['manager_id'] ?: null, $_POST['start_date'] ?: null, $_POST['end_date'] ?: null]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Project created successfully'];
        } elseif ($_POST['action'] === 'status') {
            // Update an existing project's status
            executeQuery('UPDATE projects SET status = ? WHERE id = ?', [$_POST['status'], $_POST['id']]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Project status updated'];
        }
        // Redirect back to the projects list after processing
        redirect('projects.php');
    }
}

// Fetch all projects with their manager names; fetch available managers and status options
$projects = runQuery('SELECT p.*, u.name as manager_name FROM projects p LEFT JOIN users u ON p.manager_id = u.id ORDER BY p.id DESC');
$managers = runQuery('SELECT id, name FROM users WHERE role IN ("admin", "manager")');
$statuses = ['Pending', 'Ongoing', 'In Progress', 'Completed', 'On Hold'];
// CSS class mapping for each status badge
$statusColors = ['Pending' => 'badge-yellow', 'Ongoing' => 'badge-blue', 'In Progress' => 'badge-indigo', 'Completed' => 'badge-green', 'On Hold' => 'badge-red'];
?>

<!-- Projects listing with table and create button -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-500"><?= count($projects) ?> total projects</p>
        </div>
        <!-- Button to open the "New Project" creation modal -->
        <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
            + New Project
        </button>
    </div>

    <!-- Projects data table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Name</th>
                    <th class="pb-3 font-semibold text-gray-600">Manager</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Start</th>
                    <th class="pb-3 font-semibold text-gray-600">End</th>
                    <th class="pb-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 font-medium"><?= htmlspecialchars($p['name']) ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($p['manager_name'] ?? '—') ?></td>
                    <td class="py-3">
                        <span class="badge <?= $statusColors[$p['status']] ?? 'badge-gray' ?>"><?= $p['status'] ?></span>
                    </td>
                    <td class="py-3 text-gray-600"><?= $p['start_date'] ?? '—' ?></td>
                    <td class="py-3 text-gray-600"><?= $p['end_date'] ?? '—' ?></td>
                    <td class="py-3">
                        <!-- Button to open status update modal for this project -->
                        <button onclick="openModal('status-modal-<?= $p['id'] ?>')" class="text-xs px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition-colors">Status</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create New Project -->
<div id="create-modal" class="modal fixed inset-0 z-50 hidden">
    <!-- Overlay background that closes modal on click -->
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('create-modal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-auto mt-20 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-4">New Project</h3>
        <form method="POST">
            <!-- Hidden action field tells the server this is a create operation -->
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                    <select name="manager_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">Select manager</option>
                        <?php foreach ($managers as $m): ?>
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
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('create-modal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal per project: Update Status -->
<?php foreach ($projects as $p): ?>
<div id="status-modal-<?= $p['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('status-modal-<?= $p['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-auto mt-32 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Update Status</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($p['name']) ?></p>
        <form method="POST">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <!-- Dropdown to select new status, preselects current value -->
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

<!-- Modal toggle helper functions -->
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
