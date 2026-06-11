-- SmartUjenzi - MySQL Database Schema
-- Run: mysql -u root < database/schema.sql
-- Or use: php -d extension=pdo_mysql setup.php

CREATE DATABASE IF NOT EXISTS test_smart_ujenzi;
USE test_smart_ujenzi;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS allocations;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS customer_requests;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    manager_id INT,
    start_date DATE,
    end_date DATE,
    FOREIGN KEY(manager_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Not Started',
    supervisor_id INT,
    deadline DATE,
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(supervisor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    details TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    unit VARCHAR(50) NOT NULL,
    low_stock_threshold INT DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY(project_id) REFERENCES projects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(sender_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    tagline VARCHAR(255),
    location VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) DEFAULT 'Tanzania',
    rating DECIMAL(2,1) DEFAULT 0.0,
    verified INT DEFAULT 0,
    years_experience INT DEFAULT 0,
    projects_completed INT DEFAULT 0,
    licenses TEXT,
    engineers INT DEFAULT 0,
    logo_initials VARCHAR(4),
    company_id VARCHAR(50) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    project_type VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    budget_range VARCHAR(255),
    description TEXT,
    company_proposal TEXT,
    proposed_budget VARCHAR(255),
    proposed_deadline VARCHAR(255),
    status VARCHAR(50) DEFAULT 'Pending',
    FOREIGN KEY(customer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data
-- Password: admin123 (bcrypt hash)
INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Admin', 'admin@example.com', '$2y$12$h5KvvZh1CvZdEWV5nfKBv.dsndWykdBbc8xyWkvL1JpfsmgzN8is6', 'admin'),
(2, 'Stephen Massawe', 'steve@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'manager'),
(3, 'Teleza Mkomwa', 'teleza@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'supervisor'),
(4, 'Ali Fundi', 'ali@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'supervisor'),
(5, 'Zainab Contractor', 'zainab@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'manager'),
(6, 'John Mteja', 'mteja@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'customer'),
(7, 'Daud Fundi', 'constructor@example.com', '$2y$12$DFciiduJ4J/O7ScHIqIvx.tRPV3QsbfLk3AlHH.qc2zG9kSgFGsMO', 'constructor');

INSERT INTO companies (name, tagline, location, city, country, rating, verified, years_experience, projects_completed, licenses, engineers, logo_initials, company_id) VALUES
('Kazi Bora Constructors Ltd', 'Quality and Speed in Every Brick', 'Njiro, Arusha', 'arusha', 'Tanzania', 4.8, 1, 12, 34, 'NCA-Class-1-Reg-894\nERC-Electrical-Permit-B7', 8, 'KA', 'COMP_1'),
('Aman Builders & Architects', 'Sustainable Designs, Sustainable Spaces', 'Mikocheni, Dar es Salaam', 'dar', 'Tanzania', 4.6, 1, 8, 19, 'NCA-Class-2-Reg-004\nCRB-Tanzania-7821', 5, 'AM', 'COMP_2'),
('Pamoja Modern Designs', 'Building For the Community', 'Sinza, Dar es Salaam', 'dar', 'Tanzania', 4.2, 0, 4, 4, 'NCA-Class-4-Pending-231', 2, 'PA', 'COMP_3');

INSERT INTO customer_requests (customer_id, project_type, location, budget_range, description, status) VALUES
(6, 'Residential House', 'Dar es Salaam, Masaki', '50M - 100M TZS', 'Looking for a reliable company to build a 4-bedroom house. Have plot already.', 'Pending');

INSERT INTO projects (id, name, description, status, manager_id, start_date, end_date) VALUES
(1, 'IAA New Library', 'Construction of a modern library', 'Ongoing', 2, '2025-01-01', '2026-05-01'),
(2, 'Hostel Block B', 'Expansion of students hostel', 'Pending', 2, '2025-08-01', '2026-12-01'),
(3, 'City Mall Extension', 'Adding a new wing to City Mall', 'In Progress', 5, '2025-10-10', '2027-02-15'),
(4, 'Mwanza Hospital Block', 'New maternity ward', 'Completed', 2, '2023-01-05', '2024-12-20');

INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES
(1, 'Foundation Laying', 'Excavation and foundation laying', 'Completed', 3, '2025-02-15'),
(1, 'Brickwork Phase 1', 'Ground floor brickwork', 'In Progress', 3, '2025-06-30'),
(2, 'Site Clearance', 'Clearing the bush and trees', 'Completed', 4, '2025-08-15'),
(2, 'Foundation Excavation', 'Digging trenches', 'Not Started', 4, '2025-09-01'),
(3, 'Structural Framing', 'Putting up steel columns', 'In Progress', 3, '2026-01-20');

INSERT INTO resources (id, type, name, details) VALUES
(1, 'labor', 'Fundi Juma (Mason)', 'Senior Mason, 10 yrs exp'),
(2, 'labor', 'Fundi Asha (Electrician)', 'Electrical installations'),
(3, 'labor', 'Fundi Baraka (Plumber)', 'Plumbing and piping'),
(4, 'equipment', 'Concrete Mixer', 'Small mixer, 500L'),
(5, 'equipment', 'Excavator (JCB)', 'Heavy excavation'),
(6, 'equipment', 'Crane (Tower)', 'Tower crane for City Mall'),
(7, 'labor', 'Fundi Daud (Welder)', 'Certified Welder');

INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES
(1, 'Cement (Simba)', 150, 'Bags', 20),
(2, 'Bricks', 5000, 'Pieces', 1000),
(3, 'Iron Bars (Y12)', 300, 'Pieces', 50),
(4, 'Sand', 25, 'Tons', 10),
(5, 'Gravel', 18, 'Tons', 15),
(6, 'Paint (White)', 8, 'Buckets', 10);

INSERT INTO allocations (project_id, type, item_id, quantity) VALUES
(1, 'resource', 1, 1),
(1, 'resource', 5, 1),
(3, 'resource', 6, 1),
(3, 'resource', 7, 1),
(1, 'material', 1, 50),
(1, 'material', 2, 2000);

INSERT INTO notifications (user_id, message, is_read, created_at) VALUES
(1, 'Low stock alert: Cement (Simba) is running low.', 0, NOW()),
(1, 'Task Foundation Laying has been marked as Completed.', 0, NOW()),
(1, 'Low stock alert: Paint (White) is running low.', 0, NOW()),
(2, 'New task added: Structural Framing', 0, NOW()),
(3, 'You have been assigned to Foundation Laying', 1, NOW());

INSERT INTO messages (project_id, sender_id, message, created_at) VALUES
(1, 2, 'Has the cement arrived on site?', '2026-05-01 10:00:00'),
(1, 3, 'Yes sir, we just received 150 bags.', '2026-05-01 10:15:00'),
(1, 2, 'Great, start the foundation laying immediately.', '2026-05-01 10:20:00'),
(2, 4, 'Site clearance is done. Ready for excavation.', '2025-08-15 16:00:00');
