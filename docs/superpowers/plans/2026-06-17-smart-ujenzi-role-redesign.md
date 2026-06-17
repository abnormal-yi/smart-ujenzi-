# SmartUjenzi Role Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate SmartUjenzi from current 5 roles (admin/manager/supervisor/constructor/customer) to 5 new roles (super_admin/admin/project_manager/fundi/client) with company selection flow and proper role-based pages.

**Architecture:** Modify existing schema, core includes, and seed data first; then build role-specific page directories with consistent patterns. All pages share the same Tailwind + Heroicons UI via existing header/footer.

**Tech Stack:** PHP 8, MySQL, Tailwind CSS CDN, Heroicons inline SVGs

---

### Task 1: Update DB Schema

**Files:**
- Modify: `database/schema.sql`

- [ ] **Step 1: Update users table role comment and reorder seed data**

In `database/schema.sql`, update the users table comment:

```
Stores system users with role-based access (super_admin, admin, project_manager, fundi, client)
```

Replace entire seed INSERT for users:

```sql
INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Super Admin', 'super@example.com', '$2y$12$h5KvvZh1CvZdEWV5nfKBv.dsndWykdBbc8xyWkvL1JpfsmgzN8is6', 'super_admin'),
(2, 'Stephen Massawe', 'steve@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'project_manager'),
(3, 'Teleza Mkomwa', 'teleza@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'project_manager'),
(4, 'Ali Fundi', 'ali@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'fundi'),
(5, 'Zainab Admin', 'zainab@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'admin'),
(6, 'John Mteja', 'mteja@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'client'),
(7, 'Daud Fundi', 'david@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'fundi');
```

- [ ] **Step 2: Rename supervisor_id to fundi_id in tasks table**

```sql
DROP TABLE IF EXISTS project_media;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS allocations;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS customer_requests;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS companies;
```

Replace tasks table:

