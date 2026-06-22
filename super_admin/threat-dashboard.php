<?php
$pageTitle = 'Threat Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$days = (int)($_GET['days'] ?? 7);
if ($days < 1) $days = 1;

// Stats
$failedLogins = runQuery("SELECT COUNT(*) as c FROM audit_logs WHERE action LIKE 'login_failed%' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [$days])[0]['c'] ?? 0;
$blocked = runQuery("SELECT COUNT(*) as c FROM audit_logs WHERE action LIKE 'login_blocked%' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [$days])[0]['c'] ?? 0;
$warnings = runQuery("SELECT COUNT(*) as c FROM audit_logs WHERE severity = 'warning' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [$days])[0]['c'] ?? 0;
$totEvents = runQuery("SELECT COUNT(*) as c FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)", [$days])[0]['c'] ?? 0;

// Brute force candidates (failed logins by IP)
$byIP = runQuery("SELECT ip_address, COUNT(*) as attempts FROM audit_logs WHERE action LIKE 'login_failed%' AND ip_address IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY ip_address HAVING attempts >= 3 ORDER BY attempts DESC LIMIT 20", [$days]);

// Recent suspicious events
$recentAlerts = runQuery("SELECT * FROM audit_logs WHERE severity IN ('warning','critical') AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY created_at DESC LIMIT 50", [$days]);

// Most active users
$activeUsers = runQuery("SELECT user_name, user_email, COUNT(*) as actions FROM audit_logs WHERE user_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY user_id ORDER BY actions DESC LIMIT 10", [$days]);
?>
<div class="space-y-6">
    <div class="flex justify-end"><form method="GET"><select name="days" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()"><option value="1" <?= $days === 1 ? 'selected' : '' ?>>24h</option><option value="7" <?= $days === 7 ? 'selected' : '' ?>>7 days</option><option value="30" <?= $days === 30 ? 'selected' : '' ?>>30 days</option></select></form></div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"><div class="text-3xl font-bold text-red-600"><?= $failedLogins ?></div><div class="text-sm text-gray-500 mt-1">Failed Logins</div></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"><div class="text-3xl font-bold text-orange-600"><?= $blocked ?></div><div class="text-sm text-gray-500 mt-1">Blocked Attempts</div></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"><div class="text-3xl font-bold text-yellow-600"><?= $warnings ?></div><div class="text-sm text-gray-500 mt-1">Warnings</div></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"><div class="text-3xl font-bold text-blue-600"><?= $totEvents ?></div><div class="text-sm text-gray-500 mt-1">Total Events</div></div>
    </div>

    <?php if (!empty($byIP)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-800 mb-4">Potential Brute Force Sources</h3>
        <div class="overflow-x-auto text-sm">
            <table class="w-full">
                <thead><tr class="text-left text-gray-500 border-b"><th class="pb-3 pr-4 font-semibold">IP Address</th><th class="pb-3 pr-4 font-semibold">Failed Attempts</th><th class="pb-3 font-semibold">Risk Level</th></tr></thead>
                <tbody>
                <?php foreach ($byIP as $row): ?>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 pr-4 font-mono text-xs"><?= htmlspecialchars($row['ip_address']) ?></td>
                        <td class="py-3 pr-4"><?= $row['attempts'] ?></td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $row['attempts'] >= 20 ? 'bg-red-100 text-red-700' : ($row['attempts'] >= 10 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700') ?>">
                                <?= $row['attempts'] >= 20 ? 'HIGH' : ($row['attempts'] >= 10 ? 'MEDIUM' : 'LOW') ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Recent Security Alerts</h3>
            <?php if (empty($recentAlerts)): ?>
                <p class="text-gray-400 text-sm">No alerts in this period.</p>
            <?php else: ?>
                <div class="space-y-2 max-h-96 overflow-y-auto text-sm">
                    <?php foreach ($recentAlerts as $a): ?>
                        <div class="flex items-start gap-3 p-2 rounded-lg <?= $a['severity'] === 'critical' ? 'bg-red-50' : 'bg-yellow-50' ?>">
                            <span class="text-xs font-mono text-gray-400 whitespace-nowrap w-24"><?= date('M j, g:i A', strtotime($a['created_at'])) ?></span>
                            <span class="font-medium text-gray-700 w-20 text-xs"><?= $a['action'] ?></span>
                            <span class="text-gray-600 flex-1 truncate"><?= htmlspecialchars($a['details'] ?? '') ?></span>
                            <span class="text-xs font-medium <?= $a['severity'] === 'critical' ? 'text-red-600' : 'text-orange-600' ?>"><?= $a['severity'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Most Active Users</h3>
            <?php if (empty($activeUsers)): ?>
                <p class="text-gray-400 text-sm">No user activity in this period.</p>
            <?php else: ?>
                <div class="space-y-2 text-sm">
                    <?php foreach ($activeUsers as $u): ?>
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div>
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($u['user_name'] ?? '—') ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($u['user_email'] ?? '') ?></div>
                            </div>
                            <span class="font-bold text-gray-700"><?= $u['actions'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
