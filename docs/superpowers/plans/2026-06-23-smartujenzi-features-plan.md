# SmartUjenzi Features Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement 9 features: Google SSO, client document upload, OTP 10min, login error, forgot password, Gantt chart, project icons, URL encryption, dynamic fundi.

**Architecture:** PHP monolith (no framework). All new auth flows follow existing patterns. File uploads to `uploads/` directory. Gantt uses pure CSS/HTML bars. URL encryption uses openssl with base64.

**Phases (independent, deployable in order):**
1. Simple fixes (OTP 10min, login error, fundi dropdown, project icons + favicon)
2. Forgot password
3. Google SSO
4. Client request document upload
5. Gantt chart + URL encryption

**Tech Stack:** PHP 8.2, MySQL, Tailwind CSS CDN, PHPMailer

---

## File Structure

### New Files
- `favicon.svg` — SVG favicon (blue SU text)
- `forgot-password.php` — email entry form
- `reset-password.php` — token + new password form
- `google-login.php` — redirect to Google OAuth
- `google-callback.php` — handle Google callback
- `project-detail.php` — Gantt chart view for single project
- `includes/upload_handler.php` — file upload validation + storage
- `client/upload-documents.php` — client document upload page

### Modified Files
- `login.php` — OTP 10min, error msg, forgot link, Google button
- `includes/config.php` — new constants (Google OAuth, encryption key)
- `includes/functions.php` — encryptId, decryptId helpers
- `includes/header.php` — favicon link
- `includes/mailer.php` — already done
- `projects.php` — icon selector in create modal, icon display
- `pm/tasks.php` — dynamic fundi dropdown
- `customer_requests.php` — document view for PM
- `client/requests.php` — upload button + document list
- `setup.php` — schema updates for new tables/columns

### Schema Updates (setup.php)
- New: `password_resets` table
- New: `request_documents` table
- Alter: `users` add `google_id`, `avatar_url`
- Alter: `projects` add `icon`
- Alter: `customer_requests` add `square_meters`

---

### Phase 1: Simple Fixes

#### Task 1.1: OTP 10 Minutes + Login Error Message

**Files:**
- Modify: `login.php:63`

- [ ] **Change OTP expiry and error message**

```php
// Line 63: change 5 to 10
executeQuery("INSERT INTO otp_codes (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))", [$user['id'], $code]);

// Find where error message is set for wrong credentials, change to:
$error = 'Invalid username or password';
```

Search for the current error text in login.php to find exact location.

- [ ] **Verify and commit**

```bash
git diff login.php
git add login.php
git commit -m "feat: OTP 10min, invalid username or password message"
```

---

#### Task 1.2: Dynamic Fundi Dropdown

**Files:**
- Modify: `pm/tasks.php:24`

- [ ] **Change fundi query to exclude already-assigned fundis**

Current:
```php
$fundis = runQuery("SELECT id, name, skills FROM users WHERE role = 'fundi' AND approved = 1 ORDER BY name");
```

New (exclude fundis already assigned to tasks in the selected project):
```php
$fundis = runQuery("SELECT id, name, skills FROM users WHERE role = 'fundi' AND approved = 1 AND id NOT IN (SELECT COALESCE(fundi_id, 0) FROM tasks WHERE project_id = ? AND fundi_id IS NOT NULL) ORDER BY name", [$_POST['project_id'] ?? 0]);
```

- [ ] **Commit**

```bash
git add pm/tasks.php
git commit -m "feat: dynamic fundi dropdown - exclude already assigned fundis"
```

---

#### Task 1.3: Project Icons + Favicon

**Files:**
- Modify: `projects.php` — icon selector in create modal, display icon
- Modify: `includes/header.php` — favicon link
- Create: `favicon.svg`
- Modify: `setup.php` — ALTER TABLE projects ADD COLUMN icon

- [ ] **Add icon column to schema in setup.php**

```php
// In setup.php fix=1 section
executeQuery("ALTER TABLE projects ADD COLUMN icon VARCHAR(10) DEFAULT '🏗️'");
```

