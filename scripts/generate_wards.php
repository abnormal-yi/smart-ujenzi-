<?php
$seed = file_get_contents('/home/yichang/smart-ujenzi-php/database/seed_locations.sql');

// Parse districts — the SQL has entries like: (1, 'Arusha City'), (1, 'Arusha Rural'),
// Some names contain escaped quotes: (4, 'Nyang\'hwale'),
$ourDistricts = [];

preg_match("/INSERT INTO districts.*?VALUES\s*--.*?\n(.*?);/s", $seed, $matches);
$valuesBlock = $matches[1];

// Extract all parenthesized pairs using regex
// Matches (digits, 'name') where name may contain SQL-escaped quotes
preg_match_all("/\((\d+),\s*'((?:[^'\\\\]|\\\\'|\\\\.)*)'\)/", $valuesBlock, $entries, PREG_SET_ORDER);

$nextId = 1;
foreach ($entries as $e) {
    $name = str_replace("\\'", "'", $e[2]);
    $ourDistricts[$name] = $nextId++;
}

echo "Total districts parsed: " . count($ourDistricts) . "\n";
echo "Nyang'hwale: " . (isset($ourDistricts["Nyang'hwale"]) ? 'YES id=' . $ourDistricts["Nyang'hwale"] : 'NO') . "\n";
echo "Wanging'ombe: " . (isset($ourDistricts["Wanging'ombe"]) ? 'YES id=' . $ourDistricts["Wanging'ombe"] : 'NO') . "\n";
echo "Arusha City: " . (isset($ourDistricts["Arusha City"]) ? 'YES id=' . $ourDistricts["Arusha City"] : 'NO') . "\n";

// Now generate ward SQL
$wards = json_decode(file_get_contents('/tmp/wards.json'), true);

