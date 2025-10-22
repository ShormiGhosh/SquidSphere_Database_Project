-- Create Players Table for SquidSphere Database

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

-- Index for faster queries
CREATE INDEX idx_player_number ON players(player_number);
CREATE INDEX idx_status ON players(status);
CREATE INDEX idx_alliance_group ON players(alliance_group);