```sql
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Not Started',
    fundi_id INT,
    deadline DATE,
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(fundi_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 3: Rename manager_id to project_manager_id in projects table**

```sql
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    project_manager_id INT,
    customer_id INT,
    start_date DATE,
    end_date DATE,
    FOREIGN KEY(project_manager_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 4: Add company_id, assigned_pm_id, budget fields to customer_requests**

```sql
CREATE TABLE customer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    company_id INT,
    project_type VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    budget_range VARCHAR(255),
    description TEXT,
    company_proposal TEXT,
    proposed_budget VARCHAR(255),
    proposed_deadline VARCHAR(255),
    budget_amount DECIMAL(12,2),
    budget_status VARCHAR(50) DEFAULT 'pending',
    proposed_timeline VARCHAR(255),
    assigned_pm_id INT,
    status VARCHAR(50) DEFAULT 'Pending',
    FOREIGN KEY(customer_id) REFERENCES users(id),
    FOREIGN KEY(company_id) REFERENCES companies(id),
    FOREIGN KEY(assigned_pm_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 5: Update seed data for tasks, projects, customer_requests**

Replace projects seed:

```sql
INSERT INTO projects (id, name, description, status, project_manager_id, customer_id, start_date, end_date) VALUES
(1, 'IAA New Library', 'Construction of a modern library', 'Ongoing', 2, 6, '2025-01-01', '2026-05-01'),
(2, 'Hostel Block B', 'Expansion of students hostel', 'Pending', 2, NULL, '2025-08-01', '2026-12-01'),
(3, 'City Mall Extension', 'Adding a new wing to City Mall', 'In Progress', 3, NULL, '2025-10-10', '2027-02-15'),
(4, 'Mwanza Hospital Block', 'New maternity ward', 'Completed', 2, 6, '2023-01-05', '2024-12-20');
```

Replace tasks seed (fundi_id instead of supervisor_id):

```sql
INSERT INTO tasks (project_id, name, description, status, fundi_id, deadline) VALUES
(1, 'Foundation Laying', 'Excavation and foundation laying', 'Completed', 4, '2025-02-15'),
(1, 'Brickwork Phase 1', 'Ground floor brickwork', 'In Progress', 7, '2025-06-30'),
(2, 'Site Clearance', 'Clearing the bush and trees', 'Completed', 4, '2025-08-15'),
(2, 'Foundation Excavation', 'Digging trenches', 'Not Started', 7, '2025-09-01'),
(3, 'Structural Framing', 'Putting up steel columns', 'In Progress', 7, '2026-01-20');
```

Replace customer_requests seed (add company_id):

```sql
INSERT INTO customer_requests (customer_id, company_id, project_type, location, budget_range, description, status) VALUES
(6, 1, 'Residential House', 'Dar es Salaam, Masaki', '50M - 100M TZS', 'Looking for a reliable company to build a 4-bedroom house. Have plot already.', 'Pending');
```

Update project_media seed (uploaded_by 3 → Teleza stays as project_manager, that's fine since PMs can also upload):

Replace seed for project_media `uploaded_by` from 3 → 3 (keep, Teleza is now project_manager but can still upload):

- [ ] **Step 6: Commit**

```bash
git add database/schema.sql
git commit -m "feat: update schema for new role system"
```

---

### Task 2: Update Core Includes (config, functions, header)

**Files:**
- Modify: `includes/config.php`
- Modify: `includes/functions.php`
- Modify: `includes/header.php`

- [ ] **Step 1: Update role constants/comments in config.php**

In `includes/config.php`, find the session setup. No role constants needed — roles are stored in DB. Update the APP_ENV to be local for development.

- [ ] **Step 2: Update requireRole in functions.php**

In `includes/functions.php`, `requireRole()` currently uses role strings directly. Update the role mapping aliases:

```php
function requireRole($roles) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $userRole = $_SESSION['user_role'] ?? '';
    if (!in_array($userRole, (array)$roles)) {
        header('Location: dashboard.php');
        exit;
    }
}
```

No changes needed — this already works as-is since we just change the role strings.

- [ ] **Step 3: Rewrite header.php sidebar for new roles**

Replace the entire sidebar navigation in `includes/header.php`. The sidebar shows different nav items based on `$_SESSION['user_role']`:

```php
<?php
$role = $_SESSION['user_role'] ?? '';
$pageFile = basename($_SERVER['PHP_SELF']);
?>
```

Then for each role group, show appropriate nav items:

**All roles see:** Dashboard, Profile (logout)

**super_admin additionally sees:**
- Users (super_admin/users.php)
- System Settings (super_admin/settings.php)

**admin additionally sees:**
- Requests (customer_requests.php)
- Projects (projects.php)
- Messages (messages.php)
- Reports (reports.php)

**project_manager additionally sees:**
- My Projects (pm/dashboard.php)
- Requirements (pm/requirements.php)
- Tasks (pm/tasks.php)
- Messages (messages.php)

**fundi additionally sees:**
- My Tasks (fundi/tasks.php)

**client additionally sees:**
- Companies (client/dashboard.php)
- My Requests (client/requests.php)
- My Projects (client/progress.php)

Key: Use `if ($role === 'super_admin' || $role === 'admin'):` type checks for shared nav items.

- [ ] **Step 4: Commit**

```bash
git add includes/header.php includes/functions.php
git commit -m "feat: update header nav for new roles"
```

---

### Task 3: Update Login & Dashboard

**Files:**
- Modify: `login.php`
- Modify: `dashboard.php`

- [ ] **Step 1: Update login.php demo accounts**

Update demo accounts listed on login page to match new roles:

```php
$demoAccounts = [
    ['super@example.com', 'admin123', 'Super Admin'],
    ['zainab@example.com', 'manager123', 'Admin'],
    ['steve@example.com', 'manager123', 'Project Manager'],
    ['teleza@example.com', 'manager123', 'Project Manager'],
    ['mteja@example.com', 'manager123', 'Client'],
    ['ali@example.com', 'manager123', 'Fundi'],
    ['david@example.com', 'manager123', 'Fundi'],
];
```

- [ ] **Step 2: Rewrite dashboard.php as role-based router**

Current dashboard.php has admin stats. Rewrite to show role-specific views:

```php
<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

