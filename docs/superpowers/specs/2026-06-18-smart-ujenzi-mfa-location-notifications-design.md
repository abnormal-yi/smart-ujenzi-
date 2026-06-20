# SmartUjenzi — MFA, Location Hierarchy, Budget Removal, User Mgmt & Notifications

## 1. Remove Demo Accounts
- Delete the demo accounts table from `login.php`
- Login page shows only the form (email + password), no hardcoded credentials

## 2. MFA via OTP (PHP Mailer)
- After password verification succeeds, check if device is known
- Device tracking via cookie (`device_token`) — random UUID stored in browser + database table `user_devices`
- If device is new → generate 6-digit OTP, store in `otp_codes` table (user_id, code, expires_at), send via PHP Mailer
- Redirect to `/otp-verify.php` — form for OTP input
- If OTP correct → mark device as known, create session, redirect to dashboard
- OTP expires after 5 minutes
- If device is known → skip OTP, create session directly

### Tables
```sql
CREATE TABLE user_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Login Flow
1. User submits email + password
2. Verify credentials → if invalid, show error
3. Check for `device_token` cookie:
   - Found + matches `user_devices` → create session, redirect dashboard
   - Not found → generate OTP, send email, redirect `/otp-verify.php`
4. `/otp-verify.php`: user enters 6-digit code → verify against `otp_codes` → if valid, create `user_devices` record, set cookie, create session, redirect dashboard

## 3. PHP Mailer Integration
- Download PHPMailer (no Composer — manual `require` from `vendor/` directory)
- Config in `config.local.php`:
  ```php
  define('SMTP_HOST', '');
  define('SMTP_PORT', 587);
  define('SMTP_USER', '');
  define('SMTP_PASS', '');
  define('SMTP_FROM', 'noreply@smartujenzi.com');
  define('SMTP_FROM_NAME', 'SmartUjenzi');
  ```
- Helper function `sendEmail($to, $subject, $body)` in `includes/mailer.php`
- Used for:
  - OTP code delivery
  - New user welcome email with random password

## 4. Location Hierarchy (Tanzania)
- Database tables: `regions`, `districts`, `wards`, `streets`
- Seed data: 31 regions + all districts (~184)
- (Wards and streets added later via CSV/API — for now we use SELECT-driven dropdowns)
- Registration forms: cascading dropdowns (select region → load districts)
- All forms that collect location (customer_requests, registration) use dropdown selects

### Tables
```sql
CREATE TABLE regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id)
);
```

### Forms Updated
- `register.php` — region/district dropdowns
- `customer_requests.php` — region/district dropdowns for location field
- Client request form — uses dropdowns instead of free-text location

## 5. Remove Budget Features
- Delete file: `/pm/budget.php`
- Remove from sidebar nav: the Budget nav item in header.php
- Remove budget columns from `customer_requests`:
  - `budget_amount DECIMAL(12,2)`
  - `budget_status VARCHAR(50)`
  - `proposed_timeline VARCHAR(255)`
- Remove budget fields from `customer_requests.php` (form + display)
- Remove budget accept/reject from `client/requests.php`
- Remove budget submission from `pm/budget.php` (deleted)

## 6. Super Admin User Creation
- `super_admin/users.php`:
  - Role dropdown limited to: `admin`, `project_manager`, `fundi` (no `client`)
- On user creation:
  - Generate random 10-char password
  - Hash with `password_hash()`
  - Send email via PHP Mailer with credentials
  - Show success message (no password on screen)
- `client` role registration remains via public `register.php` only

## 7. Fix Page Errors
- `client/payments.php` — already filters by `customer_id` correctly
- `client/progress.php` — verify query is correct (check for old column names)
- General: audit all pages for remaining old schema references

## Files Changed
| File | Change |
|------|--------|
| `login.php` | Remove demo accounts, add device + OTP redirect |
| `otp-verify.php` | New — OTP verification form |
| `includes/mailer.php` | New — PHPMailer helper |
| `includes/functions.php` | Add `sendEmail()`, device cookie helpers |
| `includes/header.php` | Remove Budget nav item |
| `includes/config.php` | Add SMTP constants fallback |
| `config.local.example.php` | Add SMTP config |
| `database/schema.sql` | Add `user_devices`, `otp_codes`, `regions`, `districts`; remove budget columns |
| `super_admin/users.php` | Filter role dropdown, send email, no screen password |
| `register.php` | Add location dropdowns |
| `customer_requests.php` | Remove budget fields, add location dropdowns |
| `client/requests.php` | Remove budget accept/reject |
| `pm/budget.php` | Delete |
| `dashboard.php` | Remove budget-related queries |
| `login.php` | OTP flow |

## Order of Implementation
1. PHP Mailer (core dependency)
2. Remove demo accounts
3. Remove budget features
4. Super admin user creation + email notification
5. Location hierarchy (DB + seed data + dropdowns)
6. MFA/OTP system
7. Fix remaining page errors
8. Update schema.sql + setup.php
