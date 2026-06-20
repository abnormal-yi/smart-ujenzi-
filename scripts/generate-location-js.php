<?php
// Generates assets/js/location-data.js from database seed SQL files
// Usage: php scripts/generate-location-js.php

function parseInserts(string $file, string $table): array {
    $sql = file_get_contents($file);
    // Strip SQL comments (-- ... and /* ... */)
    $sql = preg_replace('/--[^\n]*/', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $rows = [];
    // Match INSERT INTO table (cols) VALUES (row1),(row2),...;
    // Use possessive quantifier for the columns match to avoid backtracking issues
    preg_match_all('/INSERT\s+INTO\s+' . preg_quote($table) . '\s*\(([^)]+)\)\s*VALUES\s*(.*?);/si', $sql, $blocks);
    foreach ($blocks[2] as $block) {
        // Extract individual tuple contents: (val1,val2,...) not followed by (
        // We need to handle nested parentheses carefully
        $len = strlen($block);
        $depth = 0;
        $tuples = [];
        $current = '';
        for ($i = 0; $i < $len; $i++) {
            $ch = $block[$i];
            if ($ch === '(' && ($i === 0 || $block[$i-1] !== "'")) {
                if ($depth > 0) $current .= $ch;
                $depth++;
            } elseif ($ch === ')' && $depth > 0) {
                $depth--;
                if ($depth === 0) {
                    $tuples[] = $current;
                    $current = '';
                } else {
                    $current .= $ch;
                }
            } elseif ($depth > 0) {
                $current .= $ch;
            }
        }
        foreach ($tuples as $t) {
            $vals = str_getcsv($t, ',', "'");
            $vals = array_map(function($v) {
                $v = trim($v);
                $v = stripslashes($v);
                return is_numeric($v) ? (int)$v : $v;
            }, $vals);
            if (!empty($vals)) {
                $rows[] = $vals;
            }
        }
    }
    return $rows;
}

$base = __DIR__ . '/../database';
$regions = parseInserts("$base/seed_locations.sql", 'regions');
$districts = parseInserts("$base/seed_locations.sql", 'districts');
$wards = parseInserts("$base/seed_wards.sql", 'wards');

echo "Parsed " . count($regions) . " regions, " . count($districts) . " districts, " . count($wards) . " wards\n";

// Build region name lookup
$regionNames = [];
foreach ($regions as $r) {
    $regionNames[$r[0]] = $r[1];
}

// Build district name and region mapping
// Districts INSERT has (region_id, name) — no explicit id, use auto-increment order
$districtRegion = [];
$districtNames = [];
$districtWards = [];
$districtIdList = []; // index => district_name (1-based from insert order)
$idx = 1;
foreach ($districts as $d) {
    list($regionId, $name) = $d;
    $regionName = $regionNames[$regionId] ?? '';
    $districtRegion[$name] = $regionName;
    $districtNames[$name] = $name;
    $districtWards[$name] = [];
    $districtIdList[$idx] = $name;
    $idx++;
}

// Fill wards by district ID (auto-increment ID = insert order)
foreach ($districtIdList as $districtId => $name) {
    foreach ($wards as $w) {
        if ($w[0] === $districtId) {
            $districtWards[$name][] = $w[1];
        }
    }
}

// Collect unique region names in order from regions table
$regionList = [];
foreach ($regions as $r) {
    $regionList[] = $r[1];
}

// Generate JS
$js = "// Auto-generated from seed SQL files on " . date('Y-m-d H:i:s') . "\n";
$js .= "// Do not edit directly - run: php scripts/generate-location-js.php\n\n";

// Regions as array
$js .= "var TZ_REGIONS = " . json_encode($regionList, JSON_UNESCAPED_UNICODE) . ";\n\n";

// Districts as {name: region_name}
$js .= "var TZ_DISTRICTS = " . json_encode($districtRegion, JSON_UNESCAPED_UNICODE) . ";\n\n";

// Wards as {district_name: [ward_names]}
$outWards = [];
foreach ($districtWards as $dName => $wList) {
    if (!empty($wList)) {
        sort($wList);
        $outWards[$dName] = $wList;
    }
}
$js .= "var TZ_WARDS = " . json_encode($outWards, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";\n";

$outPath = __DIR__ . '/../assets/js/location-data.js';
file_put_contents($outPath, $js);

$wardCount = count($wards);
$distWithWards = count(array_filter($outWards, fn($w) => !empty($w)));
echo "Generated: $outPath\n";
echo "Regions: " . count($regionList) . "\n";
echo "Districts: " . count($districtRegion) . "\n";
echo "Wards: $wardCount (in $distWithWards districts)\n";
echo "Size: " . round(filesize($outPath) / 1024) . " KB\n";