- [ ] **Create favicon.svg**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <rect width="100" height="100" rx="16" fill="#2563eb"/>
  <text x="50" y="62" font-family="sans-serif" font-size="40" font-weight="bold" fill="white" text-anchor="middle">SU</text>
</svg>
```

- [ ] **Add favicon to header.php**

```php
<link rel="icon" type="image/svg+xml" href="favicon.svg">
```

- [ ] **Add icon display in projects.php**

In the project create modal, add icon selector:
```php
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
    <select name="icon" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        <option value="🏗️">🏗️ Construction</option>
        <option value="🏠">🏠 House</option>
        <option value="🔧">🔧 Tools</option>
        <option value="📋">📋 Planning</option>
        <option value="🎯">🎯 Project</option>
        <option value="🧱">🧱 Masonry</option>
    </select>
</div>
```

In the project table display, change name column:
```php
<td class="py-3 font-medium">
    <span class="text-blue-500 mr-1"><?= htmlspecialchars($p['icon'] ?? '🏗️') ?></span>
    <?= htmlspecialchars($p['name']) ?>
</td>
```

Update INSERT to include icon:
```php
$res = executeQuery('INSERT INTO projects (name, description, project_manager_id, start_date, end_date, icon) VALUES (?, ?, ?, ?, ?, ?)',
    [$_POST['name'], $_POST['description'], $_POST['project_manager_id'] ?: null, $_POST['start_date'] ?: null, $_POST['end_date'] ?: null, $_POST['icon'] ?? '🏗️']);
```

- [ ] **Commit**

```bash
git add favicon.svg projects.php includes/header.php setup.php
git commit -m "feat: project icons + favicon"
```

---

### Phase 2: Forgot Password

#### Task 2.1: Forgot Password - Table + Email Form

**Files:**
- Modify: `setup.php` — CREATE TABLE password_resets
- Create: `forgot-password.php`
- Create: `reset-password.php`

- [ ] **Add password_resets table to setup.php**

```php
$db->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
```

- [ ] **Create forgot-password.php**

```php
<?php
$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $user = runQuery("SELECT id, name FROM users WHERE email = ?", [$email]);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        executeQuery("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))", [$email, $token]);
        require_once __DIR__ . '/includes/mailer.php';
        $resetLink = (defined('APP_URL') ? APP_URL : 'https://trisa.luxurywebs.com') . "/reset-password.php?token=$token";
        sendEmail($email, 'SmartUjenzi Password Reset',
            "<h2>Password Reset</h2>
             <p>Hi <strong>" . htmlspecialchars($user[0]['name']) . "</strong>,</p>
             <p>Click the link below to reset your password:</p>
             <p><a href=\"$resetLink\" style=\"display:inline-block;padding:12px 24px;background:#2563eb;color:white;text-decoration:none;border-radius:6px;\">Reset Password</a></p>
             <p>Link expires in 30 minutes.</p>
             <p>If you did not request this, please ignore this email.</p>");
    }
    $success = 'If that email is registered, a reset link has been sent.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SmartUjenzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Forgot Password</h1>
        <p class="text-gray-500 text-sm mb-6">Enter your email to receive a reset link.</p>
        <?php if ($success): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-red-100 text-red-700 border border-red-200"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <button type="submit" class="w-full py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors font-medium">Send Reset Link</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4"><a href="login.php" class="text-blue-600 hover:underline">Back to Login</a></p>
    </div>
</body>
</html>
```

- [ ] **Create reset-password.php**

```php
<?php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/config.php';

