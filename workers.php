<?php
require_once __DIR__ . '/includes/functions.php';
// Workers (Mafundi) & Equipment management page: add resources and assign to projects
$pageTitle = __('nav.workers');
requireRole(['admin', 'project_manager']);
require_once __DIR__ . '/includes/header.php';

// Handle resource creation (labor or equipment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    executeQuery('INSERT INTO resources (type, name, details) VALUES (?, ?, ?)',
        [$_POST['type'], $_POST['name'], $_POST['details']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Resource added'];
    redirect('workers.php');
}

// Fetch all resources and separate into labor and equipment arrays for display
$resources = runQuery('SELECT * FROM resources ORDER BY id DESC');
$labor = array_filter($resources, fn($r) => $r['type'] === 'labor');
$equipment = array_filter($resources, fn($r) => $r['type'] === 'equipment');

// Handle resource allocation to a project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'allocate') {
    // Insert an allocation record linking a resource to a project
    executeQuery('INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (?, "resource", ?, 1)',
        [$_POST['project_id'], $_POST['resource_id']]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Resource allocated to project'];
    redirect('workers.php');
}

$projects = runQuery('SELECT id, name FROM projects');
// Fetch all current allocations with resource and project names via JOINs
$allocations = runQuery('SELECT a.*, r.name as resource_name, r.type as resource_type, p.name as project_name FROM allocations a JOIN resources r ON a.item_id = r.id AND a.type = "resource" JOIN projects p ON a.project_id = p.id');
?>

<!-- Two-column layout: labor list on left, equipment list on right -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Labor (Mafundi) card list -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">👷 Labor</h3>
        <div class="space-y-3">
            <?php foreach ($labor as $l): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($l['name']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($l['details'] ?? '') ?></p>
                </div>
                <!-- Button to open assignment modal for this labor resource -->
                <button onclick="openModal('alloc-modal-<?= $l['id'] ?>')" class="text-xs px-3 py-1 bg-slate-800 text-white rounded hover:bg-slate-700">Assign</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Equipment card list -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">🔧 Equipment</h3>
        <div class="space-y-3">
            <?php foreach ($equipment as $e): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-800"><?= htmlspecialchars($e['name']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($e['details'] ?? '') ?></p>
                </div>
                <!-- Button to open assignment modal for this equipment -->
                <button onclick="openModal('alloc-modal-<?= $e['id'] ?>')" class="text-xs px-3 py-1 bg-slate-800 text-white rounded hover:bg-slate-700">Assign</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add new resource form (labor or equipment) -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">+ Add Resource</h3>
    <form method="POST" class="flex flex-wrap gap-4 items-end">
        <input type="hidden" name="action" value="create">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                <option value="labor">Labor</option>
                <option value="equipment">Equipment</option>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
            <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" placeholder="e.g. Fundi Musa">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Details</label>
            <input type="text" name="details" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" placeholder="e.g. Mason, 5 yrs exp">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm">Add</button>
    </form>
</div>

<!-- Current allocations table showing which resources are assigned to which projects -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">📋 Current Allocations</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Project</th>
                    <th class="pb-3 font-semibold text-gray-600">Resource</th>
                    <th class="pb-3 font-semibold text-gray-600">Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allocations as $a): ?>
                <tr class="border-b border-gray-100">
                    <td class="py-3"><?= htmlspecialchars($a['project_name']) ?></td>
                    <td class="py-3"><?= htmlspecialchars($a['resource_name']) ?></td>
                    <td class="py-3"><span class="badge <?= $a['resource_type'] === 'labor' ? 'badge-blue' : 'badge-amber' ?>"><?= $a['resource_type'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal per resource: Assign to Project -->
<?php foreach ($resources as $r): ?>
<div id="alloc-modal-<?= $r['id'] ?>" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('alloc-modal-<?= $r['id'] ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-32 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Assign Resource</h3>
        <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($r['name']) ?></p>
        <form method="POST">
            <input type="hidden" name="action" value="allocate">
            <input type="hidden" name="resource_id" value="<?= $r['id'] ?>">
            <!-- Dropdown to select which project to assign this resource to -->
            <select name="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                <option value="">Select project</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('alloc-modal-<?= $r['id'] ?>')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">Assign</button>
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
