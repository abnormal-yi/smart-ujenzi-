-- SmartUjenzi - MySQL Database Schema
-- Run: mysql -u root < database/schema.sql
-- Or use: php -d extension=pdo_mysql setup.php

-- Create the database if it doesn't already exist
CREATE DATABASE IF NOT EXISTS test_smart_ujenzi;
USE test_smart_ujenzi;

-- Temporarily disable foreign key checks so tables can be dropped in any order
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all existing tables to ensure a clean slate before re-creating
DROP TABLE IF EXISTS request_documents;
DROP TABLE IF EXISTS password_resets;
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

-- Re-enable foreign key checks after dropping
SET FOREIGN_KEY_CHECKS = 1;

-- ====================
-- Table: users
-- Stores system users with role-based access (super_admin, admin, project_manager, fundi, client)
-- ====================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,              -- User's full name
    email VARCHAR(255) UNIQUE NOT NULL,       -- Login email (unique constraint)
    password VARCHAR(255) NOT NULL,           -- bcrypt hashed password
    role VARCHAR(50) NOT NULL,                -- Role: super_admin, admin, project_manager, fundi, client
    location VARCHAR(255) DEFAULT '',          -- Region, District, Ward selected during registration
    skills TEXT DEFAULT '',                      -- Skills/trade for fundi role (e.g. Mason, Plumber)
    approved TINYINT(1) DEFAULT 1               -- 1=approved, 0=pending (fundi needs PM approval)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: projects
-- Stores construction projects managed by admin/manager roles
-- ====================
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

-- ====================
-- Table: tasks
-- Individual tasks within a project, assignable to supervisors
-- ====================
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

-- ====================
-- Table: resources
-- Tracks labor (mafundi) and equipment available for assignment to projects
-- ====================
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,               -- 'labor' or 'equipment'
    name VARCHAR(255) NOT NULL,              -- Resource name (e.g. Fundi Juma, Concrete Mixer)
    details TEXT                             -- Additional details (experience, specs, etc.)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: materials
-- Inventory of construction materials with low-stock alert thresholds
-- ====================
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,              -- Material name (e.g. Cement, Bricks)
    quantity INT DEFAULT 0,                   -- Current stock quantity
    unit VARCHAR(50) NOT NULL,               -- Unit of measure (Bags, Pieces, Tons, etc.)
    low_stock_threshold INT DEFAULT 10       -- Quantity threshold that triggers a low-stock alert
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: allocations
-- Links resources and materials to specific projects (many-to-many)
-- ====================
CREATE TABLE allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,                 -- Foreign key to projects(id)
    type VARCHAR(50) NOT NULL,               -- 'resource' or 'material'
    item_id INT NOT NULL,                    -- ID of the resource or material being allocated
    quantity INT DEFAULT 1,                  -- Quantity allocated
    FOREIGN KEY(project_id) REFERENCES projects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: notifications
