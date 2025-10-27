
CREATE TABLE IF NOT EXISTS teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_leader_id INT DEFAULT NULL,
    game_id INT NOT NULL,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'eliminated', 'winner') DEFAULT 'active',
    
    -- Foreign keys
    CONSTRAINT fk_team_leader 
        FOREIGN KEY (team_leader_id) 
        REFERENCES players(player_id) 
        ON DELETE SET NULL,
    
    CONSTRAINT fk_team_game 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE CASCADE,
        
    -- Each team name should be unique per game
    CONSTRAINT uq_team_game 
        UNIQUE (team_name, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes
CREATE INDEX idx_team_leader ON teams(team_leader_id);
CREATE INDEX idx_team_game ON teams(game_id);
CREATE INDEX idx_team_status ON teams(status);

-- =====================================================
-- 2. TEAM MEMBERS TABLE (Many-to-Many: Teams-Players)
-- =====================================================
CREATE TABLE IF NOT EXISTS team_members (
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    player_id INT NOT NULL,
    join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM('leader', 'member', 'supporter') DEFAULT 'member',
    
    -- Foreign keys
    CONSTRAINT fk_tm_team 
        FOREIGN KEY (team_id) 
        REFERENCES teams(team_id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_tm_player 
        FOREIGN KEY (player_id) 
        REFERENCES players(player_id) 
        ON DELETE CASCADE,
    
    -- Each player can only be in one team per game
    CONSTRAINT uq_team_player 
        UNIQUE(team_id, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes
CREATE INDEX idx_tm_team ON team_members(team_id);
CREATE INDEX idx_tm_player ON team_members(player_id);
CREATE INDEX idx_tm_role ON team_members(role);

-- =====================================================
-- 4. PRIZE DISTRIBUTION TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS prize_distribution (
    distribution_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT DEFAULT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    distribution_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    description VARCHAR(255) DEFAULT NULL,
    
    -- Foreign keys
    CONSTRAINT fk_pd_player 
        FOREIGN KEY (player_id) 
        REFERENCES players(player_id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_pd_game 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE SET NULL,
    
    -- Amount must be positive
    CONSTRAINT chk_pd_amount 
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes
CREATE INDEX idx_pd_player ON prize_distribution(player_id);
CREATE INDEX idx_pd_game ON prize_distribution(game_id);
CREATE INDEX idx_pd_status ON prize_distribution(payment_status);
CREATE INDEX idx_pd_date ON prize_distribution(distribution_date);

-- =====================================================
-- 5. GAME PARTICIPATION TABLE
-- Tracks player performance across all games
-- =====================================================
CREATE TABLE IF NOT EXISTS game_participation (
    participation_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT NOT NULL,
    team_id INT DEFAULT NULL,
    result ENUM('survived', 'eliminated', 'winner') DEFAULT NULL,
    score DECIMAL(10, 2) DEFAULT 0,
    completion_time INT DEFAULT NULL, -- in seconds
    participation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    
    -- Foreign keys
    CONSTRAINT fk_gp_player 
        FOREIGN KEY (player_id) 
        REFERENCES players(player_id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_gp_game 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_gp_team 
        FOREIGN KEY (team_id) 
        REFERENCES teams(team_id) 
        ON DELETE SET NULL,
    
    -- Each player can only participate once per game
    CONSTRAINT uq_player_game 
        UNIQUE(player_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create indexes
CREATE INDEX idx_gp_player ON game_participation(player_id);
CREATE INDEX idx_gp_game ON game_participation(game_id);
CREATE INDEX idx_gp_team ON game_participation(team_id);
CREATE INDEX idx_gp_result ON game_participation(result);
CREATE INDEX idx_gp_date ON game_participation(participation_date);

-- =====================================================
-- CREATE VIEWS FOR BETTER QUERYING
-- =====================================================

-- View: Team Performance Summary
CREATE OR REPLACE VIEW vw_team_performance AS
SELECT 
    t.team_id,
    t.team_name,
    g.game_name,
    COUNT(tm.player_id) as team_size,
    t.status as team_status,
    p.name as team_leader_name,
    t.created_date
FROM teams t
JOIN games g ON t.game_id = g.game_id
LEFT JOIN team_members tm ON t.team_id = tm.team_id
LEFT JOIN players p ON t.team_leader_id = p.player_id
GROUP BY t.team_id, t.team_name, g.game_name, t.status, p.name, t.created_date;

-- View: Player Game History
CREATE OR REPLACE VIEW vw_player_game_history AS
SELECT 
    p.player_id,
    p.player_number,
    p.name,
    p.status as current_status,
    COUNT(gp.participation_id) as games_played,
    SUM(CASE WHEN gp.result = 'survived' THEN 1 ELSE 0 END) as games_survived,
    SUM(CASE WHEN gp.result = 'eliminated' THEN 1 ELSE 0 END) as games_eliminated,
    SUM(gp.score) as total_score,
    AVG(gp.score) as avg_score
FROM players p
LEFT JOIN game_participation gp ON p.player_id = gp.player_id
GROUP BY p.player_id, p.player_number, p.name, p.status;

-- View: Game Statistics
CREATE OR REPLACE VIEW vw_game_statistics AS
SELECT 
    g.game_id,
    g.game_name,
    g.game_type,
    g.max_players,
    COUNT(gp.participation_id) as total_participants,
    SUM(CASE WHEN gp.result = 'eliminated' THEN 1 ELSE 0 END) as total_eliminations,
    SUM(CASE WHEN gp.result = 'survived' THEN 1 ELSE 0 END) as total_survivors,
    AVG(gp.score) as avg_score,
    AVG(gp.completion_time) as avg_completion_time
FROM games g
LEFT JOIN game_participation gp ON g.game_id = gp.game_id
GROUP BY g.game_id, g.game_name, g.game_type, g.max_players;

-- View: Prize Distribution Summary
CREATE OR REPLACE VIEW vw_prize_summary AS
SELECT 
    p.player_id,
    p.player_number,
    p.name,
    COUNT(pd.distribution_id) as prize_count,
    SUM(pd.amount) as total_prize_money,
    SUM(CASE WHEN pd.payment_status = 'paid' THEN pd.amount ELSE 0 END) as paid_amount,
    SUM(CASE WHEN pd.payment_status = 'pending' THEN pd.amount ELSE 0 END) as pending_amount
FROM players p
LEFT JOIN prize_distribution pd ON p.player_id = pd.player_id
GROUP BY p.player_id, p.player_number, p.name;

-- =====================================================
-- SAMPLE DATA (Optional - Uncomment to use)
-- =====================================================

/*
-- Sample Teams for Tug of War (Game ID 3)
INSERT INTO teams (team_name, team_leader_id, game_id, status) VALUES
('Red Team', 1, 3, 'active'),
('Blue Team', 50, 3, 'active'),
('Green Team', 100, 3, 'eliminated'),
('Yellow Team', 150, 3, 'eliminated');

-- Sample Team Members
INSERT INTO team_members (team_id, player_id, role) VALUES
(1, 1, 'leader'),
(1, 2, 'member'),
(1, 3, 'member'),
(2, 50, 'leader'),
(2, 51, 'member'),
(2, 52, 'member');

-- Sample Game Participation
INSERT INTO game_participation (player_id, game_id, result, score) VALUES
(1, 1, 'survived', 100),
(1, 2, 'survived', 95),
(2, 1, 'eliminated', 45),
(3, 1, 'survived', 88);

-- Sample Prize Distribution
INSERT INTO prize_distribution (player_id, game_id, amount, payment_status, description) VALUES
(1, 1, 10000000, 'pending', 'Round 1 survival bonus'),
(1, 2, 15000000, 'pending', 'Round 2 survival bonus');
*/

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check if tables were created successfully
SELECT 'Tables created successfully!' as status;

-- Show table structure
SHOW TABLES LIKE '%team%';
SHOW TABLES LIKE '%prize%';
SHOW TABLES LIKE '%participation%';

-- Show views
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';
