<?php
// Generates location-data.js dynamically from the database
// No build step needed — just include this as a JS script
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/javascript');
header('Cache-Control: no-cache, no-store, must-revalidate');

$db = getDB();

$regions = $db->query("SELECT id, name FROM regions ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

echo "var TZ_REGIONS = " . json_encode(array_column($regions, 'name'), JSON_UNESCAPED_UNICODE) . ";\n\n";

$districts = $db->query("SELECT d.id, d.name AS district, r.name AS region FROM districts d JOIN regions r ON r.id = d.region_id ORDER BY d.id")->fetchAll(PDO::FETCH_ASSOC);

$districtMap = [];
foreach ($districts as $d) {
    $districtMap[$d['district']] = $d['region'];
}
echo "var TZ_DISTRICTS = " . json_encode($districtMap, JSON_UNESCAPED_UNICODE) . ";\n\n";

$wards = $db->query("SELECT w.district_id, d.name AS district, w.name AS ward FROM wards w JOIN districts d ON d.id = w.district_id ORDER BY d.id, w.name")->fetchAll(PDO::FETCH_ASSOC);

$wardMap = [];
foreach ($wards as $w) {
    $wardMap[$w['district']][] = $w['ward'];
}
echo "var TZ_WARDS = " . json_encode($wardMap, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";\n";
