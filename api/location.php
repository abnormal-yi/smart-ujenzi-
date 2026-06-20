<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'regions') {
    $regions = runQuery("SELECT id, name FROM regions ORDER BY name");
    echo json_encode($regions);
    exit;
}

if ($action === 'districts' && isset($_GET['region_id'])) {
    $regionId = (int)$_GET['region_id'];
    $districts = runQuery("SELECT id, name FROM districts WHERE region_id = ? ORDER BY name", [$regionId]);
    echo json_encode($districts);
    exit;
}

if ($action === 'wards' && isset($_GET['district_id'])) {
    $districtId = (int)$_GET['district_id'];
    $wards = runQuery("SELECT id, name FROM wards WHERE district_id = ? ORDER BY name", [$districtId]);
    echo json_encode($wards);
    exit;
}

echo json_encode([]);