$FIXED = [
    'ARUSHA' => 'Arusha City',
    'ARUMERU' => 'Arusha Rural',
    'KARATU' => 'Karatu',
    'LONGIDO' => 'Longido',
    'MONDULI' => 'Monduli',
    'NGORONGORO' => 'Ngorongoro',
    'ILALA' => 'Ilala',
    'KIGAMBONI' => 'Kigamboni',
    'KINONDONI' => 'Kinondoni',
    'TEMEKE' => 'Temeke',
    'UBUNGO' => 'Ubungo',
    'BAHI' => 'Bahi',
    'CHAMWINO' => 'Chamwino',
    'CHEMBA' => 'Chemba',
    'DODOMA' => 'Dodoma City',
    'KONDOA' => 'Kondoa',
    'KONGWA' => 'Kongwa',
    'MPWAPWA' => 'Mpwapwa',
    'BUKOMBE' => 'Bukombe',
    'CHATO' => 'Chato',
    'GEITA' => 'Geita',
    'MBOGWE' => 'Mbogwe',
    "NYANG'HWALE" => "Nyang'hwale",
    'IRINGA' => 'Iringa Municipal',
    'KILOLO' => 'Kilolo',
    'MUFINDI' => 'Mufindi',
    'BIHARAMULO' => 'Biharamulo',
    'BUKOBA' => 'Bukoba Municipal',
    'KARAGWE' => 'Karagwe',
    'KYERWA' => 'Kyerwa',
    'MISSENYI' => 'Missenyi',
    'MULEBA' => 'Muleba',
    'NGARA' => 'Ngara',
    'MICHEWENI' => 'Micheweni',
    'WETE' => 'Wete',
    'KASKAZINI A' => 'Kaskazini A',
    'KASKAZINI B' => 'Kaskazini B',
    'MLELE' => 'Mlele',
    'MPANDA CBD' => 'Mpanda',
    'MPANDA' => 'Mpimbwe',
    'TANGANYIKA' => 'Tanganyika',
    'BUHIGWE' => 'Buhigwe',
    'KAKONKO' => 'Kakonko',
    'KASULU' => 'Kasulu',
    'KIBONDO' => 'Kibondo',
    'KIGOMA CBD' => 'Kigoma Municipal',
    'KIGOMA' => 'Kigoma Rural',
    'UVINZA' => 'Uvinza',
    'HAI' => 'Hai',
    'MOSHI' => 'Moshi Municipal',
    'MWANGA' => 'Mwanga',
    'ROMBO' => 'Rombo',
    'SAME' => 'Same',
    'SIHA' => 'Siha',
    'CHAKECHAKE' => 'Chake Chake',
    'MKOANI' => 'Mkoani',
    'KILWA' => 'Kilwa',
    'LINDI' => 'Lindi Municipal',
    'LIWALE' => 'Liwale',
    'NACHINGWEA' => 'Nachingwea',
    'RUANGWA' => 'Ruangwa',
    'BABATI' => 'Babati',
    "HANANG'" => 'Hanang',
    'KITETO' => 'Kiteto',
    'MBULU' => 'Mbulu',
    'SIMANJIRO' => 'Simanjiro',
    'BUNDA' => 'Bunda',
    'BUTIAMA' => 'Butiama',
    'MUSOMA' => 'Musoma Municipal',
    'RORYA' => 'Rorya',
    'SERENGETI' => 'Serengeti',
    'TARIME' => 'Tarime',
    'CHUNYA' => 'Chunya',
    'KYELA' => 'Kyela',
    'MBARALI' => 'Mbarali',
    'MBEYA' => 'Mbeya Rural',
    'MBEYA CBD' => 'Mbeya City',
    'RUNGWE' => 'Rungwe',
    'KATI' => 'Kati',
    'KUSINI' => 'Kusini',
    'MAGHARIBI A' => 'Magharibi A',
    'MAGHARIBI B' => 'Magharibi B',
    'MJINI' => 'Mjini',
    'GAIRO' => 'Gairo',
    'KILOMBERO' => 'Kilombero',
    'KILOSA' => 'Kilosa',
    'MALINYI' => 'Malinyi',
    'MOROGORO' => 'Morogoro Municipal',
    'MVOMERO' => 'Mvomero',
    'ULANGA' => 'Ulanga',
    'MASASI' => 'Masasi',
    'MTWARA' => 'Mtwara Municipal',
    'NANYUMBU' => 'Nanyumbu',
    'NEWALA' => 'Newala',
    'TANDAHIMBA' => 'Tandahimba',
    'ILEMELA' => 'Ilemela',
    'KWIMBA' => 'Kwimba',
    'MAGU' => 'Magu',
    'MISUNGWI' => 'Misungwi',
    'NYAMAGANA' => 'Nyamagana',
    'SENGEREMA' => 'Sengerema',
    'UKEREWE' => 'Ukerewe',
    'LUDEWA' => 'Ludewa',
    'MAKETE' => 'Makete',
    'NJOMBE' => 'Njombe',
    "WANGING'OMBE" => "Wanging'ombe",
    'BAGAMOYO' => 'Bagamoyo',
    'KIBAHA' => 'Kibaha',
    'KIBAHA CBD' => 'Kibaha',
    'KIBITI' => 'Kibiti',
    'KISARAWE' => 'Kisarawe',
    'MAFIA' => 'Mafia',
    'MKURANGA' => 'Mkuranga',
    'RUFIJI' => 'Rufiji',
    'KALAMBO' => 'Kalambo',
    'NKASI' => 'Nkasi',
    'SUMBAWANGA' => 'Sumbawanga Municipal',
    'MBINGA' => 'Mbinga',
    'NAMTUMBO' => 'Namtumbo',
    'NYASA' => 'Nyasa',
    'SONGEA' => 'Songea Municipal',
    'TUNDURU' => 'Tunduru',
    'KAHAMA' => 'Kahama',
    'KISHAPU' => 'Kishapu',
    'SHINYANGA' => 'Shinyanga Municipal',
    'BARIADI' => 'Bariadi',
    'BUSEGA' => 'Busega',
    'ITILIMA' => 'Itilima',
    'MASWA' => 'Maswa',
    'MEATU' => 'Meatu',
    'IKUNGI' => 'Ikungi',
    'IRAMBA' => 'Iramba',
    'MANYONI' => 'Manyoni',
    'MKALAMA' => 'Mkalama',
    'SINGIDA' => 'Singida Municipal',
    'ILEJE' => 'Ileje',
    'MBOZI' => 'Mbozi',
    'MOMBA' => 'Momba',
    'SONGWE' => 'Songwe',
    'IGUNGA' => 'Igunga',
    'KALIUA' => 'Kaliua',
    'NZEGA' => 'Nzega',
    'SIKONGE' => 'Sikonge',
    'TABORA CBD' => 'Tabora Municipal',
    'URAMBO' => 'Urambo',
    'UYUI' => 'Uyui',
    'HANDENI' => 'Handeni',
    'KILINDI' => 'Kilindi',
    'KOROGWE' => 'Korogwe',
    'LUSHOTO' => 'Lushoto',
    'MKINGA' => 'Mkinga',
    'MUHEZA' => 'Muheza',
    'PANGANI' => 'Pangani',
    'TANGA' => 'Tanga City',
];

