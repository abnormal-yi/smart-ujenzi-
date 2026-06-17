# SmartUjenzi — Role System Redesign

## Overview

Complete redesign of the SmartUjenzi construction management system with 5 distinct roles,
a clear request-to-completion workflow, and company selection by clients.

## Roles & Permissions

### 1. SUPER ADMIN
- Sees everything in the system
- Can add/remove ADMIN users
- Can suspend/delete any account
- Manages system settings
- No restrictions

### 2. ADMIN
- Receives all client requests
- Assigns PROJECT MANAGER to each request
- Approves or rejects projects
- Manages regular users (clients, fundi)
- Cannot change SUPER ADMIN settings

### 3. PROJECT MANAGER
- Communicates with client to gather full requirements
- Prepares budget (makadilio), timeline, and scope
- Assigns FUNDI to tasks
- Provides progress updates and project reports
- Cannot add ADMIN or manage all users

### 4. CLIENT
- Registers and logs in
- Sees list of construction companies
- Submits request to chosen company
- Views budget and can accept/reject
- Tracks project progress, payments, photos
- Cannot see other clients' projects

### 5. FUNDI (Constructor)
- Sees assigned projects and tasks
- Updates task completion status
- Uploads site photos/videos
- Reports challenges on site
- Cannot see budget or approve projects

## System Flow

```
CLIENT registers → browses companies → selects one → submits request
                                                          ↓
ADMIN receives request → assigns PROJECT MANAGER
                                          ↓
PROJECT MANAGER contacts client → gathers requirements
    → prepares budget, timeline → submits to client
                                          ↓
CLIENT reviews budget → accepts/rejects
                                          ↓
(on accept) PROJECT MANAGER creates project → assigns FUNDI → tasks start
                                                                      ↓
FUNDI completes tasks → uploads photos → marks done
                                          ↓
CLIENT views progress, payments, gallery
```

## Database Changes

### Modified: `users` table
- role enum changes to: `super_admin`, `admin`, `project_manager`, `fundi`, `client`

### Modified: `tasks` table
- `supervisor_id` → `fundi_id` (FK to users)

### Modified: `customer_requests` table
- Add `company_id` INT FK → companies(id) — which company the client chose
- Add `assigned_pm_id` INT FK → users(id) — PM assigned by ADMIN
- Add `budget_amount` DECIMAL(12,2) — PM's proposed budget
- Add `budget_status` VARCHAR — `pending`, `accepted`, `rejected`
- Add `proposed_timeline` VARCHAR — PM's proposed timeline

### Modified: `projects` table
- `manager_id` → `project_manager_id` (renamed)

### New: `company_reviews` table (optional TBD)
- For clients to rate companies after project completion

## Pages/Routes

### SUPER ADMIN pages (new)
- `super_admin/dashboard.php`
- `super_admin/users.php` — manage all users
- `super_admin/settings.php` — system settings

### ADMIN pages (existing modified)
- `dashboard.php` — filtered for ADMIN role
- `customer_requests.php` — assign PM to requests

### PROJECT MANAGER pages (new/modified)
- `pm/dashboard.php` — PM's assigned projects
- `pm/requirements.php` — gather client requirements
- `pm/budget.php` — prepare and submit budget
- `pm/tasks.php` — assign fundi, manage tasks
- `pm/progress.php` — update project progress

### CLIENT pages (new/modified)
- `client/dashboard.php` — browse companies, submit request
- `client/requests.php` — view submitted requests
- `client/progress.php` — view project progress (replaces customer_progress.php)
- `client/payments.php` — payment history

### FUNDI pages (new)
- `fundi/dashboard.php` — assigned projects and tasks
- `fundi/tasks.php` — update tasks, upload photos

## UX/UI
- Use same Tailwind CSS CDN theme as current system
- Consistent header/sidebar navigation per role
- Mobile responsive (mx-4, overflow-x-auto pattern)
- Heroicons inline SVGs (no emoji)

## Implementation Scope
1. Update DB schema (roles, new columns)
2. Update config/routing (role checks)
3. Build SUPER ADMIN pages
4. Modify ADMIN pages
5. Build PROJECT MANAGER pages
6. Build CLIENT pages (including company browsing)
7. Build FUNDI pages
8. Seed data for new roles
