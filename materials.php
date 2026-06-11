<?php
// Materials management page: add materials and adjust stock quantities
$pageTitle = 'Materials';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager', 'supervisor']);
require_once __DIR__ . '/includes/header.php';

// Handle POST actions: add new material or update stock level
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create') {
        // Insert a new material with initial quantity and low stock threshold
        executeQuery('INSERT INTO materials (name, quantity, unit, low_stock_threshold) VALUES (?, ?, ?, ?)',
            [$_POST['name'], (int)$_POST['quantity'], $_POST['unit'], (int)$_POST['low_stock_threshold']]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Material added'];
    } elseif ($_POST['action'] === 'update_stock') {
        // Adjust stock by adding (or subtracting if negative) the given amount
        executeQuery('UPDATE materials SET quantity = quantity + ? WHERE id = ?', [(int)$_POST['amount'], $_POST['id']]);
        $mat = runQuery('SELECT * FROM materials WHERE id = ?', [$_POST['id']]);
        // Send notification if stock has fallen to or below the low stock threshold
        if ($mat && $mat[0]['quantity'] <= $mat[0]['low_stock_threshold']) {
            executeQuery('INSERT INTO notifications (user_id, message, is_read) SELECT id, ?, 0 FROM users WHERE role IN ("admin", "manager")',
                ["Low stock alert: {$mat[0]['name']} (Qty: {$mat[0]['quantity']})"]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Stock updated'];
    }
    redirect('materials.php');
}

// Fetch all materials ordered by newest first
$materials = runQuery('SELECT * FROM materials ORDER BY id DESC');
?>

<!-- Materials listing with stock levels and inline stock adjustment -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500"><?= count($materials) ?> materials</p>
        <button onclick="openModal('create-modal')" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">+ Add Material</button>
    </div>

    <!-- Materials data table with stock status -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left">
                    <th class="pb-3 font-semibold text-gray-600">Material</th>
                    <th class="pb-3 font-semibold text-gray-600">Quantity</th>
                    <th class="pb-3 font-semibold text-gray-600">Unit</th>
                    <th class="pb-3 font-semibold text-gray-600">Low Stock At</th>
                    <th class="pb-3 font-semibold text-gray-600">Status</th>
                    <th class="pb-3 font-semibold text-gray-600">Adjust Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $m): ?>
                <?php $isLow = $m['quantity'] <= $m['low_stock_threshold']; ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 font-medium"><?= htmlspecialchars($m['name']) ?></td>
                    <!-- Highlight quantity in red if below threshold -->
                    <td class="py-3 font-bold <?= $isLow ? 'text-red-600' : 'text-gray-800' ?>"><?= $m['quantity'] ?></td>
                    <td class="py-3 text-gray-600"><?= htmlspecialchars($m['unit']) ?></td>
                    <td class="py-3 text-gray-600"><?= $m['low_stock_threshold'] ?></td>
                    <td class="py-3">
                        <?php if ($isLow): ?>
                            <span class="badge badge-red">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-green">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3">
                        <!-- Inline form to add or subtract stock quantity -->
                        <form method="POST" class="flex items-center space-x-2" onsubmit="return confirm('Adjust stock?')">
                            <input type="hidden" name="action" value="update_stock">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <input type="number" name="amount" placeholder="+/- qty" required
                                   class="w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-slate-500">
                            <button type="submit" class="text-xs px-2 py-1 bg-slate-800 text-white rounded hover:bg-slate-700">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Material -->
<div id="create-modal" class="modal fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50" onclick="closeModal('create-modal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 mt-20 p-6 z-10">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Add Material</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Material Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <select name="unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="Bags">Bags</option>
                            <option value="Pieces">Pieces</option>
                            <option value="Tons">Tons</option>
                            <option value="Buckets">Buckets</option>
                            <option value="Litres">Litres</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="10" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal('create-modal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal toggle helper functions -->
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
