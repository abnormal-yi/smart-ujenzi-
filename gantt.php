<?php
$pageTitle = 'Project Gantt Chart';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$singleProjectId = (int)($_GET['project_id'] ?? 0);

if ($singleProjectId) {
    $projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id WHERE p.id = ?", [$singleProjectId]);
    if ($role === 'project_manager') {
        $projects = array_filter($projects, fn($p) => $p['project_manager_id'] == $userId);
    }
} elseif (in_array($role, ['super_admin', 'admin'])) {
    $projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id ORDER BY p.start_date ASC");
} elseif ($role === 'project_manager') {
    $projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id WHERE p.project_manager_id = ? ORDER BY p.start_date ASC", [$userId]);
} else {
    $projects = [];
}

$minDate = null;
$maxDate = null;
foreach ($projects as &$p) {
    if ($p['start_date'] && $p['end_date']) {
        $s = strtotime($p['start_date']);
        $e = strtotime($p['end_date']);
        if ($minDate === null || $s < $minDate) $minDate = $s;
        if ($maxDate === null || $e > $maxDate) $maxDate = $e;
    }
    $p['tasks'] = runQuery("SELECT id, name, deadline, status FROM tasks WHERE project_id = ? ORDER BY deadline ASC", [$p['id']]);
}
unset($p);

if ($singleProjectId && !empty($projects)) {
    $proj = reset($projects);
    if ($proj['start_date'] && $proj['end_date']) {
        $s = strtotime($proj['start_date']);
        $e = strtotime($proj['end_date']);
        $minDate = strtotime('monday this week', $s);
        if ($minDate > $s) $minDate = strtotime('-7 days', $minDate);
        $maxDate = strtotime('sunday this week', $e);
    }
}

if (!$minDate) $minDate = time();
if (!$maxDate) $maxDate = time() + 86400 * 30;

$totalDays = max(1, ceil(($maxDate - $minDate) / 86400));
$today = time();

function pct($dateStr, $minDate, $totalDays): float {
    $ts = strtotime($dateStr);
    $day = ceil(($ts - $minDate) / 86400);
    return round(($day / $totalDays) * 100, 1);
}

$projectName = $singleProjectId && !empty($projects) ? $projects[array_key_first($projects)]['name'] ?? '' : '';

