<?php
$pageTitle = 'Security Reports';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$tab = $_GET['tab'] ?? '1';
$days = (int)($_GET['days'] ?? 30);
if ($days < 1) $days = 1;
$since = date('Y-m-d H:i:s', strtotime("-$days days"));

function loadLogs(string $actionPattern, int $days): array {
    return runQuery("SELECT * FROM audit_logs WHERE action LIKE ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY created_at DESC LIMIT 200", [$actionPattern, $days]);
}
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-1">
            <a href="?tab=1&days=<?= $days ?>" class="px-4 py-2 text-sm rounded-lg <?= $tab === '1' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">Gen. Analysis</a>
            <a href="?tab=2&days=<?= $days ?>" class="px-4 py-2 text-sm rounded-lg <?= $tab === '2' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">Incident Report</a>
            <a href="?tab=3&days=<?= $days ?>" class="px-4 py-2 text-sm rounded-lg <?= $tab === '3' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">Audit Report</a>
            <a href="?tab=4&days=<?= $days ?>" class="px-4 py-2 text-sm rounded-lg <?= $tab === '4' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">Threat Hunt</a>
            <a href="?tab=5&days=<?= $days ?>" class="px-4 py-2 text-sm rounded-lg <?= $tab === '5' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' ?>">Forensic</a>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="<?= $tab ?>">
            <select name="days" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                <option value="7" <?= $days === 7 ? 'selected' : '' ?>>7 days</option>
                <option value="30" <?= $days === 30 ? 'selected' : '' ?>>30 days</option>
                <option value="90" <?= $days === 90 ? 'selected' : '' ?>>90 days</option>
            </select>
        </form>
    </div>