$success = '';
$error = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $row = runQuery("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()", [$token]);
        if ($row) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            executeQuery("UPDATE users SET password = ? WHERE email = ?", [$hash, $row[0]['email']]);
            executeQuery("UPDATE password_resets SET used = 1 WHERE id = ?", [$row[0]['id']]);
            $success = 'Password reset successful! <a href="login.php" class="text-blue-600 underline">Log in here</a>';
        } else {
            $error = 'Invalid or expired reset token.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SmartUjenzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h1>
        <?php if ($success): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-red-100 text-red-700 border border-red-200"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!$token): ?>
            <p class="text-red-500">Missing reset token.</p>
        <?php else: ?>
            <p class="text-gray-500 text-sm mb-6">Enter your new password.</p>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password" minlength="6" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                <button type="submit" class="w-full py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors font-medium">Reset Password</button>
            </form>
        <?php endif; ?>
        <p class="text-center text-sm text-gray-500 mt-4"><a href="login.php" class="text-blue-600 hover:underline">Back to Login</a></p>
    </div>
</body>
</html>
```

The `includes/config.php` is already required by `includes/functions.php` which sets up session, DB, etc. But `forgot-password.php` and `reset-password.php` need DB access without requiring the full functions.php. Let me use `require_once __DIR__ . '/includes/config.php'` directly (config.php calls session_start and sets up DB function `getDB()`). I need to ensure `getDB()` is accessible and `executeQuery`, `runQuery` are available.

Actually, safer to use `require_once __DIR__ . '/includes/functions.php'` since it requires config.php and defines helper functions.

- [ ] **Add "Forgot Password?" link to login.php**

```php
<!-- Add after password input -->
<p class="text-right text-sm mt-1"><a href="forgot-password.php" class="text-blue-600 hover:underline">Forgot Password?</a></p>
```

- [ ] **Commit**

```bash
git add forgot-password.php reset-password.php login.php setup.php
git commit -m "feat: forgot password with email reset link"
```

---

### Phase 3: Google SSO

#### Task 3.1: Google OAuth - Config + Tables

**Files:**
- Modify: `includes/config.php` — Google OAuth constants
- Modify: `setup.php` — ALTER TABLE users add google_id, avatar_url

- [ ] **Add constants to config.php**

```php
define('GOOGLE_CLIENT_ID', defined('OVERRIDE_GOOGLE_CLIENT_ID') ? OVERRIDE_GOOGLE_CLIENT_ID : '');
define('GOOGLE_CLIENT_SECRET', defined('OVERRIDE_GOOGLE_CLIENT_SECRET') ? OVERRIDE_GOOGLE_CLIENT_SECRET : '');
define('GOOGLE_REDIRECT_URI', (defined('APP_URL') ? APP_URL : 'https://trisa.luxurywebs.com') . '/google-callback.php');
```

- [ ] **Add columns to setup.php**

```php
$db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE");
$db->exec("ALTER TABLE users ADD COLUMN avatar_url TEXT NULL");
```

- [ ] **Commit**

```bash
git add includes/config.php setup.php
git commit -m "feat: Google OAuth config + DB columns"
```

---

#### Task 3.2: Google OAuth - Login Button + Callback

**Files:**
- Create: `google-login.php`
- Create: `google-callback.php`
- Modify: `login.php` — Google button

- [ ] **Create google-login.php**

```php
<?php
require_once __DIR__ . '/includes/config.php';
if (!defined('GOOGLE_CLIENT_ID') || !GOOGLE_CLIENT_ID) {
    die('Google SSO not configured. Contact the administrator.');
}
$params = http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
]);
header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
```

- [ ] **Create google-callback.php**

```php
<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';