if ($role === 'super_admin' || $role === 'admin') {
    // System stats: total users, projects, requests, companies
    $stats = runQuery("SELECT
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM projects) as total_projects,
        (SELECT COUNT(*) FROM customer_requests) as total_requests,
        (SELECT COUNT(*) FROM companies) as total_companies,
        (SELECT COUNT(*) FROM users WHERE role='client') as total_clients,
        (SELECT COUNT(*) FROM users WHERE role='fundi') as total_fundi
    ")[0];
    // Show admin dashboard with stats cards, recent requests, low stock
} elseif ($role === 'project_manager') {
    // PM's assigned projects
    $myProjects = runQuery("SELECT * FROM projects WHERE project_manager_id = ?", [$userId]);
    // Show my projects, tasks due, etc.
} elseif ($role === 'fundi') {
    // Fundi's assigned tasks
    $myTasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ?", [$userId]);
    // Show my tasks
} elseif ($role === 'client') {
    // Client's projects
    $myProjects = runQuery("SELECT * FROM projects WHERE customer_id = ?", [$userId]);
    $myRequests = runQuery("SELECT * FROM customer_requests WHERE customer_id = ? ORDER BY id DESC", [$userId]);
    // Show my projects and request status
}
```

Keep the same UI pattern (stats cards, progress bars, Heroicons SVGs).

- [ ] **Step 3: Commit**

```bash
git add login.php dashboard.php
git commit -m "feat: role-based dashboard and updated login"
```

---

### Task 4: SUPER ADMIN Pages

**Files:**
- Create: `super_admin/dashboard.php`
- Create: `super_admin/users.php`
- Create: `super_admin/settings.php`

- [ ] **Step 1: Create super_admin/ directory**

```bash
mkdir -p /home/yichang/smart-ujenzi-php/super_admin
```

- [ ] **Step 2: Create super_admin/dashboard.php**

Simple redirect to main dashboard or full stats:

```php
<?php
$pageTitle = 'Super Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

$stats = runQuery("SELECT
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role='admin') as total_admins,
    (SELECT COUNT(*) FROM users WHERE role='project_manager') as total_pms,
    (SELECT COUNT(*) FROM users WHERE role='fundi') as total_fundi,
    (SELECT COUNT(*) FROM users WHERE role='client') as total_clients,
    (SELECT COUNT(*) FROM projects) as total_projects,
    (SELECT COUNT(*) FROM customer_requests) as total_requests,
    (SELECT COUNT(*) FROM companies) as total_companies
")[0];
// Stats cards + recent activity table
?>
```

- [ ] **Step 3: Create super_admin/users.php**

Table of all users with role badges. Form to edit role inline. Delete user button.

```php
<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['role'];
    runQuery("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
    $success = "User role updated!";
}

// Handle delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    runQuery("DELETE FROM users WHERE id = ?", [$delId]);
    $success = "User deleted!";
}

$users = runQuery("SELECT * FROM users ORDER BY role, name");
$roles = ['super_admin', 'admin', 'project_manager', 'fundi', 'client'];
?>
```

- [ ] **Step 4: Create super_admin/settings.php**

Placeholder for system settings (future use):

```php
<?php
$pageTitle = 'System Settings';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/header.php';
// Show basic system info, DB status, etc.
?>
```

- [ ] **Step 5: Commit**

```bash
git add super_admin/
git commit -m "feat: super admin pages"
```

---

### Task 5: Update ADMIN Pages (customer_requests + projects)

**Files:**
- Modify: `customer_requests.php` — add PM assignment, company filter
- Modify: `projects.php` — filter by role, use project_manager_id

- [ ] **Step 1: Rewrite customer_requests.php for ADMIN**

Add PM assignment dropdown and company column:

```php
<?php
$pageTitle = 'Customer Requests';
require_once __DIR__ . '/includes/functions.php';
requireRole(['super_admin', 'admin', 'project_manager']);
require_once __DIR__ . '/includes/header.php';

$role = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

// Assign PM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_pm'])) {
    $reqId = (int)$_POST['request_id'];
    $pmId = (int)$_POST['pm_id'];
    runQuery("UPDATE customer_requests SET assigned_pm_id = ?, status = 'Reviewed' WHERE id = ?", [$pmId, $reqId]);
    // Also notify the PM
    runQuery("INSERT INTO notifications (user_id, message) VALUES (?, 'New request assigned to you')", [$pmId]);
    $success = "Project Manager assigned!";
}

if ($role === 'project_manager') {
    $requests = runQuery("SELECT cr.*, u.name as customer_name, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.assigned_pm_id = ? ORDER BY cr.id DESC", [$userId]);
} else {
    $requests = runQuery("SELECT cr.*, u.name as customer_name, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id ORDER BY cr.id DESC");
}