<?php if ($tab === '1'): /* Prompt 1: General Log Analysis */ ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">1. General Log Analysis Report</h3>
        <?php
        $failed = loadLogs('login_failed%', $days);
        $successLogin = loadLogs('login', $days);
        $registrations = loadLogs('user_registered%', $days);
        $approvals = loadLogs('user_approved%', $days);
        $alerts = loadLogs('%', $days);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-red-50 rounded-lg"><div class="text-2xl font-bold text-red-600"><?= count($failed) ?></div><div class="text-sm text-red-500">Failed Logins</div></div>
            <div class="p-4 bg-green-50 rounded-lg"><div class="text-2xl font-bold text-green-600"><?= count($successLogin) ?></div><div class="text-sm text-green-500">Successful Logins</div></div>
            <div class="p-4 bg-blue-50 rounded-lg"><div class="text-2xl font-bold text-blue-600"><?= count($registrations) ?></div><div class="text-sm text-blue-500">New Registrations</div></div>
            <div class="p-4 bg-purple-50 rounded-lg"><div class="text-2xl font-bold text-purple-600"><?= count($approvals) ?></div><div class="text-sm text-purple-500">Approvals/Changes</div></div>
        </div>
        <h4 class="font-semibold text-gray-700 mb-2">Suspicious Activities</h4>
        <?php $suspicious = array_filter($alerts, fn($l) => $l['severity'] === 'warning' || $l['severity'] === 'critical'); ?>
        <?php if (empty($suspicious)): ?>
            <p class="text-gray-400 text-sm">No suspicious activities detected in the selected period.</p>
        <?php else: ?>
            <div class="overflow-x-auto text-sm">
                <table class="w-full"><thead><tr class="text-left text-gray-500 border-b"><th class="pb-2 pr-4">Time</th><th class="pb-2 pr-4">User</th><th class="pb-2 pr-4">Action</th><th class="pb-2 pr-4">Severity</th><th class="pb-2">Details</th></tr></thead><tbody>
                <?php foreach ($suspicious as $l): ?>
                    <tr class="border-b border-gray-50"><td class="py-2 pr-4 text-xs text-gray-500"><?= date('M j, g:i A', strtotime($l['created_at'])) ?></td><td class="py-2 pr-4"><?= htmlspecialchars($l['user_name'] ?? '—') ?></td><td class="py-2 pr-4 font-mono text-xs"><?= $l['action'] ?></td><td class="py-2 pr-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $l['severity'] === 'warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700' ?>"><?= $l['severity'] ?></span></td><td class="py-2 text-xs text-gray-600 max-w-xs truncate"><?= htmlspecialchars($l['details'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($tab === '2'): /* Prompt 2: Security Incident Investigation */ ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">2. Security Incident Investigation Report</h3>
        <p class="text-sm text-gray-400 mb-6">Period: <?= date('M j, Y', strtotime($since)) ?> – <?= date('M j, Y') ?></p>
        <?php
        $critical = loadLogs('%', $days);
        $critical = array_filter($critical, fn($l) => $l['severity'] === 'critical' || $l['severity'] === 'warning');
        $uniqueIPs = array_unique(array_filter(array_column($critical, 'ip_address')));
        $affectedUsers = array_unique(array_filter(array_column($critical, 'user_id')));
        $iocs = array_filter($critical, fn($l) => str_contains($l['action'] ?? '', 'failed') || str_contains($l['action'] ?? '', 'blocked'));
        ?>
        <div class="space-y-4 text-sm">
            <div class="p-4 bg-gray-50 rounded-lg"><strong>What happened:</strong> <?= count($critical) ?> security events detected in the review period.</div>
            <div class="p-4 bg-gray-50 rounded-lg"><strong>When it happened:</strong> <?= date('M j, Y', strtotime($since)) ?> to <?= date('M j, Y') ?></div>
            <div class="p-4 bg-gray-50 rounded-lg"><strong>Who was involved:</strong> <?= count($affectedUsers) ?> user(s) triggered security events.</div>
            <div class="p-4 bg-gray-50 rounded-lg"><strong>Source IP addresses:</strong> <?= implode(', ', $uniqueIPs ?: ['None recorded']) ?></div>
            <div class="p-4 bg-gray-50 rounded-lg"><strong>Affected systems:</strong> SmartUjenzi Application (user authentication, registration, task management)</div>
            <div class="p-4 bg-gray-50 rounded-lg"><strong>Indicators of Compromise (IOCs):</strong> <?= count($iocs) ?> failed/blocked attempts, <?= count($uniqueIPs) ?> unique IPs</div>
            <div class="p-4 bg-gray-50 rounded-lg"><strong>Severity level:</strong> <?= count(array_filter($critical, fn($l) => $l['severity'] === 'critical')) > 0 ? 'HIGH' : (count($iocs) > 5 ? 'MEDIUM' : 'LOW') ?></div>
        </div>
    </div>

<?php elseif ($tab === '3'): /* Prompt 3: Audit Report Generation */ ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">3. Audit Report</h3>
        <p class="text-sm text-gray-400 mb-4">Period: <?= date('M j, Y', strtotime($since)) ?> – <?= date('M j, Y') ?></p>
        <?php
        $all = loadLogs('%', $days);
        $actions = [];
        foreach ($all as $l) $actions[$l['action']] = ($actions[$l['action']] ?? 0) + 1;
        $byUser = [];
        foreach ($all as $l) {
            $k = $l['user_name'] ?? 'System';
            $byUser[$k] = ($byUser[$k] ?? 0) + 1;
        }
        arsort($actions);
        arsort($byUser);
        ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Activity by Type</h4>
                <div class="space-y-2 text-sm">
                    <?php foreach (array_slice($actions, 0, 15) as $act => $count): ?>
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded"><span class="text-gray-600 font-mono text-xs"><?= $act ?></span><span class="font-bold text-gray-800"><?= $count ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Activity by User</h4>
                <div class="space-y-2 text-sm">
                    <?php foreach (array_slice($byUser, 0, 15) as $user => $count): ?>
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded"><span class="text-gray-600"><?= htmlspecialchars($user) ?></span><span class="font-bold text-gray-800"><?= $count ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php if (count($iocs ?? []) > 0): ?>
            <div class="mt-6 p-4 bg-red-50 rounded-lg">
                <h4 class="font-semibold text-red-700 mb-2">Security Violations</h4>
                <p class="text-sm text-red-600"><?= count($iocs) ?> events classified as security violations in this period.</p>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($tab === '4'): /* Prompt 4: Threat Hunting */ ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">4. Threat Hunting Report</h3>
        <p class="text-sm text-gray-400 mb-4">Scan period: <?= date('M j, Y', strtotime($since)) ?> – <?= date('M j, Y') ?></p>
        <?php
        $failed = loadLogs('login_failed%', $days);
        $blocked = loadLogs('login_blocked%', $days);
        $byIP = [];
        foreach ($failed as $l) {
            $ip = $l['ip_address'] ?? 'unknown';
            $byIP[$ip] = ($byIP[$ip] ?? 0) + 1;
        }
        arsort($byIP);
        $bruteForce = array_filter($byIP, fn($c) => $c >= 3);
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-lg bg-red-50"><div class="text-2xl font-bold text-red-600"><?= count($failed) ?></div><div class="text-sm text-red-500">Failed Logins</div></div>
            <div class="p-4 rounded-lg bg-orange-50"><div class="text-2xl font-bold text-orange-600"><?= count($blocked) ?></div><div class="text-sm text-orange-500">Blocked Attempts</div></div>
            <div class="p-4 rounded-lg bg-yellow-50"><div class="text-2xl font-bold text-yellow-600"><?= count($bruteForce) ?></div><div class="text-sm text-yellow-500">Brute Force Sources</div></div>
        </div>
        <?php if (!empty($bruteForce)): ?>
            <h4 class="font-semibold text-gray-700 mb-2">Brute Force Attacks Detected</h4>
            <div class="overflow-x-auto text-sm"><table class="w-full"><thead><tr class="text-left text-gray-500 border-b"><th class="pb-2 pr-4">IP Address</th><th class="pb-2 pr-4">Attempts</th><th class="pb-2">Confidence</th></tr></thead><tbody>
            <?php foreach ($bruteForce as $ip => $count): ?>
                <tr class="border-b border-gray-50"><td class="py-2 pr-4 font-mono text-xs"><?= htmlspecialchars($ip) ?></td><td class="py-2 pr-4"><?= $count ?></td><td class="py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $count >= 10 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' ?>"><?= $count >= 10 ? 'HIGH' : 'MEDIUM' ?></span></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?>
            <p class="text-gray-400 text-sm">No brute force patterns detected.</p>
        <?php endif; ?>
    </div>

<?php elseif ($tab === '5'): /* Prompt 5: Advanced Forensic Analysis */ ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-2">5. Advanced Forensic Analysis</h3>
        <p class="text-sm text-gray-400 mb-6">Investigation period: <?= date('M j, Y', strtotime($since)) ?> – <?= date('M j, Y') ?></p>
        <?php
        $all = runQuery("SELECT * FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY created_at ASC", [$days]);
        $timeline = [];
        foreach ($all as $l) $timeline[] = ['time' => $l['created_at'], 'event' => $l['action'] . ' — ' . ($l['user_name'] ?? 'System'), 'details' => $l['details'] ?? ''];

        $mitre = [];
        foreach ($all as $l) {
            if (str_contains($l['action'], 'login_failed')) $mitre['T1110 (Brute Force)'][] = $l;
            if (str_contains($l['action'], 'login_blocked')) $mitre['T1078 (Valid Accounts)'][] = $l;
            if ($l['severity'] === 'warning' || $l['severity'] === 'critical') $mitre['T1059 (Command & Scripting)'][] = $l;
        }
        ?>
        <div class="space-y-6">
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Complete Timeline</h4>
                <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg text-sm">
                    <?php if (empty($timeline)): ?>
                        <p class="p-4 text-gray-400">No events in this period.</p>
                    <?php else: ?>
                        <?php foreach ($timeline as $t): ?>
                            <div class="flex gap-3 p-2 border-b border-gray-50 hover:bg-gray-50">
                                <span class="text-xs text-gray-400 whitespace-nowrap w-28"><?= date('M j, g:i A', strtotime($t['time'])) ?></span>
                                <span class="text-gray-700 flex-1"><?= htmlspecialchars($t['event']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">MITRE ATT&CK Mapping</h4>
                <?php if (empty($mitre)): ?>
                    <p class="text-gray-400 text-sm">No MITRE ATT&CK techniques identified in this period.</p>
                <?php else: ?>
                    <div class="space-y-2 text-sm">
                        <?php foreach ($mitre as $technique => $events): ?>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="font-medium text-gray-800"><?= $technique ?></div>
                                <div class="text-xs text-gray-500"><?= count($events) ?> event(s) detected</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2">Containment & Remediation</h4>
                <ol class="text-sm text-gray-600 list-decimal ml-4 space-y-1">
                    <li>Review and block suspicious IP addresses showing brute force patterns.</li>
                    <li>Enforce strong password policies for all user accounts.</li>
                    <li>Enable account lockout after 5 failed login attempts.</li>
                    <li>Audit all user accounts with elevated privileges.</li>
                    <li>Implement regular security awareness training.</li>
                </ol>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
