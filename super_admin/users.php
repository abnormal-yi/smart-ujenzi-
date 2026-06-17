<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['role'];
    if ($userId !== $_SESSION['user_id']) {
        runQuery("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
        $success = 'User role updated!';
    } else {
        $error = 'You cannot change your own role.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)$_POST['user_id'];
    if ($userId !== $_SESSION['user_id']) {
        runQuery("DELETE FROM users WHERE id = ?", [$userId]);
        $success = 'User deleted!';
    } else {
        $error = 'You cannot delete your own account.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    runQuery("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)", [$name, $email, $password, $role]);
    $success = 'User added!';
}

$users = runQuery("SELECT * FROM users ORDER BY FIELD(role, 'super_admin', 'admin', 'project_manager', 'fundi', 'client'), name");
$roles = ['super_admin', 'admin', 'project_manager', 'fundi', 'client'];

$roleColors = [
    'super_admin' => 'bg-red-100 text-red-700',
    'admin' => 'bg-purple-100 text-purple-700',
    'project_manager' => 'bg-blue-100 text-blue-700',
    'fundi' => 'bg-green-100 text-green-700',
    'client' => 'bg-yellow-100 text-yellow-700',
];
?>

<?php if ($success): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-red-100 text-red-700 border border-red-200"><?= $error ?></div>
<?php endif; ?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600 text-sm"><?= count($users) ?> total users</p>
    <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
        + Add User
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100 bg-gray-50">
                    <th class="p-3 font-medium">ID</th>
                    <th class="p-3 font-medium">Name</th>
                    <th class="p-3 font-medium">Email</th>
                    <th class="p-3 font-medium">Role</th>
                    <th class="p-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                    <td class="p-3 text-gray-500"><?= $u['id'] ?></td>
                    <td class="p-3 text-gray-800 font-medium"><?= htmlspecialchars($u['name']) ?></td>
                    <td class="p-3 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full <?= $roleColors[$u['role']] ?? 'bg-gray-100 text-gray-700' ?>">
                            <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                        </span>
                    </td>
                    <td class="p-3">
                        <div class="flex items-center gap-2">
                            <form method="POST" class="flex items-center gap-1">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role" class="text-xs border border-gray-200 rounded px-2 py-1">
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $r)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_role" class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">Save</button>
                            </form>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" onsubmit="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>?')">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="delete_user" class="text-xs px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors">Delete</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Add User</h3>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>"><?= ucfirst(str_replace('_', ' ', $r)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" name="add_user" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">Add User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
