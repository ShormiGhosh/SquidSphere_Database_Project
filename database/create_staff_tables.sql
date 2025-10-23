-- =====================================================
-- SQUID SPHERE DATABASE - STAFF HIERARCHY SYSTEM
-- Lab 03: DDL, Constraints
-- Lab 06: Self Join for Hierarchy
-- =====================================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS staff_assignments;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS staff;

-- =====================================================
-- 1. STAFF TABLE (with Self-Referencing Foreign Key)
-- =====================================================
CREATE TABLE staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_number VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('Circle', 'Triangle', 'Square', 'Front Man') NOT NULL,
    supervisor_id INT NULL,  -- SELF JOIN: References another staff member
    hire_date DATE DEFAULT (CURRENT_DATE),
    salary DECIMAL(10, 2) NOT NULL,
    status ENUM('active', 'terminated') DEFAULT 'active',
    
    -- Self-referencing foreign key
    CONSTRAINT fk_supervisor 
        FOREIGN KEY (supervisor_id) 
        REFERENCES staff(staff_id) 
        ON DELETE SET NULL,
    
    -- Check constraint for salary based on role
    CONSTRAINT chk_salary CHECK (salary > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create index for faster supervisor lookups
CREATE INDEX idx_supervisor ON staff(supervisor_id);
CREATE INDEX idx_role ON staff(role);

-- =====================================================
-- 2. GAMES TABLE (Reference for assignments)
-- =====================================================
CREATE TABLE games (
    game_id INT PRIMARY KEY AUTO_INCREMENT,
    game_name VARCHAR(100) NOT NULL,
    game_type VARCHAR(50) NOT NULL,
    max_players INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. STAFF ASSIGNMENTS TABLE (Many-to-Many relationship)
-- =====================================================
CREATE TABLE staff_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    game_id INT NOT NULL,
    round_number INT NOT NULL,
    role_description VARCHAR(255) NOT NULL,
    assignment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    hours_worked DECIMAL(5, 2) DEFAULT 0,
    
    -- Foreign keys
    CONSTRAINT fk_staff_assignment 
        FOREIGN KEY (staff_id) 
        REFERENCES staff(staff_id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_game_assignment 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE CASCADE,
    
    -- Unique constraint: same staff can't be assigned twice to same game round
    CONSTRAINT uq_staff_game_round 
        UNIQUE (staff_id, game_id, round_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes for faster queries
CREATE INDEX idx_staff_assignment ON staff_assignments(staff_id);
CREATE INDEX idx_game_assignment ON staff_assignments(game_id);
CREATE INDEX idx_round ON staff_assignments(round_number);

-- =====================================================
-- 4. INSERT SAMPLE DATA
-- =====================================================

-- Insert Games
INSERT INTO games (game_id, game_name, game_type, max_players, description) VALUES
(1, 'Red Light Green Light', 'Elimination', 456, 'Players must reach the finish line without being caught moving'),
(2, 'Honeycomb', 'Precision', 456, 'Extract candy shapes without breaking them'),
(3, 'Tug of War', 'Team', 20, 'Two teams pull rope, losing team falls'),
(4, 'Marbles', 'Pairs', 456, 'Win all 20 marbles from partner'),
(5, 'Glass Bridge', 'Probability', 16, 'Cross bridge by choosing correct glass panels'),
(6, 'Squid Game', 'Final Battle', 2, 'Final one-on-one combat');

-- Insert Front Man (No supervisor)
INSERT INTO staff (staff_number, name, role, supervisor_id, hire_date, salary, status) VALUES
('FM-001', 'In-ho', 'Front Man', NULL, '2020-01-01', 50000000.00, 'active');

-- Insert Square Guards (Supervised by Front Man)
INSERT INTO staff (staff_number, name, role, supervisor_id, hire_date, salary, status) VALUES
('SQ-001', 'Jun-ho', 'Square', 1, '2020-02-01', 5000000.00, 'active'),
('SQ-002', 'Gi-hun Assistant', 'Square', 1, '2020-02-15', 5000000.00, 'active'),
('SQ-003', 'Sang-woo Watcher', 'Square', 1, '2020-03-01', 5000000.00, 'active'),
('SQ-004', 'Sae-byeok Monitor', 'Square', 1, '2020-03-15', 5000000.00, 'active'),
('SQ-005', 'Ali Observer', 'Square', 1, '2020-04-01', 5000000.00, 'active');

-- Insert Triangle Guards (Supervised by Square Guards)
INSERT INTO staff (staff_number, name, role, supervisor_id, hire_date, salary, status) VALUES
('TR-001', 'Triangle Leader A', 'Triangle', 2, '2020-05-01', 2000000.00, 'active'),
('TR-002', 'Triangle Leader B', 'Triangle', 2, '2020-05-05', 2000000.00, 'active'),
('TR-003', 'Triangle Leader C', 'Triangle', 3, '2020-05-10', 2000000.00, 'active'),
('TR-004', 'Triangle Leader D', 'Triangle', 3, '2020-05-15', 2000000.00, 'active'),
('TR-005', 'Triangle Leader E', 'Triangle', 4, '2020-05-20', 2000000.00, 'active'),
('TR-006', 'Triangle Leader F', 'Triangle', 4, '2020-05-25', 2000000.00, 'active'),
('TR-007', 'Triangle Leader G', 'Triangle', 5, '2020-06-01', 2000000.00, 'active'),
('TR-008', 'Triangle Leader H', 'Triangle', 5, '2020-06-05', 2000000.00, 'active'),
('TR-009', 'Triangle Leader I', 'Triangle', 6, '2020-06-10', 2000000.00, 'active'),
('TR-010', 'Triangle Leader J', 'Triangle', 6, '2020-06-15', 2000000.00, 'active');

-- Insert Circle Guards (Supervised by Triangle Guards)
INSERT INTO staff (staff_number, name, role, supervisor_id, hire_date, salary, status) VALUES
('CR-001', 'Circle Worker 1', 'Circle', 7, '2020-07-01', 500000.00, 'active'),
('CR-002', 'Circle Worker 2', 'Circle', 7, '2020-07-02', 500000.00, 'active'),
('CR-003', 'Circle Worker 3', 'Circle', 7, '2020-07-03', 500000.00, 'active'),
('CR-004', 'Circle Worker 4', 'Circle', 8, '2020-07-04', 500000.00, 'active'),
('CR-005', 'Circle Worker 5', 'Circle', 8, '2020-07-05', 500000.00, 'active'),
('CR-006', 'Circle Worker 6', 'Circle', 9, '2020-07-06', 500000.00, 'active'),
('CR-007', 'Circle Worker 7', 'Circle', 9, '2020-07-07', 500000.00, 'active'),
('CR-008', 'Circle Worker 8', 'Circle', 10, '2020-07-08', 500000.00, 'active'),
('CR-009', 'Circle Worker 9', 'Circle', 10, '2020-07-09', 500000.00, 'active'),
('CR-010', 'Circle Worker 10', 'Circle', 11, '2020-07-10', 500000.00, 'active'),
('CR-011', 'Circle Worker 11', 'Circle', 11, '2020-07-11', 500000.00, 'active'),
('CR-012', 'Circle Worker 12', 'Circle', 12, '2020-07-12', 500000.00, 'active'),
('CR-013', 'Circle Worker 13', 'Circle', 12, '2020-07-13', 500000.00, 'active'),
('CR-014', 'Circle Worker 14', 'Circle', 13, '2020-07-14', 500000.00, 'active'),
('CR-015', 'Circle Worker 15', 'Circle', 13, '2020-07-15', 500000.00, 'active'),
('CR-016', 'Circle Worker 16', 'Circle', 14, '2020-07-16', 500000.00, 'active'),
('CR-017', 'Circle Worker 17', 'Circle', 14, '2020-07-17', 500000.00, 'active'),
('CR-018', 'Circle Worker 18', 'Circle', 15, '2020-07-18', 500000.00, 'active'),
('CR-019', 'Circle Worker 19', 'Circle', 15, '2020-07-19', 500000.00, 'active'),
('CR-020', 'Circle Worker 20', 'Circle', 16, '2020-07-20', 500000.00, 'active');

-- Insert Staff Assignments (Game 1: Red Light Green Light)
INSERT INTO staff_assignments (staff_id, game_id, round_number, role_description, hours_worked) VALUES
(2, 1, 1, 'Supervisor - Monitor all guards', 8.0),
(7, 1, 1, 'Doll Operator', 6.5),
(8, 1, 1, 'Starting Line Guard', 6.5),
(17, 1, 1, 'Elimination Guard - Left Side', 6.5),
(18, 1, 1, 'Elimination Guard - Right Side', 6.5),
(19, 1, 1, 'Player Monitoring', 6.5),
(20, 1, 1, 'Cleanup Crew', 2.0);

-- Insert Staff Assignments (Game 2: Honeycomb)
INSERT INTO staff_assignments (staff_id, game_id, round_number, role_description, hours_worked) VALUES
(3, 2, 2, 'Supervisor - Honeycomb Distribution', 7.0),
(9, 2, 2, 'Shape Verification', 5.5),
(21, 2, 2, 'Guard Station 1', 5.5),
(22, 2, 2, 'Guard Station 2', 5.5),
(23, 2, 2, 'Guard Station 3', 5.5),
(24, 2, 2, 'Guard Station 4', 5.5),
(25, 2, 2, 'Elimination Enforcement', 5.5);

-- Insert Staff Assignments (Game 3: Tug of War)
INSERT INTO staff_assignments (staff_id, game_id, round_number, role_description, hours_worked) VALUES
(4, 3, 3, 'Supervisor - Platform Safety', 6.0),
(10, 3, 3, 'Rope Setup', 4.0),
(11, 3, 3, 'Platform Monitor - Team A', 6.0),
(12, 3, 3, 'Platform Monitor - Team B', 6.0),
(26, 3, 3, 'Safety Line Check', 6.0),
(27, 3, 3, 'Cleanup Crew', 3.0);

-- Insert Staff Assignments (Game 4: Marbles)
INSERT INTO staff_assignments (staff_id, game_id, round_number, role_description, hours_worked) VALUES
(5, 4, 4, 'Supervisor - Marble Distribution', 8.0),
(13, 4, 4, 'Pairing Coordination', 7.0),
(14, 4, 4, 'Timer Management', 7.0),
(28, 4, 4, 'Zone Guard - Area 1', 7.0),
(29, 4, 4, 'Zone Guard - Area 2', 7.0),
(30, 4, 4, 'Zone Guard - Area 3', 7.0);

-- Insert Staff Assignments (Game 5: Glass Bridge)
INSERT INTO staff_assignments (staff_id, game_id, round_number, role_description, hours_worked) VALUES
(6, 5, 5, 'Supervisor - Bridge Safety', 5.0),
(15, 5, 5, 'Number Assignment', 4.0),
(16, 5, 5, 'Timer Control', 5.0),
(31, 5, 5, 'Starting Platform Guard', 5.0),
(32, 5, 5, 'End Platform Guard', 5.0);

-- =====================================================
-- 5. USEFUL VIEWS FOR QUICK QUERIES
-- =====================================================

-- View: Staff with Supervisor Names (Most common Self Join)
CREATE VIEW v_staff_hierarchy AS
SELECT 
    s1.staff_id,
    s1.staff_number,
    s1.name AS staff_name,
    s1.role AS staff_role,
    s1.salary,
    s1.status,
    s2.staff_number AS supervisor_number,
    s2.name AS supervisor_name,
    s2.role AS supervisor_role
FROM staff s1
LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id
ORDER BY 
    CASE s1.role
        WHEN 'Front Man' THEN 1
        WHEN 'Square' THEN 2
        WHEN 'Triangle' THEN 3
        WHEN 'Circle' THEN 4
    END, s1.staff_number;

-- View: Staff Assignment Summary
CREATE VIEW v_staff_assignments_summary AS
SELECT 
    s.staff_id,
    s.staff_number,
    s.name,
    s.role,
    COUNT(sa.assignment_id) AS total_assignments,
    SUM(sa.hours_worked) AS total_hours,
    AVG(sa.hours_worked) AS avg_hours_per_assignment
FROM staff s
LEFT JOIN staff_assignments sa ON s.staff_id = sa.staff_id
GROUP BY s.staff_id, s.staff_number, s.name, s.role;

-- =====================================================
-- DATABASE SETUP COMPLETE!
-- =====================================================
