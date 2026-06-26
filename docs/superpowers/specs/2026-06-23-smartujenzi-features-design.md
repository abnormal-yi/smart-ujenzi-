# SmartUjenzi Feature Design

## Overview
Nine features for the SmartUjenzi construction management system:
1. Google SSO
2. Client request document upload flow
3. OTP extended to 10 minutes
4. Invalid username/password error message
5. Forgot password
6. Gantt chart for project details
7. Project icons + favicon
8. URL encryption
9. Dynamic fundi assignment (remove assigned fundi from dropdown)

## 1. Google SSO

### Approach: Google OAuth 2.0
- **No library** — pure PHP using cURL for token exchange and userinfo fetch
- Google Cloud Project → OAuth 2.0 Client ID + Client Secret
- Redirect URI: `https://trisa.luxurywebs.com/google-callback.php`
- Config stored in `config.local.php` (OVERRIDE_GOOGLE_CLIENT_ID, OVERRIDE_GOOGLE_CLIENT_SECRET)

### Flow
1. User clicks "Login with Google" → redirects to Google consent screen
2. Google redirects to `google-callback.php?code=...`
3. Backend exchanges code for access token via cURL POST to `https://oauth2.googleapis.com/token`
4. Fetch user info: `https://www.googleapis.com/oauth2/v2/userinfo` (email, name, picture)
5. Check `google_id` in users table:
   - Match found → log in (update name/picture if changed)
   - No match but email exists → link google_id to existing account → log in
   - No match + no email → auto-register with role `client` (default)
6. If email domain matches known patterns, could auto-assign role — for now all Google users get `client` role
7. Skip OTP for Google SSO logins (trusted identity provider)

### Database
- Add `google_id VARCHAR(255) NULL UNIQUE` to users table
- Add `avatar_url TEXT NULL` to users table (Google profile picture)

### Files
- `google-login.php` — redirect to Google
- `google-callback.php` — handle callback
- Update `login.php` — add "Login with Google" button
- Update `includes/config.php` — Google OAuth constants with local override

### Auth Implications
- Google users bypass OTP (trusted provider)
- Google users cannot use "Forgot Password" (no password stored)

## 2. Client Request + Document Upload Flow

### Current State
- `customer_requests.php` — admin/PM views requests, can assign PM
- `client/requests.php` — client views their requests
- PM gets in-app notification when assigned

### New Flow
1. Admin approves request + assigns PM (existing)
2. **Email notification** sent to client via existing `sendEmail()`
3. Client logs in → sees request with "Upload Documents" button
4. Client uploads:
   - **Photos** (JPEG/PNG, max 5MB, multiple files) — required
   - **Square meters** (numeric) — required
   - **Additional documents** (PDF/DOC, max 10MB) — optional
5. Files stored in `uploads/requests/{request_id}/` folder
6. PM views documents on `customer_requests.php` detail view

### Database
- Add columns to `customer_requests`:
  - `square_meters DECIMAL(10,2) NULL`
  - `status` already exists (`Pending`, `Reviewd`, `Accepted`, `Rejected`)
- New table: `request_documents`
  - `id INT AUTO_INCREMENT PRIMARY KEY`
  - `request_id INT NOT NULL`
  - `file_path VARCHAR(500) NOT NULL`
  - `file_type ENUM('photo', 'document') NOT NULL`
  - `original_name VARCHAR(255) NOT NULL`
  - `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`

### Files
- Update `customer_requests.php` — add document view for PM
- Update `client/requests.php` — add upload form
- Create `includes/upload_handler.php` — file upload logic
- Update `includes/mailer.php` usage for client notification
- Update `includes/functions.php` — add helper functions

## 3. OTP Extended to 10 Minutes

Simple change in `login.php`:
- `DATE_ADD(NOW(), INTERVAL 5 MINUTE)` → `DATE_ADD(NOW(), INTERVAL 10 MINUTE)`

## 4. Invalid Username/Password

Change in `login.php`:
- Current: generic error
- New: "Invalid username or password" (single message for both wrong email and wrong password, to avoid leaking which field is wrong)

## 5. Forgot Password