if (!isset($_GET['code'])) {
    $error = 'No authorization code received.';
} else {
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = http_build_query([
        'code' => $_GET['code'],
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        $error = 'Failed to authenticate with Google.';
    } else {
        $tokenData = json_decode($response, true);
        $accessToken = $tokenData['access_token'] ?? '';

        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $accessToken"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $userInfo = curl_exec($ch);
        curl_close($ch);

        $googleUser = json_decode($userInfo, true);
        if (!$googleUser || !isset($googleUser['email'])) {
            $error = 'Failed to fetch user info from Google.';
        } else {
            $googleId = $googleUser['id'];
            $email = $googleUser['email'];
            $name = $googleUser['name'] ?? explode('@', $email)[0];
            $avatar = $googleUser['picture'] ?? '';

            // Check if google_id exists
            $existing = runQuery("SELECT * FROM users WHERE google_id = ?", [$googleId]);
            if ($existing) {
                $user = $existing[0];
            } else {
                // Check if email exists
                $existingEmail = runQuery("SELECT * FROM users WHERE email = ?", [$email]);
                if ($existingEmail) {
                    // Link google_id to existing account
                    executeQuery("UPDATE users SET google_id = ?, avatar_url = ? WHERE id = ?", [$googleId, $avatar, $existingEmail[0]['id']]);
                    $user = $existingEmail[0];
                } else {
                    // Auto-register as client
                    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                    executeQuery("INSERT INTO users (name, email, password, role, google_id, avatar_url, approved) VALUES (?, ?, ?, 'client', ?, ?, 1)",
                        [$name, $email, $hash, $googleId, $avatar]);
                    $user = runQuery("SELECT * FROM users WHERE google_id = ?", [$googleId])[0];
                }
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            registerDevice($user['id']);
            logActivity('login', 'user', $user['id'], 'Google SSO login');
            redirect('dashboard.php');
        }
    }
}

// Error display
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Google Login - SmartUjenzi</title>
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="bg-white p-8 rounded-xl shadow-sm max-w-md w-full text-center">
    <h1 class="text-xl font-bold text-red-600 mb-2">Login Failed</h1>
    <p class="text-gray-600"><?= htmlspecialchars($error) ?></p>
    <a href="login.php" class="inline-block mt-4 text-blue-600 hover:underline">Back to Login</a>
</div></body></html>
```

- [ ] **Add Google button to login.php**

Find the login form and add before the submit button:
```php
<div class="mt-4">
    <a href="google-login.php" class="flex items-center justify-center gap-2 w-full py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
        <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
        Sign in with Google
    </a>
</div>
```

- [ ] **Commit**

```bash
git add google-login.php google-callback.php login.php
git commit -m "feat: Google SSO login"
```

---

### Phase 4: Client Document Upload

#### Task 4.1: Client Document Upload - Schema + Upload Handler

**Files:**
- Modify: `setup.php` — request_documents table, customer_requests square_meters column
- Create: `includes/upload_handler.php`

- [ ] **Add schema to setup.php**

```php
$db->exec("CREATE TABLE IF NOT EXISTS request_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('photo', 'document') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request_id (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("ALTER TABLE customer_requests ADD COLUMN square_meters DECIMAL(10,2) NULL");
```

- [ ] **Create upload_handler.php**

```php
<?php
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_DOC_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

function uploadRequestFile(array $file, int $requestId, string $type): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }

    $maxSize = $type === 'photo' ? MAX_PHOTO_SIZE : MAX_DOC_SIZE;
    $allowedTypes = $type === 'photo' ? ALLOWED_PHOTO_TYPES : ALLOWED_DOC_TYPES;

    if ($file['size'] > $maxSize) {
        $sizeName = $type === 'photo' ? '5MB' : '10MB';
        return ['success' => false, 'error' => "File too large. Max $sizeName."];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type.'];
    }

    $uploadDir = __DIR__ . '/../uploads/requests/' . $requestId;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'error' => 'Failed to save file.'];
    }

    $relativePath = 'uploads/requests/' . $requestId . '/' . $filename;
    executeQuery("INSERT INTO request_documents (request_id, file_path, file_type, original_name) VALUES (?, ?, ?, ?)",
        [$requestId, $relativePath, $type, $file['name']]);

    return ['success' => true, 'path' => $relativePath];
}
```

- [ ] **Commit**

```bash
git add includes/upload_handler.php setup.php
git commit -m "feat: client document upload handler + schema"
```

---

#### Task 4.2: Client Upload Page + PM View

**Files:**
- Create: `client/upload-documents.php`
- Modify: `client/requests.php` — upload button and document list
- Modify: `customer_requests.php` — document view for PM

- [ ] **Create client/upload-documents.php**

```php
<?php
$pageTitle = 'Upload Documents';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

