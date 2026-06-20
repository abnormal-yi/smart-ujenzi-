<?php
// Database + SMTP overrides for production.
// Copy credentials from your hosting control panel.
// This file is gitignored — safe to keep passwords here.

define('OVERRIDE_DB_HOST', '');
define('OVERRIDE_DB_NAME', '');
define('OVERRIDE_DB_USER', '');
define('OVERRIDE_DB_PASS', '');
define('OVERRIDE_DB_SOCKET', '');

define('OVERRIDE_SMTP_HOST', '');
define('OVERRIDE_SMTP_PORT', 465);
define('OVERRIDE_SMTP_USER', '');
define('OVERRIDE_SMTP_PASS', '');
define('OVERRIDE_SMTP_FROM', 'noreply@yourdomain.com');
define('OVERRIDE_SMTP_FROM_NAME', 'SmartUjenzi');
define('OVERRIDE_APP_URL', 'https://yourdomain.com');