$projectManagers = runQuery("SELECT id, name FROM users WHERE role = 'project_manager'");
?>
```

Request table shows: company, customer, type, status, assigned PM, actions.
ADMIN sees "Assign PM" dropdown. PM sees "Prepare Budget" button.

- [ ] **Step 2: Update projects.php**

Replace `manager_id` references with `project_manager_id`:

```php
// In queries
$projects = runQuery("SELECT p.*, u.name as pm_name FROM projects p LEFT JOIN users u ON p.project_manager_id = u.id ORDER BY p.id DESC");
```

Also filter by role:
- super_admin/admin sees all
- project_manager sees their projects
- fundi sees projects they have tasks in
- client sees their own projects

- [ ] **Step 3: Update tasks.php**

Replace `supervisor_id` references with `fundi_id`:

```php
$tasks = runQuery("SELECT t.*, u.name as fundi_name, p.name as project_name FROM tasks t LEFT JOIN users u ON t.fundi_id = u.id JOIN projects p ON t.project_id = p.id ORDER BY t.deadline ASC");
```

Also add assign_fundi dropdown (PM can assign fundi to tasks):

```php
// Assign fundi to task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_fundi'])) {
    $taskId = (int)$_POST['task_id'];
    $fundiId = (int)$_POST['fundi_id'];
    runQuery("UPDATE tasks SET fundi_id = ? WHERE id = ?", [$fundiId, $taskId]);
}
```

- [ ] **Step 4: Commit**

```bash
git add customer_requests.php projects.php tasks.php
git commit -m "feat: update admin/pm pages for new roles"
```

---

### Task 6: PROJECT MANAGER Pages

**Files:**
- Create: `pm/dashboard.php`
- Create: `pm/requirements.php`
- Create: `pm/budget.php`
- Create: `pm/tasks.php`
- Create: `pm/progress.php`

- [ ] **Step 1: Create pm/ directory**

```bash
mkdir -p /home/yichang/smart-ujenzi-php/pm
```

- [ ] **Step 2: Create pm/dashboard.php**

```php
<?php
$pageTitle = 'PM Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';
$userId = $_SESSION['user_id'];

$myProjects = runQuery("SELECT * FROM projects WHERE project_manager_id = ?", [$userId]);
$pendingRequests = runQuery("SELECT cr.*, u.name as client_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id WHERE cr.assigned_pm_id = ? AND cr.status != 'Accepted'", [$userId]);
// Stats cards, pending requests, my projects list
?>
```

- [ ] **Step 3: Create pm/requirements.php**

PM views assigned requests and fills requirements form:

```php
<?php
$pageTitle = 'Project Requirements';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';
$userId = $_SESSION['user_id'];

$requests = runQuery("SELECT cr.*, u.name as client_name, u.email, c.name as company_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.assigned_pm_id = ? AND cr.status != 'Accepted'", [$userId]);
// Contact info display, requirement notes form
?>
```

- [ ] **Step 4: Create pm/budget.php**

PM submits budget proposal:

```php
<?php
$pageTitle = 'Prepare Budget';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_budget'])) {
    $reqId = (int)$_POST['request_id'];
    $amount = $_POST['budget_amount'];
    $timeline = $_POST['proposed_timeline'];
    runQuery("UPDATE customer_requests SET budget_amount = ?, proposed_timeline = ?, budget_status = 'pending', status = 'Reviewed' WHERE id = ?", [$amount, $timeline, $reqId]);
    $success = 'Budget sent to client for review';
}

$userId = $_SESSION['user_id'];
$requests = runQuery("SELECT cr.*, u.name as client_name FROM customer_requests cr JOIN users u ON cr.customer_id = u.id WHERE cr.assigned_pm_id = ? AND cr.status = 'Reviewed'", [$userId]);
?>
```

- [ ] **Step 5: Create pm/tasks.php**

PM creates tasks and assigns fundi:

```php
<?php
$pageTitle = 'Manage Tasks';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

// Create task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    runQuery("INSERT INTO tasks (project_id, name, description, fundi_id, deadline) VALUES (?,?,?,?,?)",
        [$_POST['project_id'], $_POST['name'], $_POST['description'], $_POST['fundi_id'], $_POST['deadline']]);
}

$userId = $_SESSION['user_id'];
$myProjects = runQuery("SELECT id, name FROM projects WHERE project_manager_id = ?", [$userId]);
$fundis = runQuery("SELECT id, name FROM users WHERE role = 'fundi'");
$tasks = runQuery("SELECT t.*, p.name as project_name, u.name as fundi_name FROM tasks t JOIN projects p ON t.project_id = p.id LEFT JOIN users u ON t.fundi_id = u.id WHERE p.project_manager_id = ? ORDER BY t.deadline", [$userId]);
?>
```

- [ ] **Step 6: Create pm/progress.php**

PM updates project status, adds notes:

```php
<?php
$pageTitle = 'Project Progress';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['project_manager']);
require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    runQuery("UPDATE projects SET status = ? WHERE id = ? AND project_manager_id = ?",
        [$_POST['status'], $_POST['project_id'], $_SESSION['user_id']]);
}