$weekRef = $singleProjectId && !empty($projects) ? $minDate : $minDate;
?>
<style>
.gantt-container { position: relative; overflow-x: auto; }
.gantt-header { display: flex; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: #fff; z-index: 10; }
.gantt-label-col { width: 250px; min-width: 250px; flex-shrink: 0; padding: 8px 12px; font-size: 12px; font-weight: 600; color: #374151; border-right: 1px solid #e5e7eb; }
.gantt-chart-col { flex: 1; position: relative; height: 48px; min-width: 600px; }
.gantt-week { position: absolute; top: 0; height: 100%; border-left: 1px solid #f3f4f6; font-size: 10px; color: #9ca3af; text-align: center; padding-top: 2px; }
.gantt-week-alt { background: #fafafa; }
.gantt-today { position: absolute; top: 0; bottom: 0; width: 2px; background: #ef4444; z-index: 5; }
.gantt-row { display: flex; border-bottom: 1px solid #f3f4f6; }
.gantt-row-label { width: 250px; min-width: 250px; flex-shrink: 0; padding: 8px 12px; font-size: 13px; border-right: 1px solid #e5e7eb; display: flex; align-items: center; gap: 6px; }
.gantt-row-chart { flex: 1; position: relative; height: 36px; min-width: 600px; }
.gantt-bar { position: absolute; top: 6px; height: 22px; border-radius: 4px; display: flex; align-items: center; padding: 0 8px; font-size: 10px; color: #fff; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 4px; cursor: pointer; transition: opacity .15s; }
.gantt-bar:hover { opacity: .85; }
.gantt-subrow { display: flex; }
.gantt-sub-label { width: 250px; min-width: 250px; flex-shrink: 0; padding: 2px 12px 2px 32px; font-size: 11px; color: #6b7280; border-right: 1px solid #f3f4f6; }
.gantt-sub-chart { flex: 1; position: relative; height: 24px; min-width: 600px; }
.gantt-task-bar { position: absolute; top: 4px; height: 16px; border-radius: 3px; min-width: 3px; }
.bg-progress { background: #3b82f6; }
.bg-completed { background: #10b981; }
.bg-pending { background: #f59e0b; }
.bg-project { background: #6366f1; }
</style>

<div class="max-w-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold"><?= $projectName ? htmlspecialchars($projectName) . ' — Gantt Chart' : 'Project Gantt Chart' ?></h1>
        <?php if ($singleProjectId): ?>
            <a href="progress.php" class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">← Back to Progress</a>
        <?php endif; ?>
    </div>
    <?php if (empty($projects)): ?>
        <p class="text-gray-500">No projects available.</p>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 gantt-container">
        <div class="gantt-header">
            <div class="gantt-label-col">Project / Tasks</div>
            <div class="gantt-chart-col" style="position:relative;">
                <?php
                $monday = strtotime('monday this week', $minDate);
                if ($monday > $minDate) $monday = strtotime('-7 days', $monday);
                $weekStarts = [];
                $cursor = $monday;
                $weekNum = 1;
                while ($cursor <= $maxDate + 86400 * 7) {
                    $weekStarts[] = $cursor;
                    $cursor = strtotime('+7 days', $cursor);
                }
                foreach ($weekStarts as $i => $ws):
                    $left = pct(date('Y-m-d', $ws), $minDate, $totalDays);
                    if ($i > 0 && $weekStarts[$i-1]) {
                        $prevLeft = pct(date('Y-m-d', $weekStarts[$i-1]), $minDate, $totalDays);
                        $w = round($left - $prevLeft, 2);
                    } else {
                        $w = 0;
                    }
                    if ($w <= 0) continue;
                    $alt = $i % 2 === 0;
                    $weekLabel = '';
                    if ($singleProjectId) {
                        $weekLabel = 'W' . $weekNum;
                        $weekNum++;
                    } else {
                        $weekLabel = date('M j', $ws);
                    }
                ?>
                <div class="gantt-week <?= $alt ? 'gantt-week-alt' : '' ?>" style="left:<?= $left ?>%;width:<?= $w ?>%;"><?= $weekLabel ?></div>
                <?php endforeach; ?>
                <?php if ($today >= $minDate && $today <= $maxDate): ?>
                <div class="gantt-today" style="left:<?= pct(date('Y-m-d', $today), $minDate, $totalDays) ?>%;"></div>
                <?php endif; ?>
            </div>
        </div>

        <?php foreach ($projects as $p):
            if (!$p['start_date'] || !$p['end_date']) continue;
            $left = pct($p['start_date'], $minDate, $totalDays);
            $width = max(1, round(pct($p['end_date'], $minDate, $totalDays) - $left, 1));
            $statusClass = match($p['status']) {
                'Completed' => 'bg-completed',
                'Ongoing', 'In Progress' => 'bg-progress',
                default => 'bg-pending'
            };
        ?>
        <div class="gantt-row">
            <div class="gantt-row-label">
                <span><?= htmlspecialchars($p['icon'] ?? '🏗️') ?></span>
                <span class="truncate"><?= htmlspecialchars($p['name']) ?></span>
                <span class="text-xs text-gray-400 ml-auto"><?= date('M j', strtotime($p['start_date'])) ?> - <?= date('M j', strtotime($p['end_date'])) ?></span>
            </div>
            <div class="gantt-row-chart">
                <div class="gantt-bar bg-project" style="left:<?= $left ?>%;width:<?= $width ?>%;" title="<?= htmlspecialchars($p['name'] . ' (' . $p['status'] . ')') ?>">
                    <?= htmlspecialchars($p['name']) ?>
                </div>
            </div>
        </div>

        <?php foreach ($p['tasks'] as $t):
            $tStart = strtotime($p['start_date']);
            if ($t['deadline']) {
                $tEnd = strtotime($t['deadline']);
            } else {
                $tEnd = $tStart + 86400 * 14;
            }
            if ($tEnd < $minDate || $tStart > $maxDate) continue;
            $tLeft = max(0, pct(date('Y-m-d', $tStart), $minDate, $totalDays));
            $tRight = min(100, pct(date('Y-m-d', $tEnd), $minDate, $totalDays));
            $tWidth = max(1, round($tRight - $tLeft, 1));
            $tClass = match($t['status']) {
                'Completed' => 'bg-completed',
                'In Progress' => 'bg-progress',
                default => 'bg-pending'
            };
        ?>
        <div class="gantt-subrow">
            <div class="gantt-sub-label">↳ <?= htmlspecialchars($t['name']) ?></div>
            <div class="gantt-sub-chart">
                <div class="gantt-task-bar <?= $tClass ?>" style="left:<?= $tLeft ?>%;width:<?= $tWidth ?>%;" title="<?= htmlspecialchars($t['name'] . ' (' . $t['status'] . ')') ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