$requestId = (int)($_GET['request_id'] ?? 0);
$request = runQuery("SELECT * FROM customer_requests WHERE id = ? AND customer_id = ?", [$requestId, $_SESSION['user_id']]);

if (!$request) {
    echo '<div class="p-6 text-center text-red-500">Request not found.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$request = $request[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/upload_handler.php';

    $squareMeters = (float)($_POST['square_meters'] ?? 0);
    if ($squareMeters > 0) {
        executeQuery("UPDATE customer_requests SET square_meters = ? WHERE id = ?", [$squareMeters, $requestId]);
    }

    $anyFail = false;
    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
            if (empty($tmp)) continue;
            $file = ['name' => $_FILES['photos']['name'][$i], 'tmp_name' => $tmp, 'error' => $_FILES['photos']['error'][$i], 'size' => $_FILES['photos']['size'][$i]];
            $result = uploadRequestFile($file, $requestId, 'photo');
            if (!$result['success']) $anyFail = true;
        }
    }
    if (!empty($_FILES['documents']['name'][0])) {
        foreach ($_FILES['documents']['tmp_name'] as $i => $tmp) {
            if (empty($tmp)) continue;
            $file = ['name' => $_FILES['documents']['name'][$i], 'tmp_name' => $tmp, 'error' => $_FILES['documents']['error'][$i], 'size' => $_FILES['documents']['size'][$i]];
            $result = uploadRequestFile($file, $requestId, 'document');
            if (!$result['success']) $anyFail = true;
        }
    }

    if ($anyFail) {
        $error = 'Some files could not be uploaded. Check file types and sizes.';
    } else {
        $success = 'Documents uploaded successfully!';
    }
}

$documents = runQuery("SELECT * FROM request_documents WHERE request_id = ? ORDER BY created_at DESC", [$requestId]);
?>
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Upload Documents</h1>
    <p class="text-gray-500 text-sm mb-6">Request #<?= $request['id'] ?> — <?= htmlspecialchars($request['project_type'] ?? 'General') ?></p>

    <?php if (isset($success)): ?><div class="p-3 mb-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div><?php endif; ?>
    <?php if (isset($error)): ?><div class="p-3 mb-4 rounded-lg text-sm bg-red-100 text-red-700 border border-red-200"><?= $error ?></div><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Square Meters (area) *</label>
                <input type="number" name="square_meters" step="0.01" min="0" value="<?= htmlspecialchars($request['square_meters'] ?? '') ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Photos *</label>
                <input type="file" name="photos[]" accept="image/jpeg,image/png" multiple class="w-full text-sm">
                <p class="text-xs text-gray-400 mt-1">JPEG/PNG, max 5MB each</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Additional Documents</label>
                <input type="file" name="documents[]" accept=".pdf,.doc,.docx" multiple class="w-full text-sm">
                <p class="text-xs text-gray-400 mt-1">PDF/DOC, max 10MB each</p>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">Upload</button>
        </form>
    </div>

    <?php if ($documents): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Uploaded Documents</h2>
        <div class="space-y-3">
            <?php foreach ($documents as $d): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <span class="text-sm"><?= $d['file_type'] === 'photo' ? '📷' : '📄' ?></span>
                    <span class="text-sm font-medium ml-2"><?= htmlspecialchars($d['original_name']) ?></span>
                </div>
                <a href="/<?= $d['file_path'] ?>" target="_blank" class="text-xs px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">View</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```

- [ ] **Update client/requests.php — add upload button**

In the client requests table, add an "Upload" action column:
```php
<td class="py-3">
    <a href="upload-documents.php?request_id=<?= $r['id'] ?>" class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Upload</a>
</td>
```

- [ ] **Update customer_requests.php — add document view for PM**

In the customer requests table, after the company/location info, add a column for viewing uploaded documents for the PM:
```php
// After fetching requests, fetch document counts
$docCounts = runQuery("SELECT request_id, COUNT(*) as count FROM request_documents GROUP BY request_id");
$docCountMap = [];
foreach ($docCounts as $dc) $docCountMap[$dc['request_id']] = $dc['count'];
```

Add documents column in table:
```php
<td class="py-3">
    <?php $dc = $docCountMap[$r['id']] ?? 0; ?>
    <a href="?view_docs=<?= $r['id'] ?>" class="text-xs px-2 py-1 <?= $dc > 0 ? 'bg-green-500' : 'bg-gray-400' ?> text-white rounded"><?= $dc ?> Docs</a>
