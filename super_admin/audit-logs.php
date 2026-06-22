<?php
$pageTitle = 'Audit Logs';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$action = $_GET['action'] ?? '';
$userId = $_GET['user_id'] ?? '';
$severity = $_GET['severity'] ?? '';
$search = $_GET['search'] ?? '';
$days = (int)($_GET['days'] ?? 7);
if ($days < 1) $days = 1;

$where = [];
$params = [];
$where[] = "a.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
$params[] = $days;

if ($action) { $where[] = "a.action = ?"; $params[] = $action; }
if ($userId) { $where[] = "a.user_id = ?"; $params[] = $userId; }
if ($severity) { $where[] = "a.severity = ?"; $params[] = $severity; }
if ($search) { $where[] = "(a.details LIKE ? OR a.user_name LIKE ? OR a.user_email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereClause = implode(' AND ', $where);
$logs = runQuery("SELECT a.* FROM audit_logs a WHERE $whereClause ORDER BY a.created_at DESC LIMIT 500", $params);
$actions = runQuery("SELECT DISTINCT action FROM audit_logs ORDER BY action");
$users = runQuery("SELECT DISTINCT a.user_id, a.user_name, a.user_email FROM audit_logs a WHERE a.user_id IS NOT NULL ORDER BY a.user_name");
$total = runQuery("SELECT COUNT(*) as c FROM audit_logs")[0]['c'] ?? 0;
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex flex-wrap items-center justify-between mb-4 gap-4">
        <div>
            <p class="text-sm text-gray-500"><?= number_format($total) ?> total events recorded</p>
        </div>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="days" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="1" <?= $days === 1 ? 'selected' : '' ?>>Last 24 hours</option>
                <option value="7" <?= $days === 7 ? 'selected' : '' ?>>Last 7 days</option>
                <option value="30" <?= $days === 30 ? 'selected' : '' ?>>Last 30 days</option>
                <option value="90" <?= $days === 90 ? 'selected' : '' ?>>Last 90 days</option>
                <option value="365" <?= $days === 365 ? 'selected' : '' ?>>Last year</option>
            </select>
            <select name="action" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All actions</option>
                <?php foreach ($actions as $a): ?>
                    <option value="<?= $a['action'] ?>" <?= $action === $a['action'] ? 'selected' : '' ?>><?= $a['action'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="severity" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All severity</option>
                <option value="info" <?= $severity === 'info' ? 'selected' : '' ?>>Info</option>
                <option value="warning" <?= $severity === 'warning' ? 'selected' : '' ?>>Warning</option>
                <option value="critical" <?= $severity === 'critical' ? 'selected' : '' ?>>Critical</option>
            </select>
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">Filter</button>
            <a href="audit-logs.php" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Reset</a>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="pb-3 pr-4 font-semibold">Time</th>
                    <th class="pb-3 pr-4 font-semibold">User</th>
                    <th class="pb-3 pr-4 font-semibold">Role</th>
                    <th class="pb-3 pr-4 font-semibold">Action</th>
                    <th class="pb-3 pr-4 font-semibold">Severity</th>
                    <th class="pb-3 pr-4 font-semibold">Details</th>
                    <th class="pb-3 font-semibold">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="py-8 text-center text-gray-400">No audit logs found for the selected filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                            <td class="py-3 pr-4 text-gray-600 whitespace-nowrap"><?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></td>
                            <td class="py-3 pr-4">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($log['user_name'] ?? '—') ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($log['user_email'] ?? '') ?></div>
                            </td>
                            <td class="py-3 pr-4"><span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"><?= $log['user_role'] ?? '—' ?></span></td>
                            <td class="py-3 pr-4 font-mono text-xs text-gray-600"><?= htmlspecialchars($log['action']) ?></td>
                            <td class="py-3 pr-4">
                                <?php
                                $sevClass = ['info' => 'bg-blue-100 text-blue-700', 'warning' => 'bg-yellow-100 text-yellow-700', 'critical' => 'bg-red-100 text-red-700'];
                                $cls = $sevClass[$log['severity']] ?? 'bg-gray-100 text-gray-600';
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?= $cls ?>"><?= $log['severity'] ?></span>
                            </td>
                            <td class="py-3 pr-4 max-w-xs">
                                <div class="text-gray-600 truncate" title="<?= htmlspecialchars($log['details'] ?? '') ?>"><?= htmlspecialchars($log['details'] ?? '—') ?></div>
                            </td>
                            <td class="py-3 text-xs text-gray-500 font-mono"><?= htmlspecialchars($log['ip_address'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