$userId = $_SESSION['user_id'];
$projects = runQuery("SELECT * FROM projects WHERE project_manager_id = ?", [$userId]);
?>
```

- [ ] **Step 7: Commit**

```bash
git add pm/
git commit -m "feat: project manager pages"
```

---

### Task 7: CLIENT Pages

**Files:**
- Create: `client/dashboard.php` — browse companies + submit request
- Create: `client/requests.php` — view request status + accept/reject budget
- Create: `client/progress.php` — view project progress (migrate customer_progress.php)
- Create: `client/payments.php` — payment history
- Delete: `customer_progress.php` (migrated to client/progress.php)

- [ ] **Step 1: Create client/ directory**

```bash
mkdir -p /home/yichang/smart-ujenzi-php/client
```

- [ ] **Step 2: Create client/dashboard.php — Company browser + request form**

This is the main client landing page. Shows companies with search/filter and a "Submit Request" form:

```php
<?php
$pageTitle = 'Find a Contractor';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

// Handle request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    runQuery("INSERT INTO customer_requests (customer_id, company_id, project_type, location, budget_range, description) VALUES (?,?,?,?,?,?)",
        [$_SESSION['user_id'], $_POST['company_id'], $_POST['project_type'], $_POST['location'], $_POST['budget_range'], $_POST['description']]);
    $success = 'Request submitted! Admin will review shortly.';
}

$companies = runQuery("SELECT * FROM companies ORDER BY verified DESC, rating DESC");
// Show company cards (same style as index.php hero section cards) with "Select Company" button
// Modal or inline form for submitting request to selected company
?>
```

Show companies as cards (logo initials, name, rating, location, verified badge). "Select" button opens a modal with request form. Same card style as index.php.

- [ ] **Step 3: Create client/requests.php**

Shows submitted requests, their status, and budget accept/reject:

```php
<?php
$pageTitle = 'My Requests';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

// Handle budget accept/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reqId = (int)$_POST['request_id'];
    $action = $_POST['action']; // 'accept' or 'reject'
    runQuery("UPDATE customer_requests SET budget_status = ? WHERE id = ? AND customer_id = ?", [$action === 'accept' ? 'accepted' : 'rejected', $reqId, $_SESSION['user_id']]);

    if ($action === 'accept') {
        // Auto-create project from request
        $req = runQuery("SELECT * FROM customer_requests WHERE id = ?", [$reqId])[0];
        runQuery("INSERT INTO projects (name, description, status, project_manager_id, customer_id, start_date) VALUES (?, ?, 'Pending', ?, ?, CURDATE())",
            [$req['project_type'] . ' - ' . $req['location'], $req['description'], $req['assigned_pm_id'], $req['customer_id']]);
    }
}

$userId = $_SESSION['user_id'];
$requests = runQuery("SELECT cr.*, c.name as company_name FROM customer_requests cr LEFT JOIN companies c ON cr.company_id = c.id WHERE cr.customer_id = ? ORDER BY cr.id DESC", [$userId]);
?>
```

Request cards show: company, type, location, budget range, status, budget amount (if proposed), accept/reject buttons.

- [ ] **Step 4: Create client/progress.php (migrate from customer_progress.php)**

Copy the logic from `customer_progress.php` and update queries. Replace the file path — create new file, keep old for redirect:

```php
<?php
$pageTitle = 'My Projects';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';
$userId = $_SESSION['user_id'];

