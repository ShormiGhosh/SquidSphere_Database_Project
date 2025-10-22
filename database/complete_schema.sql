-- SquidSphere Database Schema - Complete Project
-- Covers ALL Lab Topics for Database Lab Project

-- ============================================
-- Lab 02: DDL - Table Creation
-- ============================================

-- 1. Players Table (Already exists, but let's document it)
CREATE TABLE IF NOT EXISTS players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    player_number VARCHAR(3) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('M', 'F', 'Other') NOT NULL,
    status ENUM('alive', 'eliminated', 'winner') DEFAULT 'alive',
    debt_amount DECIMAL(15, 2) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    alliance_group INT DEFAULT NULL,
    CONSTRAINT chk_age CHECK (age >= 18),
    CONSTRAINT chk_debt CHECK (debt_amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Games Table
CREATE TABLE IF NOT EXISTS games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    game_name VARCHAR(100) UNIQUE NOT NULL,
    game_type ENUM('individual', 'team', 'pair') NOT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard', 'extreme') NOT NULL,
    max_survivors INT NOT NULL,
    prize_pool DECIMAL(15, 2) DEFAULT 0,
    time_limit INT DEFAULT NULL, -- in minutes
    game_order INT UNIQUE NOT NULL,
    game_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'active', 'completed') DEFAULT 'pending',
    CONSTRAINT chk_max_survivors CHECK (max_survivors > 0),
    CONSTRAINT chk_prize_pool CHECK (prize_pool >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Teams Table
CREATE TABLE IF NOT EXISTS teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_leader_id INT DEFAULT NULL,
    game_id INT NOT NULL,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'eliminated', 'winner') DEFAULT 'active',
    CONSTRAINT fk_team_leader FOREIGN KEY (team_leader_id) REFERENCES players(player_id) ON DELETE SET NULL,
    CONSTRAINT fk_team_game FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Team Members Table (for many-to-many relationship)
CREATE TABLE IF NOT EXISTS team_members (
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    player_id INT NOT NULL,
    join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM('leader', 'member', 'supporter') DEFAULT 'member',
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_player FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE,
    CONSTRAINT unique_team_player UNIQUE(team_id, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Game Participation Table
CREATE TABLE IF NOT EXISTS game_participation (
    participation_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT NOT NULL,
    team_id INT DEFAULT NULL,
    result ENUM('survived', 'eliminated', 'winner') DEFAULT NULL,
    score DECIMAL(10, 2) DEFAULT 0,
    completion_time INT DEFAULT NULL, -- in seconds
    participation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gp_player FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE,
    CONSTRAINT fk_gp_game FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE CASCADE,
    CONSTRAINT fk_gp_team FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE SET NULL,
    CONSTRAINT unique_player_game UNIQUE(player_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Staff Table (for self-join demonstration)
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role ENUM('Front Man', 'Manager', 'Guard', 'Worker', 'VIP') NOT NULL,
    manager_id INT DEFAULT NULL, -- Self-referencing FK for hierarchy
    hire_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    salary DECIMAL(12, 2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    CONSTRAINT fk_staff_manager FOREIGN KEY (manager_id) REFERENCES staff(staff_id) ON DELETE SET NULL,
    CONSTRAINT chk_salary CHECK (salary > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Voting Table (for subqueries)
CREATE TABLE IF NOT EXISTS votes (
    vote_id INT AUTO_INCREMENT PRIMARY KEY,
    voter_id INT NOT NULL,
    vote_choice ENUM('continue', 'stop') NOT NULL,
    game_id INT NOT NULL,
    vote_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_player FOREIGN KEY (voter_id) REFERENCES players(player_id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_game FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE CASCADE,
    CONSTRAINT unique_player_vote UNIQUE(voter_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Prize Distribution Table
CREATE TABLE IF NOT EXISTS prize_distribution (
    distribution_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    distribution_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    CONSTRAINT fk_pd_player FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE,
    CONSTRAINT fk_pd_game FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE CASCADE,
    CONSTRAINT chk_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Lab 02: DDL - ALTER Statements
-- ============================================

-- Add index for better performance
CREATE INDEX idx_player_status ON players(status);
CREATE INDEX idx_player_nationality ON players(nationality);
CREATE INDEX idx_game_status ON games(status);
CREATE INDEX idx_participation_result ON game_participation(result);

-- Add a column to players table (example ALTER)
ALTER TABLE players ADD COLUMN last_active DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add a column to games table
ALTER TABLE games ADD COLUMN description TEXT DEFAULT NULL;

-- ============================================
-- Lab 05: VIEWS (4-5 Views Required)
-- ============================================

-- View 1: Active Players Summary
CREATE OR REPLACE VIEW vw_active_players AS
SELECT 
    p.player_id,
    p.player_number,
    p.name,
    p.age,
    p.gender,
    p.nationality,
    p.debt_amount,
    p.alliance_group,
    COUNT(gp.participation_id) as games_played,
    SUM(CASE WHEN gp.result = 'survived' THEN 1 ELSE 0 END) as games_survived
FROM players p
LEFT JOIN game_participation gp ON p.player_id = gp.player_id
WHERE p.status = 'alive'
GROUP BY p.player_id;

-- View 2: Game Statistics
CREATE OR REPLACE VIEW vw_game_statistics AS
SELECT 
    g.game_id,
    g.game_name,
    g.game_type,
    g.difficulty_level,
    g.prize_pool,
    COUNT(gp.participation_id) as total_participants,
    SUM(CASE WHEN gp.result = 'eliminated' THEN 1 ELSE 0 END) as total_eliminations,
    SUM(CASE WHEN gp.result = 'survived' THEN 1 ELSE 0 END) as total_survivors,
    AVG(gp.score) as avg_score,
    AVG(gp.completion_time) as avg_completion_time
FROM games g
LEFT JOIN game_participation gp ON g.game_id = gp.game_id
GROUP BY g.game_id;

-- View 3: Team Performance
CREATE OR REPLACE VIEW vw_team_performance AS
SELECT 
    t.team_id,
    t.team_name,
    g.game_name,
    COUNT(tm.player_id) as team_size,
    t.status as team_status,
    p.name as team_leader
FROM teams t
JOIN games g ON t.game_id = g.game_id
LEFT JOIN team_members tm ON t.team_id = tm.team_id
LEFT JOIN players p ON t.team_leader_id = p.player_id
GROUP BY t.team_id;

-- View 4: Player Rankings
CREATE OR REPLACE VIEW vw_player_rankings AS
SELECT 
    p.player_id,
    p.player_number,
    p.name,
    p.status,
    COUNT(gp.participation_id) as games_played,
    SUM(gp.score) as total_score,
    AVG(gp.score) as avg_score,
    RANK() OVER (ORDER BY SUM(gp.score) DESC) as score_rank
FROM players p
LEFT JOIN game_participation gp ON p.player_id = gp.player_id
GROUP BY p.player_id
ORDER BY total_score DESC;

-- View 5: Staff Hierarchy
CREATE OR REPLACE VIEW vw_staff_hierarchy AS
SELECT 
    s.staff_id,
    s.name as staff_name,
    s.role,
    m.name as manager_name,
    m.role as manager_role,
    s.salary
FROM staff s
LEFT JOIN staff m ON s.manager_id = m.staff_id;

-- ============================================
-- Insert Sample Data for Testing
-- ============================================

-- Insert sample games
INSERT INTO games (game_name, game_type, difficulty_level, max_survivors, prize_pool, game_order, description) VALUES
('Red Light Green Light', 'individual', 'easy', 255, 45600000000, 1, 'Stop when the doll turns around'),
('Honeycomb', 'individual', 'medium', 187, 45600000000, 2, 'Cut the shape without breaking it'),
('Tug of War', 'team', 'hard', 93, 45600000000, 3, 'Pull the opposing team over'),
('Marbles', 'pair', 'medium', 46, 45600000000, 4, 'Win all your partner marbles'),
('Glass Bridge', 'individual', 'extreme', 16, 45600000000, 5, 'Choose the right glass panel'),
('Squid Game', 'individual', 'extreme', 1, 45600000000, 6, 'Final game - one winner')
ON DUPLICATE KEY UPDATE game_name=game_name;

-- Insert sample staff (demonstrating hierarchy)
INSERT INTO staff (name, role, manager_id, salary) VALUES
('Front Man', 'Front Man', NULL, 10000000),
('Il-nam', 'VIP', NULL, 5000000),
('Manager 1', 'Manager', 1, 500000),
('Manager 2', 'Manager', 1, 500000),
('Guard Squad Leader', 'Guard', 3, 100000),
('Guard 1', 'Guard', 5, 50000),
('Guard 2', 'Guard', 5, 50000),
('Worker 1', 'Worker', 4, 20000),
('Worker 2', 'Worker', 4, 20000)
ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- Useful Queries for Project Demonstration
-- ============================================

-- These queries will be implemented in PHP for the dashboard
-- Saved here for documentation purposes

/*
-- Lab 04: Aggregates
SELECT 
    nationality, 
    COUNT(*) as player_count,
    AVG(age) as avg_age,
    SUM(debt_amount) as total_debt,
    MIN(debt_amount) as min_debt,
    MAX(debt_amount) as max_debt
FROM players
GROUP BY nationality
HAVING player_count > 5;

-- Lab 05: Subqueries - Find players with debt above average
SELECT * FROM players 
WHERE debt_amount > (SELECT AVG(debt_amount) FROM players);

-- Lab 05: Set Operations - UNION example
SELECT player_id, name, 'High Debt' as category FROM players WHERE debt_amount > 5000000
UNION
SELECT player_id, name, 'Senior' as category FROM players WHERE age > 60;

-- Lab 06: INNER JOIN - Players and their game results
SELECT p.name, g.game_name, gp.result, gp.score
FROM players p
INNER JOIN game_participation gp ON p.player_id = gp.player_id
INNER JOIN games g ON gp.game_id = g.game_id;

-- Lab 06: LEFT JOIN - All players and their games (including those who haven't played)
SELECT p.name, g.game_name, gp.result
FROM players p
LEFT JOIN game_participation gp ON p.player_id = gp.player_id
LEFT JOIN games g ON gp.game_id = g.game_id;

-- Lab 06: SELF JOIN - Staff hierarchy
SELECT s.name as staff_name, m.name as manager_name
FROM staff s
LEFT JOIN staff m ON s.manager_id = m.staff_id;

-- Lab 06: CROSS JOIN - All possible player-game combinations
SELECT p.player_number, p.name, g.game_name
FROM players p
CROSS JOIN games g
LIMIT 100;
*/