// Auto-match for districts whose names normalize cleanly
$autoMatch = [];
foreach ($ourDistricts as $name => $id) {
    $norm = strtoupper(str_replace(["'", "-", " "], "", $name));
    $norm = str_replace(['CBD', 'MUNICIPAL', 'RURAL', 'CITY'], '', $norm);
    $autoMatch[$norm] = $name;
}

$mapped = [];
$unmapped = [];

foreach ($wards['data'] as $ward) {
    $pName = $ward['parent']['name'];

    if (isset($FIXED[$pName])) {
        $ourName = $FIXED[$pName];
    } else {
        $norm = strtoupper(str_replace(["'", "-", " "], "", $pName));
        $norm = str_replace(['CBD', 'MUNICIPAL', 'RURAL', 'CITY'], '', $norm);
        $ourName = $autoMatch[$norm] ?? null;
    }

    if (!$ourName || !isset($ourDistricts[$ourName])) {
        $unmapped[$pName] = ($unmapped[$pName] ?? 0) + 1;
        continue;
    }

    $did = $ourDistricts[$ourName];
    $mapped[$did][] = $ward['name'];
}

echo "Wards mapped: " . array_sum(array_map('count', $mapped)) . "\n";
echo "Districts covered: " . count($mapped) . "\n";
echo "Unmapped districts: " . count($unmapped) . "\n";
foreach ($unmapped as $k => $v) echo "  $k ($v wards)\n";

// Generate SQL
$sql = "-- Tanzania wards (auto-generated from Bichwaa/mikoa dataset)\n";
$sql .= "-- " . array_sum(array_map('count', $mapped)) . " wards across " . count($mapped) . " districts\n";
$sql .= "INSERT INTO wards (district_id, name) VALUES\n";

// Build rows grouped by district
$rows = [];
ksort($mapped);
foreach ($mapped as $did => $wardNames) {
    $unique = array_unique($wardNames);
    sort($unique);
    foreach ($unique as $wn) {
        $escaped = str_replace("'", "\\'", ucwords(strtolower($wn)));
        $rows[] = "($did, '$escaped')";
    }
}
$sql .= implode(",\n", $rows) . ";\n";

file_put_contents('/home/yichang/smart-ujenzi-php/database/seed_wards.sql', $sql);
echo "Written: " . count($rows) . " rows to database/seed_wards.sql\n";
echo "File size: " . round(filesize('/home/yichang/smart-ujenzi-php/database/seed_wards.sql') / 1024) . " KB\n";