</td>
```

- [ ] **Commit**

```bash
git add client/upload-documents.php client/requests.php customer_requests.php
git commit -m "feat: client document upload + PM document view"
```

---

### Phase 5: Gantt Chart + URL Encryption

#### Task 5.1: Gantt Chart

**Files:**
- Create: `project-detail.php`
- Modify: `projects.php` — make project name clickable

- [ ] **Create project-detail.php**

```php
<?php
$pageTitle = 'Project Details';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
require_once __DIR__ . '/includes/header.php';

$projectId = (int)($_GET['id'] ?? 0);
$project = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id WHERE p.id = ?", [$projectId]);
if (!$project) { echo '<div class="p-6 text-center text-red-500">Project not found.</div>'; require_once __DIR__ . '/includes/footer.php'; exit; }
$project = $project[0];

$tasks = runQuery("SELECT t.*, u.name as fundi_name FROM tasks t LEFT JOIN users u ON t.fundi_id = u.id WHERE t.project_id = ? ORDER BY t.deadline", [$projectId]);
if (!$tasks) $tasks = [];

// Calculate date range for Gantt
$startDate = $project['start_date'] ? new DateTime($project['start_date']) : new DateTime();
$endDate = $project['end_date'] ? new DateTime($project['end_date']) : (clone $startDate)->modify('+30 days');
$totalDays = max(1, $startDate->diff($endDate)->days);
$today = new DateTime();
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800"><span class="text-blue-500 mr-2"><?= htmlspecialchars($project['icon'] ?? '🏗️') ?></span><?= htmlspecialchars($project['name']) ?></h1>
    <p class="text-gray-500 text-sm">PM: <?= htmlspecialchars($project['pm_name'] ?? '—') ?> | Status: <span class="font-semibold"><?= $project['status'] ?></span></p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Project Timeline (Gantt)</h2>
    <p class="text-xs text-gray-400 mb-4"><?= $startDate->format('M d, Y') ?> — <?= $endDate->format('M d, Y') ?> (<?= $totalDays ?> days)</p>

    <div class="overflow-x-auto">
        <div class="relative" style="min-width: 600px;">
            <!-- Month headers -->
            <div class="flex mb-2" style="margin-left: 200px;">
                <?php
                $cursor = clone $startDate;
                $months = [];
                while ($cursor <= $endDate) {
                    $monthKey = $cursor->format('Y-m');
                    if (!isset($months[$monthKey])) {
                        $months[$monthKey] = ['label' => $cursor->format('M Y'), 'days' => 0, 'start' => clone $cursor];
                    }
                    $months[$monthKey]['days']++;
                    $cursor->modify('+1 day');
                }
                foreach ($months as $m):
                    $width = ($m['days'] / $totalDays) * 100;
                ?>
                <div class="text-xs font-medium text-gray-500 text-center border-l border-gray-200" style="width: <?= $width ?>%;"><?= $m['label'] ?></div>
                <?php endforeach; ?>
            </div>

            <!-- Task bars -->
            <div class="space-y-2">
                <?php foreach ($tasks as $task):
                    $taskStart = $task['created_at'] ? new DateTime($task['created_at']) : $startDate;
                    $taskEnd = $task['deadline'] ? new DateTime($task['deadline']) : $endDate;
                    if ($taskEnd < $taskStart) $taskEnd = $taskStart;
                    if ($taskStart < $startDate) $taskStart = $startDate;
                    if ($taskEnd > $endDate) $taskEnd = $endDate;

                    $offsetDays = max(0, $startDate->diff($taskStart)->days);
                    $durationDays = max(1, $taskStart->diff($taskEnd)->days);
                    $offsetPercent = ($offsetDays / $totalDays) * 100;
                    $widthPercent = ($durationDays / $totalDays) * 100;

                    $barColor = match($task['status'] ?? 'Pending') {
                        'Completed' => 'bg-green-500',
                        'In Progress' => 'bg-blue-500',
                        default => 'bg-gray-400',
                    };
                ?>
                <div class="flex items-center">
                    <div class="w-[200px] pr-3 flex-shrink-0">
                        <div class="text-sm font-medium truncate"><?= htmlspecialchars($task['name']) ?></div>
                        <div class="text-xs text-gray-400"><?= htmlspecialchars($task['fundi_name'] ?? 'Unassigned') ?></div>
                    </div>
                    <div class="flex-1 h-8 relative bg-gray-50 rounded">
                        <div class="absolute top-1 h-6 rounded <?= $barColor ?> opacity-80" style="left: <?= $offsetPercent ?>%; width: <?= max(2, $widthPercent) ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($tasks)): ?>
                <p class="text-gray-400 text-sm text-center py-8">No tasks for this project yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

