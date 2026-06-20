<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($districts);
    exit;
}

if ($action === 'wards' && isset($_GET['district_id'])) {
    $districtId = (int)$_GET['district_id'];
    $stmt = getDB()->prepare("SELECT id, name FROM wards WHERE district_id = ? ORDER BY name");
    $stmt->execute([$districtId]);
    $wards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($wards);
    exit;
}

echo json_encode([]);
