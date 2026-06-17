<?php
$pageTitle = 'System Settings';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$phpVersion = phpversion();
$dbInfo = getDB()->query("SELECT VERSION() as version")->fetch();
$userCount = runQuery("SELECT COUNT(*) as c FROM users")[0]['c'];
$projectCount = runQuery("SELECT COUNT(*) as c FROM projects")[0]['c'];
$requestCount = runQuery("SELECT COUNT(*) as c FROM customer_requests")[0]['c'];
$companyCount = runQuery("SELECT COUNT(*) as c FROM companies")[0]['c'];
$taskCount = runQuery("SELECT COUNT(*) as c FROM tasks")[0]['c'];
$materialCount = runQuery("SELECT COUNT(*) as c FROM materials")[0]['c'];
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">PHP Version</p>
                <p class="text-xl font-bold text-gray-800 mt-1"><?= htmlspecialchars($phpVersion) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">MySQL Version</p>
                <p class="text-xl font-bold text-gray-800 mt-1"><?= htmlspecialchars($dbInfo['version']) ?></p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Environment</p>
                <p class="text-xl font-bold text-gray-800 mt-1 capitalize"><?= htmlspecialchars(APP_ENV) ?></p>
            </div>
            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<h3 class="text-lg font-bold text-gray-800 mb-4">Application Statistics</h3>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500">Total Users</p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $userCount ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500">Total Projects</p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $projectCount ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500">Total Requests</p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $requestCount ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500">Total Companies</p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $companyCount ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500">Total Tasks</p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $taskCount ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500">Total Materials</p>
        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $materialCount ?></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
