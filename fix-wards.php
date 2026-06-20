<?php
// ============================================================
// Fix Ward Dropdown - Run ONCE after uploading to server
// Upload to: https://trisa.luxurywebs.com/fix-wards.php
// Then visit: https://trisa.luxurywebs.com/fix-wards.php
// DELETE this file after it runs successfully!
// ============================================================

require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="background:#111;color:#0f0;padding:20px;font-size:14px;line-height:1.5">';

try {
    $db = getDB();
    
    // Step 1: Create wards table if not exists
    echo "Step 1: Creating wards table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS wards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        district_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        FOREIGN KEY(district_id) REFERENCES districts(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] wards table ready\n\n";
    
    // Step 2: Check how many wards currently exist
    $count = $db->query("SELECT COUNT(*) FROM wards")->fetchColumn();
    echo "Step 2: Current wards: $count\n";
    
    if ($count > 100) {
        echo "[OK] Ward data already exists ($count wards), nothing to do\n";
    } else {
        echo "Seeding wards...\n";
        
        // Read the seed file from the database directory
        $seedFile = __DIR__ . '/database/seed_wards.sql';
        if (!file_exists($seedFile)) {
            echo "[ERROR] database/seed_wards.sql not found!\n";
            echo "You need to pull the latest code from GitHub first.\n";
            exit;
        }
        
        // Execute the seed SQL
        $seedSQL = file_get_contents($seedFile);
        $statements = array_filter(array_map('trim', explode(';', $seedSQL)), fn($s) => $s !== '');
        $total = 0;
        foreach ($statements as $stmt) {
            $db->exec($stmt);
            // Count inserted rows
            if (stripos($stmt, 'INSERT INTO wards') === 0) {
                preg_match_all("/\((\d+),/", $stmt, $m);
                $total += count($m[1]);
            }
        }
        echo "[OK] Inserted $total wards\n";
    }
    
    // Step 3: Verify ward data exists for districts
    echo "\nStep 3: Verifying ward coverage...\n";
    $coverage = $db->query("
        SELECT d.id, d.name, COUNT(w.id) as ward_count
        FROM districts d
        LEFT JOIN wards w ON w.district_id = d.id
        GROUP BY d.id
        HAVING ward_count = 0
        ORDER BY d.name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($coverage) === 0) {
        echo "[OK] All districts have ward data!\n";
    } else {
        echo "Districts WITHOUT wards: " . count($coverage) . "\n";
        foreach (array_slice($coverage, 0, 10) as $d) {
            echo "  - {$d['name']} (id={$d['id']})\n";
        }
    }
    
    // Step 4: Verify the API works
    echo "\nStep 4: Testing API endpoint...\n";
    $testDistrict = $db->query("SELECT id FROM districts LIMIT 1")->fetchColumn();
    if ($testDistrict) {
        $testWards = $db->query("SELECT COUNT(*) FROM wards WHERE district_id = $testDistrict")->fetchColumn();
        echo "[OK] District id=$testDistrict has $testWards wards\n";
        echo "Test URL: /api/location.php?action=wards&district_id=$testDistrict\n";
    }
    
    echo "\n=== ALL DONE ===\n";
    echo "Now go to /register.php and test the ward dropdown.\n";
    echo "DELETE this file (fix-wards.php) after confirming it works!\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
