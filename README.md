# SmartUjenzi - Smart Construction Management System

A construction project management platform built with **pure PHP + MySQL** — no JavaScript frameworks, no build step.

Manages projects, tasks, materials, workers, messaging, customer requests, and contractor discovery.

## Features

- **Role-Based Access** — Admin, Manager, Supervisor, Constructor, Customer
- **Project Management** — Create, track status (Pending, In Progress, Ongoing, Completed)
- **Task Management** — Assign supervisors, set deadlines, auto-notify on status changes
- **Material Tracking** — Stock levels, low-stock alerts, adjust quantities
- **Worker/Equipment Management** — Laborers, equipment, assign to projects, track allocations
- **Messaging** — Project-based chat between team members
- **Customer Requests** — Customers submit project requests, admin accepts to auto-create projects
- **Reports** — Project progress, overdue tasks, stock reports, summary stats
- **Contractor Directory** — Browse NCA-verified contractors with search/filter (from database)
- **Notifications** — Dropdown alerts for stock, tasks, assignments

## Requirements

- **PHP** 8.1+
- **MySQL** 8.0+ (MariaDB compatible)
- **PHP Extensions:** `pdo_mysql`, `mysqli`
- No Composer dependencies required

## Quick Start

```bash
# 1. Clone
git clone https://github.com/abnormal-yi/smart-ujenzi-.git
cd smart-ujenzi-

# 2. Setup database
php -d extension=pdo_mysql setup.php

# 3. Start server
php start.sh
#   or: php -d extension=pdo_mysql -S localhost:8000
```

Open **http://localhost:8000** in your browser.

## Demo Accounts

| Email | Password | Role |
|---|---|---|
| admin@example.com | admin123 | Admin |
| steve@example.com | pass123 | Manager |
| zainab@example.com | pass123 | Manager |
| teleza@example.com | pass123 | Supervisor |
| ali@example.com | pass123 | Supervisor |
| constructor@example.com | pass123 | Constructor |
| mteja@example.com | pass123 | Customer |

## Role Permissions

| Page | Admin | Manager | Supervisor | Constructor | Customer |
|---|---|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| Projects | ✅ | ✅ | ✅ | ✅ | — |
| Tasks | ✅ | ✅ | ✅ | ✅ | — |
| Materials | ✅ | ✅ | ✅ | — | — |
| Workers | ✅ | ✅ | ✅ | — | — |
| Messages | ✅ | ✅ | ✅ | — | — |
| Customer Requests | ✅ | ✅ | — | — | ✅ |
| Reports | ✅ | ✅ | — | — | — |

## Project Structure

```
├── index.php              # Landing page with contractor directory
├── login.php              # Authentication
├── logout.php             # Logout
├── dashboard.php          # Role-based dashboard
├── projects.php           # Project CRUD
├── tasks.php              # Task management
├── materials.php          # Material stock tracking
├── workers.php            # Labor & equipment management
├── messages.php           # Project chat
├── customer_requests.php  # Customer project requests
├── reports.php            # Analytics & reports
├── setup.php              # Database installer
├── start.sh               # Dev server launcher
├── includes/
│   ├── config.php         # DB connection & auth helpers
│   ├── functions.php      # Query helpers & role guards
│   ├── header.php         # Sidebar + navigation + notifications
│   └── footer.php         # Closing HTML + scripts
├── database/
│   └── schema.sql         # Full schema + seed data
├── assets/
│   ├── css/style.css      # Custom styles
│   └── js/app.js          # Client-side interactivity
├── public/                # Uploaded images
└── composer.json
```

## Database

- Uses **MySQL via Unix socket** (`/var/run/mysqld/mysqld.sock`)
- Database name: `test_smart_ujenzi`
- Run `php setup.php` to create tables + seed demo data
- 10 tables: users, projects, tasks, materials, resources, allocations, customer_requests, messages, notifications, companies

## License

MIT
