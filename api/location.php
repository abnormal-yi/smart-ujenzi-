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

if ($action === 'districts') {
    $regionId = $_GET['region_id'] ?? '';
    if (is_numeric($regionId)) {
        $stmt = getDB()->prepare("SELECT id, name FROM districts WHERE region_id = ? ORDER BY name");
        $stmt->execute([(int)$regionId]);
    } else {
        $stmt = getDB()->prepare("SELECT d.id, d.name FROM districts d JOIN regions r ON r.id = d.region_id WHERE r.name = ? ORDER BY d.name");
        $stmt->execute([$regionId]);
    }
    $out = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $out[] = ['id' => (int)$row['id'], 'name' => $row['name']];
    }
    echo json_encode($out);
    exit;
}

if ($action === 'wards') {
    $districtId = $_GET['district_id'] ?? '';
    if (is_numeric($districtId)) {
        $stmt = getDB()->prepare("SELECT id, name FROM wards WHERE district_id = ? ORDER BY name");
        $stmt->execute([(int)$districtId]);
    } else {
        $stmt = getDB()->prepare("SELECT w.id, w.name FROM wards w JOIN districts d ON d.id = w.district_id WHERE d.name = ? ORDER BY w.name");
        $stmt->execute([$districtId]);
    }
    $out = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $out[] = ['id' => (int)$row['id'], 'name' => $row['name']];
    }
    echo json_encode($out);
    exit;
}

echo json_encode([]);
