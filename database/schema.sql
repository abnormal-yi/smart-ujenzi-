-- SmartUjenzi - MySQL Database Schema
-- Run: mysql -u root < database/schema.sql
-- Or use: php -d extension=pdo_mysql setup.php

-- Create the database if it doesn't already exist
CREATE DATABASE IF NOT EXISTS test_smart_ujenzi;
USE test_smart_ujenzi;

-- Temporarily disable foreign key checks so tables can be dropped in any order
SET FOREIGN_KEY_CHECKS = 0;

-- Drop all existing tables to ensure a clean slate before re-creating
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
-- Stores system users with role-based access (admin, manager, supervisor, constructor, customer)
-- ====================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,              -- User's full name
    email VARCHAR(255) UNIQUE NOT NULL,       -- Login email (unique constraint)
    password VARCHAR(255) NOT NULL,           -- bcrypt hashed password
    role VARCHAR(50) NOT NULL                 -- Role: admin, manager, supervisor, constructor, customer
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: projects
-- Stores construction projects managed by admin/manager roles
-- ====================
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,              -- Project name
    description TEXT,                         -- Project description
    status VARCHAR(50) DEFAULT 'Pending',    -- Status: Pending, Ongoing, In Progress, Completed, On Hold
    manager_id INT,                          -- Foreign key to users(id) - the project manager
    start_date DATE,                         -- Project start date
    end_date DATE,                           -- Project end date
    FOREIGN KEY(manager_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Table: tasks
-- Individual tasks within a project, assignable to supervisors
-- ====================
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,                 -- Foreign key to projects(id)
    name VARCHAR(255) NOT NULL,              -- Task name
    description TEXT,                         -- Task description
    status VARCHAR(50) DEFAULT 'Not Started',-- Status: Not Started, In Progress, Completed, On Hold
    supervisor_id INT,                       -- Foreign key to users(id) - the assigned supervisor
    deadline DATE,                           -- Task deadline date
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(supervisor_id) REFERENCES users(id)
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
    customer_id INT NOT NULL,                -- Foreign key to users(id) - the customer
    project_type VARCHAR(255) NOT NULL,      -- Type of project requested
    location VARCHAR(255) NOT NULL,          -- Desired project location
    budget_range VARCHAR(255),               -- Customer's budget range
    description TEXT,                         -- Detailed project description
    company_proposal TEXT,                    -- Admin/manager's response proposal
    proposed_budget VARCHAR(255),            -- Company's proposed budget
    proposed_deadline VARCHAR(255),          -- Company's proposed deadline
    status VARCHAR(50) DEFAULT 'Pending',   -- Status: Pending, Reviewed, Accepted, Rejected
    FOREIGN KEY(customer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================
-- Seed Data
-- ====================

-- Demo users with pre-hashed bcrypt passwords
-- Password: admin123 (bcrypt hash)
INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Admin', 'admin@example.com', '$2y$12$h5KvvZh1CvZdEWV5nfKBv.dsndWykdBbc8xyWkvL1JpfsmgzN8is6', 'admin'),
(2, 'Stephen Massawe', 'steve@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'manager'),
(3, 'Teleza Mkomwa', 'teleza@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'supervisor'),
(4, 'Ali Fundi', 'ali@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'supervisor'),
(5, 'Zainab Contractor', 'zainab@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'manager'),
(6, 'John Mteja', 'mteja@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'customer'),
(7, 'Daud Fundi', 'constructor@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'constructor');

-- NCA verified contractor companies (displayed on landing page)
INSERT INTO companies (name, tagline, location, city, country, rating, verified, years_experience, projects_completed, licenses, engineers, logo_initials, company_id) VALUES
('Kazi Bora Constructors Ltd', 'Quality and Speed in Every Brick', 'Njiro, Arusha', 'arusha', 'Tanzania', 4.8, 1, 12, 34, 'NCA-Class-1-Reg-894\nERC-Electrical-Permit-B7', 8, 'KA', 'COMP_1'),
('Aman Builders & Architects', 'Sustainable Designs, Sustainable Spaces', 'Mikocheni, Dar es Salaam', 'dar', 'Tanzania', 4.6, 1, 8, 19, 'NCA-Class-2-Reg-004\nCRB-Tanzania-7821', 5, 'AM', 'COMP_2'),
('Pamoja Modern Designs', 'Building For the Community', 'Sinza, Dar es Salaam', 'dar', 'Tanzania', 4.2, 0, 4, 4, 'NCA-Class-4-Pending-231', 2, 'PA', 'COMP_3');

-- Sample customer request
INSERT INTO customer_requests (customer_id, project_type, location, budget_range, description, status) VALUES
(6, 'Residential House', 'Dar es Salaam, Masaki', '50M - 100M TZS', 'Looking for a reliable company to build a 4-bedroom house. Have plot already.', 'Pending');

-- Sample projects with different statuses
INSERT INTO projects (id, name, description, status, manager_id, start_date, end_date) VALUES
(1, 'IAA New Library', 'Construction of a modern library', 'Ongoing', 2, '2025-01-01', '2026-05-01'),
(2, 'Hostel Block B', 'Expansion of students hostel', 'Pending', 2, '2025-08-01', '2026-12-01'),
(3, 'City Mall Extension', 'Adding a new wing to City Mall', 'In Progress', 5, '2025-10-10', '2027-02-15'),
(4, 'Mwanza Hospital Block', 'New maternity ward', 'Completed', 2, '2023-01-05', '2024-12-20');

-- Tasks assigned to various projects and supervisors
INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES
(1, 'Foundation Laying', 'Excavation and foundation laying', 'Completed', 3, '2025-02-15'),
(1, 'Brickwork Phase 1', 'Ground floor brickwork', 'In Progress', 3, '2025-06-30'),
(2, 'Site Clearance', 'Clearing the bush and trees', 'Completed', 4, '2025-08-15'),
(2, 'Foundation Excavation', 'Digging trenches', 'Not Started', 4, '2025-09-01'),
(3, 'Structural Framing', 'Putting up steel columns', 'In Progress', 3, '2026-01-20');

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
