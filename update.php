<?php
$secret = $_GET['key'] ?? '';
if ($secret !== 'admin123') {
    die('Access denied');
}

$files = [
    'login.php'            => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/login.php',
    'otp-verify.php'       => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/otp-verify.php',
    'register.php'         => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/register.php',
    'includes/mailer.php'  => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/includes/mailer.php',
    'test-mail.php'        => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/test-mail.php',
    'fundi-register.php'   => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/fundi-register.php',
    'index.php'            => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/index.php',
    'setup.php'            => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/setup.php',
];

$base = __DIR__;
echo "<pre>\n";

foreach ($files as $path => $url) {
    $full = "$base/$path";
    $dir = dirname($full);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $content = @file_get_contents($url);
    if ($content === false) {
        echo "FAILED: $path (could not download)\n";
        continue;
    }

    $bytes = file_put_contents($full, $content);
    if ($bytes === false) {
        echo "FAILED: $path (could not write)\n";
        continue;
    }

    echo "OK: $path ($bytes bytes)\n";
}

echo "\nDone! Try logging in now.\n";
echo "</pre>";
