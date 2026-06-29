<?php
$secret = $_GET['key'] ?? '';
if ($secret !== 'admin123') {
    die('Access denied');
}

$files = [
    'update.php'                   => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/update.php',
    'login.php'                    => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/login.php',
    'otp-verify.php'               => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/otp-verify.php',
    'logout.php'                   => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/logout.php',
    'register.php'                 => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/register.php',
    'fundi-register.php'           => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/fundi-register.php',
    'forgot-password.php'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/forgot-password.php',
    'reset-password.php'           => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/reset-password.php',
    'google-login.php'             => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/google-login.php',
    'google-callback.php'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/google-callback.php',
    'gantt.php'                    => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/gantt.php',
    'lang-switch.php'              => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/lang-switch.php',
    'lang/en.php'                  => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/lang/en.php',
    'lang/sw.php'                  => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/lang/sw.php',
    'includes/mailer.php'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/includes/mailer.php',
    'includes/functions.php'       => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/includes/functions.php',
    'includes/config.php'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/includes/config.php',
    'includes/header.php'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/includes/header.php',
    'includes/footer.php'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/includes/footer.php',
    'test-mail.php'                => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/test-mail.php',
    'index.php'                    => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/index.php',
    'setup.php'                    => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/setup.php',
    'dashboard.php'                => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/dashboard.php',
    'projects.php'                 => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/projects.php',
    'tasks.php'                    => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/tasks.php',
    'materials.php'                => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/materials.php',
    'workers.php'                  => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/workers.php',
    'customer_requests.php'        => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/customer_requests.php',
    'upload_handler.php'           => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/upload_handler.php',
    'download-document.php'        => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/download-document.php',
    'pm/dashboard.php'             => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/pm/dashboard.php',
    'pm/fundi-approve.php'         => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/pm/fundi-approve.php',
    'pm/tasks.php'                 => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/pm/tasks.php',
    'pm/progress.php'              => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/pm/progress.php',
    'client/dashboard.php'         => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/client/dashboard.php',
    'client/upload-documents.php'  => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/client/upload-documents.php',
    'db-update.php'                => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/db-update.php',
    'database/schema.sql'          => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/database/schema.sql',
    'super_admin/users.php'        => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/super_admin/users.php',
    'super_admin/settings.php'     => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/super_admin/settings.php',
    'super_admin/audit-logs.php'   => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/super_admin/audit-logs.php',
    'super_admin/security-report.php' => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/super_admin/security-report.php',
    'super_admin/threat-dashboard.php' => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/super_admin/threat-dashboard.php',
    '.htaccess'                    => 'https://raw.githubusercontent.com/abnormal-yi/smart-ujenzi-/main/.htaccess',
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