-- System notifications sent to users (e.g. low stock alerts, status changes)
-- ====================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                    -- Foreign key to users(id) - the recipient
    message TEXT NOT NULL,                    -- Notification message content
    link VARCHAR(500) DEFAULT NULL,          -- URL to navigate to when clicked
    is_read INT DEFAULT 0,                   -- 0 = unread, 1 = read
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP  -- Timestamp of when notification was created
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: messages
-- Project-specific discussion messages (chat between team members)
-- ====================
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,                 -- Foreign key to projects(id)
    sender_id INT NOT NULL,                  -- Foreign key to users(id) - who sent the message
    message TEXT NOT NULL,                    -- Message body text
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- Timestamp of when message was sent
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(sender_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: companies
-- NCA verified contractor companies displayed on the landing page
-- ====================
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,              -- Company name
    tagline VARCHAR(255),                     -- Company tagline/motto
    location VARCHAR(255) NOT NULL,          -- Full location address
    city VARCHAR(100) NOT NULL,              -- City for filtering (arusha, dar, etc.)
    country VARCHAR(100) DEFAULT 'Tanzania',
    rating DECIMAL(2,1) DEFAULT 0.0,        -- Average rating (e.g. 4.8)
    verified INT DEFAULT 0,                  -- 0 = pending verification, 1 = verified
    years_experience INT DEFAULT 0,          -- Years in business
    projects_completed INT DEFAULT 0,        -- Number of projects completed
    licenses TEXT,                            -- NCA/license information
    engineers INT DEFAULT 0,                  -- Number of on-site project managers/engineers
    logo_initials VARCHAR(4),                 -- Company initials displayed as logo
    company_id VARCHAR(50) UNIQUE            -- Unique company identifier (e.g. COMP_1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: customer_requests
-- Customer-submitted project requests that can be reviewed by admin/manager
-- ====================
CREATE TABLE customer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    company_id INT,
    project_type VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    company_proposal TEXT,
    assigned_pm_id INT,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(customer_id) REFERENCES users(id),
    FOREIGN KEY(company_id) REFERENCES companies(id),
    FOREIGN KEY(assigned_pm_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: request_documents
-- Client-uploaded documents attached to customer requests
-- ====================
CREATE TABLE request_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    file_type VARCHAR(100) NOT NULL DEFAULT '',
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES customer_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: payments
-- Customer payment records for projects
-- ====================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Completed',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(project_id) REFERENCES projects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: project_media
-- Supervisor-uploaded photos/videos documenting project progress
-- ====================
CREATE TABLE project_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    task_id INT DEFAULT NULL,
    uploaded_by INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'image',
    caption TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(task_id) REFERENCES tasks(id),
    FOREIGN KEY(uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: regions
-- Tanzania administrative regions (31 total)
-- ====================
CREATE TABLE regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: districts
-- Tanzania districts within regions (~184 total)
-- ====================
CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY(region_id) REFERENCES regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: wards
-- Tanzania wards/mitaa within districts
-- ====================
CREATE TABLE wards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY(district_id) REFERENCES districts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: user_devices
-- Known devices/browsers for MFA skip
-- ====================
CREATE TABLE user_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: otp_codes
-- One-time passwords sent via email for MFA
-- ====================
CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(255),
    user_email VARCHAR(255),
    user_role VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    severity VARCHAR(20) DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_user (user_id, created_at),
    INDEX idx_audit_action (action, created_at),
    INDEX idx_audit_severity (severity, created_at),
    INDEX idx_audit_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: password_resets
-- Stores password reset tokens with expiration
-- ====================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Seed Data
-- ====================

-- Demo users with pre-hashed bcrypt passwords
-- Password: admin123 (bcrypt hash)
INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Super Admin', 'super@example.com', '$2y$12$h5KvvZh1CvZdEWV5nfKBv.dsndWykdBbc8xyWkvL1JpfsmgzN8is6', 'super_admin'),
(2, 'Stephen Massawe', 'steve@example.com', '$2y$12$JluMmHAeFE47pAainTnIJ.VwEBXXTY4xUPrrBl29ifuL.mCjOUCFS', 'project_manager'),
(3, 'Teleza Mkomwa', 'teleza@example.com', '$2y$12$JluMmHAeFE47pAainTnIJ.VwEBXXTY4xUPrrBl29ifuL.mCjOUCFS', 'project_manager'),
(4, 'Ali Fundi', 'ali@example.com', '$2y$12$JluMmHAeFE47pAainTnIJ.VwEBXXTY4xUPrrBl29ifuL.mCjOUCFS', 'fundi'),
(5, 'Zainab Admin', 'zainab@example.com', '$2y$12$JluMmHAeFE47pAainTnIJ.VwEBXXTY4xUPrrBl29ifuL.mCjOUCFS', 'admin'),
(6, 'John Mteja', 'client@example.com', '$2y$12$JluMmHAeFE47pAainTnIJ.VwEBXXTY4xUPrrBl29ifuL.mCjOUCFS', 'client'),
(7, 'Daud Fundi', 'david@example.com', '$2y$12$JluMmHAeFE47pAainTnIJ.VwEBXXTY4xUPrrBl29ifuL.mCjOUCFS', 'fundi');

-- NCA verified contractor companies (displayed on landing page)
INSERT INTO companies (name, tagline, location, city, country, rating, verified, years_experience, projects_completed, licenses, engineers, logo_initials, company_id) VALUES
('Kazi Bora Constructors Ltd', 'Quality and Speed in Every Brick', 'Njiro, Arusha', 'arusha', 'Tanzania', 4.8, 1, 12, 34, 'NCA-Class-1-Reg-894\nERC-Electrical-Permit-B7', 8, 'KA', 'COMP_1'),
('Aman Builders & Architects', 'Sustainable Designs, Sustainable Spaces', 'Mikocheni, Dar es Salaam', 'dar', 'Tanzania', 4.6, 1, 8, 19, 'NCA-Class-2-Reg-004\nCRB-Tanzania-7821', 5, 'AM', 'COMP_2'),
('Pamoja Modern Designs', 'Building For the Community', 'Sinza, Dar es Salaam', 'dar', 'Tanzania', 4.2, 0, 4, 4, 'NCA-Class-4-Pending-231', 2, 'PA', 'COMP_3');

-- Sample customer request
INSERT INTO customer_requests (customer_id, company_id, project_type, location, description, status) VALUES
(6, 1, 'Residential House', 'Dar es Salaam, Masaki', 'Looking for a reliable company to build a 4-bedroom house. Have plot already.', 'Pending');

-- Sample projects with different statuses
INSERT INTO projects (id, name, description, status, project_manager_id, customer_id, start_date, end_date) VALUES
(1, 'IAA New Library', 'Construction of a modern library', 'Ongoing', 2, 6, '2025-01-01', '2026-05-01'),
(2, 'Hostel Block B', 'Expansion of students hostel', 'Pending', 2, NULL, '2025-08-01', '2026-12-01'),
(3, 'City Mall Extension', 'Adding a new wing to City Mall', 'In Progress', 3, NULL, '2025-10-10', '2027-02-15'),
(4, 'Mwanza Hospital Block', 'New maternity ward', 'Completed', 2, 6, '2023-01-05', '2024-12-20');

-- Tasks assigned to various projects and supervisors
INSERT INTO tasks (project_id, name, description, status, fundi_id, deadline) VALUES
(1, 'Foundation Laying', 'Excavation and foundation laying', 'Completed', 4, '2025-02-15'),
(1, 'Brickwork Phase 1', 'Ground floor brickwork', 'In Progress', 7, '2025-06-30'),
(2, 'Site Clearance', 'Clearing the bush and trees', 'Completed', 4, '2025-08-15'),
(2, 'Foundation Excavation', 'Digging trenches', 'Not Started', 7, '2025-09-01'),
(3, 'Structural Framing', 'Putting up steel columns', 'In Progress', 7, '2026-01-20');

-- Available labor and equipment resources
INSERT INTO resources (id, type, name, details) VALUES
(1, 'labor', 'Fundi Juma (Mason)', 'Senior Mason, 10 yrs exp'),
(2, 'labor', 'Fundi Asha (Electrician)', 'Electrical installations'),
(3, 'labor', 'Fundi Baraka (Plumber)', 'Plumbing and piping'),
(4, 'equipment', 'Concrete Mixer', 'Small mixer, 500L'),
(5, 'equipment', 'Excavator (JCB)', 'Heavy excavation'),
(6, 'equipment', 'Crane (Tower)', 'Tower crane for City Mall'),
(7, 'labor', 'Fundi Daud (Welder)', 'Certified Welder');

-- Material inventory with low stock thresholds
INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES
(1, 'Cement (Simba)', 150, 'Bags', 20),
(2, 'Bricks', 5000, 'Pieces', 1000),
(3, 'Iron Bars (Y12)', 300, 'Pieces', 50),
(4, 'Sand', 25, 'Tons', 10),
(5, 'Gravel', 18, 'Tons', 15),
(6, 'Paint (White)', 8, 'Buckets', 10);   -- Below threshold (10) — triggers low stock alert

-- Resource and material allocations to projects
INSERT INTO allocations (project_id, type, item_id, quantity) VALUES
(1, 'resource', 1, 1),    -- Fundi Juma assigned to IAA Library
(1, 'resource', 5, 1),    -- Excavator assigned to IAA Library
(3, 'resource', 6, 1),    -- Tower crane assigned to City Mall
(3, 'resource', 7, 1),    -- Welder assigned to City Mall
(1, 'material', 1, 50),   -- 50 bags of cement allocated to IAA Library
(1, 'material', 2, 2000); -- 2000 bricks allocated to IAA Library

-- System notifications (some unread, some read)
INSERT INTO notifications (user_id, message, is_read, created_at) VALUES
(1, 'Low stock alert: Cement (Simba) is running low.', 0, NOW()),
(1, 'Task Foundation Laying has been marked as Completed.', 0, NOW()),
(1, 'Low stock alert: Paint (White) is running low.', 0, NOW()),
(2, 'New task added: Structural Framing', 0, NOW()),
(3, 'You have been assigned to Foundation Laying', 1, NOW());

-- Sample project discussion messages
INSERT INTO messages (project_id, sender_id, message, created_at) VALUES
(1, 2, 'Has the cement arrived on site?', '2026-05-01 10:00:00'),
(1, 3, 'Yes sir, we just received 150 bags.', '2026-05-01 10:15:00'),
(1, 2, 'Great, start the foundation laying immediately.', '2026-05-01 10:20:00'),
(2, 4, 'Site clearance is done. Ready for excavation.', '2025-08-15 16:00:00');

-- Sample payments (customer John Mteja for project IAA Library)
INSERT INTO payments (project_id, amount, payment_date, status, description) VALUES
(1, 15000000.00, '2025-01-15', 'Completed', 'Initial deposit for foundation work'),
(1, 25000000.00, '2025-03-01', 'Completed', 'Phase 1 milestone payment'),
(1, 10000000.00, '2025-06-10', 'Completed', 'Brickwork materials advance'),
(4, 50000000.00, '2024-06-01', 'Completed', 'Full payment for Mwanza Hospital Block');

-- Sample media uploads (supervisor documented progress)
INSERT INTO project_media (project_id, task_id, uploaded_by, file_path, type, caption) VALUES
(1, 1, 3, 'public/uploads/foundation-excavation.jpg', 'image', 'Foundation excavation complete - depth 2m as per spec'),
(1, 1, 3, 'public/uploads/foundation-concrete.jpg', 'image', 'Concrete pouring for foundation footings'),
(1, 2, 3, 'public/uploads/brickwork-progress.jpg', 'image', 'Ground floor brickwork - east wall progress'),
(3, 5, 3, 'public/uploads/steel-columns.jpg', 'image', 'Steel columns erected for City Mall extension');