// Same queries as customer_progress.php but updated for new schema
$myProjects = runQuery('SELECT * FROM projects WHERE customer_id = ? ORDER BY created_at DESC', [$userId]);
$payments = runQuery('SELECT p.*, pr.name as project_name FROM payments p JOIN projects pr ON p.project_id = pr.id WHERE pr.customer_id = ? ORDER BY p.payment_date DESC', [$userId]);
$media = runQuery('SELECT pm.*, u.name as uploaded_by_name, pr.name as project_name, t.name as task_name FROM project_media pm JOIN users u ON pm.uploaded_by = u.id JOIN projects pr ON pm.project_id = pr.id LEFT JOIN tasks t ON pm.task_id = t.id WHERE pr.customer_id = ? ORDER BY pm.created_at DESC', [$userId]);
$allTasks = runQuery('SELECT t.*, pr.name as project_name FROM tasks t JOIN projects pr ON t.project_id = pr.id WHERE pr.customer_id = ? ORDER BY t.deadline ASC', [$userId]);
// Same rendering as customer_progress.php
?>
```

- [ ] **Step 5: Create client/payments.php**

```php
<?php
$pageTitle = 'Payment History';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$payments = runQuery("SELECT p.*, pr.name as project_name FROM payments p JOIN projects pr ON p.project_id = pr.id WHERE pr.customer_id = ? ORDER BY p.payment_date DESC", [$userId]);
// Payment table with project, amount, date, status
?>
```

- [ ] **Step 6: Keep customer_progress.php as redirect**

```php
<?php
header('Location: client/progress.php');
exit;
```

- [ ] **Step 7: Commit**

```bash
git add client/ customer_progress.php
git commit -m "feat: client pages - companies, requests, progress, payments"
```

---

### Task 8: FUNDI Pages

**Files:**
- Create: `fundi/dashboard.php`
- Create: `fundi/tasks.php`
- Create: `public/uploads/` (ensure directory exists)

- [ ] **Step 1: Create fundi/ directory**

```bash
mkdir -p /home/yichang/smart-ujenzi-php/fundi
```

- [ ] **Step 2: Create fundi/dashboard.php**

```php
<?php
$pageTitle = 'Fundi Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['fundi']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$pendingTasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ? AND t.status != 'Completed' ORDER BY t.deadline", [$userId]);
$completedTasks = runQuery("SELECT COUNT(*) as total FROM tasks WHERE fundi_id = ? AND status = 'Completed'", [$userId])[0]['total'];
// Stats: pending tasks count, completed count, upcoming deadlines
?>
```

- [ ] **Step 3: Create fundi/tasks.php**

Fundi sees their tasks, can update status, upload photos:

```php
<?php
$pageTitle = 'My Tasks';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['fundi']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

// Update task status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    runQuery("UPDATE tasks SET status = ? WHERE id = ? AND fundi_id = ?", [$_POST['status'], $_POST['task_id'], $userId]);
}

// Upload photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $taskId = (int)$_POST['task_id'];
    $task = runQuery("SELECT project_id FROM tasks WHERE id = ? AND fundi_id = ?", [$taskId, $userId])[0] ?? null;
    if ($task) {
        $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . $ext;
        move_uploaded_file($_FILES['media']['tmp_name'], 'public/uploads/' . $filename);
        runQuery("INSERT INTO project_media (project_id, task_id, uploaded_by, file_path, type, caption) VALUES (?,?,?,?,?,?)",
            [$task['project_id'], $taskId, $userId, 'public/uploads/' . $filename, in_array($ext, ['mp4','webm']) ? 'video' : 'image', $_POST['caption'] ?? '']);
        $success = 'Photo uploaded!';
    }
}

$tasks = runQuery("SELECT t.*, p.name as project_name FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.fundi_id = ? ORDER BY t.deadline", [$userId]);
?>
```

Task list with status update dropdown + upload photo button/modal.

- [ ] **Step 4: Commit**

```bash
git add fundi/
git commit -m "feat: fundi pages"
```

---

### Task 9: Update Setup & Seed Script

**Files:**
- Modify: `setup.php` — update for new schema
- Modify: `start.sh` — no changes needed

- [ ] **Step 1: Update setup.php**

Ensure `setup.php` uses the updated schema.sql correctly.

- [ ] **Step 2: Verify DB reset works**

```bash
php -d extension=pdo_mysql setup.php force=1
```

- [ ] **Step 3: Commit**

```bash
git add setup.php
git commit -m "chore: update setup script for new schema"
```

---

### Task 10: Verify Everything Works

- [ ] **Step 1: Start the server and test login**

```bash
php -d extension=pdo_mysql -S localhost:8080 -t /home/yichang/smart-ujenzi-php
```

- [ ] **Step 2: Test each role login**
  - super@example.com → see super admin links
  - zainab@example.com → see admin links (requests, projects)
  - steve@example.com → see PM links (requirements, budget)
  - mteja@example.com → see client links (companies, my requests)
  - ali@example.com or david@example.com → see fundi links (my tasks)

- [ ] **Step 3: Test client flow**
  - Login as client → browse companies → submit request to company
  - Login as admin → see request → assign PM
  - Login as PM → see assigned request → submit budget
  - Login as client → accept budget → project auto-created

- [ ] **Step 4: Test fundi flow**
  - Login as fundi → see assigned tasks → update status → upload photo

- [ ] **Step 5: Fix any issues encountered**

- [ ] **Step 6: Final commit**

```bash
git add -A && git commit -m "fix: resolve role migration issues"
```