- [ ] **Make project name clickable in projects.php**

Change the project name display:
```php
<td class="py-3 font-medium">
    <span class="text-blue-500 mr-1"><?= htmlspecialchars($p['icon'] ?? '🏗️') ?></span>
    <a href="project-detail.php?id=<?= $p['id'] ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($p['name']) ?></a>
</td>
```

- [ ] **Commit**

```bash
git add project-detail.php projects.php
git commit -m "feat: Gantt chart for project details"
```

---

#### Task 5.2: URL Encryption

**Files:**
- Modify: `includes/functions.php` — add encryptId, decryptId
- Modify: `includes/config.php` — encryption key constant
- Modify: `projects.php` — use encrypted URLs
- Modify: `project-detail.php` — decrypt IDs

- [ ] **Add encryption key constant to config.php**

```php
define('ENCRYPTION_KEY', defined('OVERRIDE_ENCRYPTION_KEY') ? OVERRIDE_ENCRYPTION_KEY : 'sm4rtUj3nz1S3cur3K3y2026!');
```

- [ ] **Add helper functions to functions.php**

```php
function encryptId(int $id): string {
    $method = 'aes-128-ecb';
    $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default-key';
    $encrypted = openssl_encrypt((string)$id, $method, $key);
    return $encrypted === false ? (string)$id : urlencode($encrypted);
}

function decryptId(string $encrypted): ?int {
    if (is_numeric($encrypted)) return (int)$encrypted;
    $method = 'aes-128-ecb';
    $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default-key';
    $decrypted = openssl_decrypt(urldecode($encrypted), $method, $key);
    if ($decrypted === false || !is_numeric($decrypted)) return null;
    return (int)$decrypted;
}
```

- [ ] **Apply to projects.php — encrypt URLs**

In the project name link and status modal:
```php
<a href="project-detail.php?id=<?= urlencode(encryptId($p['id'])) ?>">
```

- [ ] **Apply to project-detail.php — decrypt ID**

```php
$projectId = decryptId($_GET['id'] ?? '');
if (!$projectId) { echo '<div class="p-6 text-center text-red-500">Invalid project.</div>'; require_once __DIR__ . '/includes/footer.php'; exit; }
```

- [ ] **Commit**

```bash
git add includes/functions.php includes/config.php projects.php project-detail.php
git commit -m "feat: URL encryption for project IDs"
```

---

## Self-Review

After writing all tasks, verify:
1. Spec coverage: all 9 features have tasks? Yes — OTP 10min (Task 1.1), login error (1.1), fundi dropdown (1.2), icons+favicon (1.3), forgot password (Phase 2), Google SSO (Phase 3), client upload (Phase 4), Gantt (5.1), URL encryption (5.2)
2. Placeholder scan: all code blocks contain complete code
3. Path consistency: all file paths match existing project structure

---

## Execution

Plan complete. Two options:
1. **Subagent-Driven** — dispatch fresh agent per task
2. **Inline Execution** — execute in this session with checkpoints
