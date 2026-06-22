<?php
$pageTitle = 'Super Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$stats = runQuery("SELECT
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role='super_admin') as total_super_admins,
    (SELECT COUNT(*) FROM users WHERE role='admin') as total_admins,
    (SELECT COUNT(*) FROM users WHERE role='project_manager') as total_pms,
    (SELECT COUNT(*) FROM users WHERE role='fundi') as total_fundi,
    (SELECT COUNT(*) FROM users WHERE role='client') as total_clients,
    (SELECT COUNT(*) FROM projects) as total_projects,
    (SELECT COUNT(*) FROM customer_requests) as total_requests,
    (SELECT COUNT(*) FROM companies) as total_companies
")[0];

$recentUsers = runQuery("SELECT id, name, email, role FROM users ORDER BY id DESC LIMIT 10");
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_users'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Projects</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_projects'] ?></p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Requests</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_requests'] ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Companies</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_companies'] ?></p>
            </div>
            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Admins</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_admins'] ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Project Managers</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_pms'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500"><?= __('sa.workers') ?></p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_fundi'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Clients</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?= $stats['total_clients'] ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center text-yellow-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Users</h3>
    <?php if (empty($recentUsers)): ?>
        <p class="text-gray-500 text-sm">No users registered yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="pb-3 font-medium">ID</th>
                        <th class="pb-3 font-medium">Name</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-500"><?= $u['id'] ?></td>
                        <td class="py-3 text-gray-800 font-medium"><?= htmlspecialchars($u['name']) ?></td>
                        <td class="py-3 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?php
                                $roleColors = [
                                    'super_admin' => 'bg-red-100 text-red-700',
                                    'admin' => 'bg-purple-100 text-purple-700',
                                    'project_manager' => 'bg-blue-100 text-blue-700',
                                    'fundi' => 'bg-green-100 text-green-700',
                                    'client' => 'bg-yellow-100 text-yellow-700',
                                ];
                                echo $roleColors[$u['role']] ?? 'bg-gray-100 text-gray-700';
                            ?>"><?= ucfirst(str_replace('_', ' ', $u['role'])) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