### Flow
1. "Forgot Password?" link on `login.php`
2. `forgot-password.php` — enter email address
3. Check if email exists → send reset link (always show "If email exists, reset link sent" to avoid email enumeration)
4. `reset-password.php?token=...` — show new password form
5. Validate token + expiry → update password → redirect to login

### Database
- New table: `password_resets`
  - `id INT AUTO_INCREMENT PRIMARY KEY`
  - `email VARCHAR(255) NOT NULL`
  - `token VARCHAR(64) NOT NULL UNIQUE`
  - `expires_at DATETIME NOT NULL`
  - `used TINYINT(1) DEFAULT 0`
  - `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`

### Files
- `forgot-password.php` — email form
- `reset-password.php` — token validation + new password form

## 6. Gantt Chart

### Approach
- Display on project detail page (click project name from projects list)
- Use **pure HTML/CSS** bars for timeline visualization (no JS library dependency)
- Tasks grouped by status, displayed as horizontal colored bars
- Timeline calculated from project start_date to end_date

### Implementation
- Create `project-detail.php?id=X` — project tasks as Gantt bars
- Each task: bar spans from task start to end (or project dates if task has no dates)
- Color by status: Pending → gray, In Progress → blue, Completed → green
- Alternatively use CDN-loaded Frappe Gantt (`<script src="...">`) for interactive features

### Files
- `project-detail.php` — new page
- Update `projects.php` — make project name clickable linking to detail page

## 7. Project Icons + Favicon

### Icons
- Add `icon VARCHAR(10) DEFAULT '🏗️'` column to projects table
- Default blue emoji shown before project name
- Admin/PM can select icon when creating project (dropdown of emojis: 🏗️🏠🔧📋🎯🧱)
- CSS: color the icon blue (`color: #2563eb`)

### Favicon
- Simple blue SVG favicon: "SU" letters or construction helmet emoji
- Added in `includes/header.php` → `<link rel="icon" type="image/svg+xml" href="favicon.svg">`

### Files
- `favicon.svg` — new file
- Update `includes/header.php` — add favicon link
- Update `projects.php` — icon selector in create modal + display icon
- Update database schema for projects table

## 8. URL Encryption

### Approach
- Use `base64_encode(openssl_encrypt())` with a secret key
- Only apply to resource IDs in URLs (e.g., `?id=5` → `?e=encrypted_string`)
- Decrypt middleware/helper function

### Implementation
- Helper functions in `includes/functions.php`:
  - `encryptId($id)` — encrypt integer ID
  - `decryptId($encrypted)` — decrypt back to integer, return null on failure
- Apply to: projects, tasks, materials, workers detail/action URLs
- Secret key stored in `config.local.php` (OVERRIDE_ENCRYPTION_KEY)

### Security Notes
- This is **obfuscation**, not true security (no auth bypass prevention)
- Authorization checks remain in place (requireLogin, requireRole)

## 9. Dynamic Fundi Dropdown

### Current
- `pm/tasks.php` line 24: `SELECT id, name FROM users WHERE role = 'fundi' AND approved = 1`
- Shows all approved fundis regardless of existing assignments

### New
- When assigning fundi to a task within a project, exclude fundis already assigned to other tasks in the same project
- Query: `SELECT id, name, skills FROM users WHERE role = 'fundi' AND approved = 1 AND id NOT IN (SELECT fundi_id FROM tasks WHERE project_id = ? AND fundi_id IS NOT NULL)`
- This prevents assigning the same fundi to multiple tasks in one project
- Fundi can still be assigned to tasks in different projects

### Files
- Update `pm/tasks.php` — modify fundi query to exclude already-assigned

## Database Changes Summary

### New Tables
```sql
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS request_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('photo', 'document') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Column Changes
```sql
ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE;
ALTER TABLE users ADD COLUMN avatar_url TEXT NULL;
ALTER TABLE projects ADD COLUMN icon VARCHAR(10) DEFAULT '🏗️';
ALTER TABLE customer_requests ADD COLUMN square_meters DECIMAL(10,2) NULL;
```

### indexes
- `INDEX idx_request_documents_request (request_id)` on request_documents
- `INDEX idx_password_resets_token (token)` on password_resets
- `INDEX idx_password_resets_email (email)` on password_resets
