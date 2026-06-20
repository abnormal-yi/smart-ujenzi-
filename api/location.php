<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-LiteSpeed-Cache-Control: no-cache');

$action = $_GET['action'] ?? '';

if ($action === 'regions') {
    $regions = runQuery("SELECT id, name FROM regions ORDER BY name");
    echo json_encode($regions);
    exit;
}

if ($action === 'districts' && isset($_GET['region_id'])) {
    $regionId = (int)$_GET['region_id'];
    $stmt = getDB()->prepare("SELECT id, name FROM districts WHERE region_id = ? ORDER BY name");
    $stmt->execute([$regionId]);
    $out = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $out[] = ['id' => (int)$row['id'], 'name' => $row['name']];
    }
    echo json_encode($out);
    exit;
}

if ($action === 'wards' && isset($_GET['district_id'])) {
    $districtId = (int)$_GET['district_id'];
    $stmt = getDB()->prepare("SELECT id, name FROM wards WHERE district_id = ? ORDER BY name");
    $stmt->execute([$districtId]);
    $out = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $out[] = ['id' => (int)$row['id'], 'name' => $row['name']];
    }
    echo json_encode($out);
    exit;
}

echo json_encode([]);
