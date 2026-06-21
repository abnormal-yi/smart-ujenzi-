<?php
// Upload to server and visit: https://trisa.luxurywebs.com/debug-wards.php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: text/html; charset=utf-8');
echo '<pre style="background:#111;color:#eee;padding:20px;font-size:14px">';

try {
    $db = getDB();

    // 1. Does wards table exist?
    $tables = $db->query("SHOW TABLES LIKE 'wards'")->fetchAll();
    echo "1. Wards table: " . (count($tables) ? "EXISTS" : "MISSING") . "\n\n";

    // 2. Ward count
    if (count($tables)) {
        $count = $db->query("SELECT COUNT(*) FROM wards")->fetchColumn();
        echo "2. Total wards: $count\n\n";

        // 3. Districts with wards
        $withWards = $db->query("SELECT COUNT(DISTINCT district_id) FROM wards")->fetchColumn();
        echo "3. Districts with wards: $withWards\n\n";

        // 4. Sample data
        $sample = $db->query("SELECT d.id, d.name, COUNT(w.id) as wc
            FROM districts d JOIN wards w ON w.district_id = d.id
            GROUP BY d.id ORDER BY d.id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "4. First 5 districts with ward count:\n";
        foreach ($sample as $s) echo "   id={$s['id']} {$s['name']}: {$s['wc']} wards\n";

        // 5. Test Arusha City (should be id=1)
        $test = $db->prepare("SELECT COUNT(*) FROM wards WHERE district_id = ?");
        $test->execute([1]);
        echo "\n5. Wards for district_id=1 (Arusha City): {$test->fetchColumn()}\n";

        // 6. Simulate the API call
        $apiUrl = '/api/location.php?action=wards&district_id=1';
        echo "\n6. Simulated API response ($apiUrl):\n";
        $wards = $db->query("SELECT id, name FROM wards WHERE district_id = 1 ORDER BY name LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($wards, JSON_PRETTY_PRINT) . "\n";
    }

    // 7. Does seed_wards.sql exist?
    $seedFile = __DIR__ . '/database/seed_wards.sql';
    echo "\n7. seed_wards.sql: " . (file_exists($seedFile) ? "EXISTS (" . round(filesize($seedFile)/1024) . " KB)" : "MISSING!") . "\n";

    // 8. All districts
    $allDists = $db->query("SELECT id, name FROM districts ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n8. All districts (" . count($allDists) . " total):\n";
    foreach ($allDists as $d) echo "   {$d['id']}: {$d['name']}\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo '</pre>';
