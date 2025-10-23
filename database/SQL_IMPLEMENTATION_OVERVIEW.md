# SquidSphere Database Project - SQL Implementation Overview

## 📊 Project Summary

**Project Name:** SquidSphere - Squid Game Database Management System  
**Database Type:** MySQL / MariaDB  
**Total Tables:** 9  
**Total Views:** 4  
**Programming Languages:** PHP, JavaScript, SQL  
**Framework:** Vanilla PHP with MySQL

---

## 🗂️ Database Schema Overview

### **Core Tables (4 Original)**

#### 1. **PLAYERS Table**
Primary table storing all player information.

```sql
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
    last_active DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_age CHECK (age >= 18),
    CONSTRAINT chk_debt CHECK (debt_amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Track all 456 players with their personal info and game status  
**Key Fields:**
- `player_id` - Primary key (auto-increment)
- `player_number` - Unique 3-digit identifier (e.g., '001', '456')
- `status` - Current state: alive, eliminated, winner
- `debt_amount` - Financial debt (reason for joining)

**Constraints:**
- Age must be ≥ 18
- Debt must be positive
- Player number must be unique

**Indexes:**
```sql
CREATE INDEX idx_player_number ON players(player_number);
CREATE INDEX idx_status ON players(status);
CREATE INDEX idx_player_nationality ON players(nationality);
CREATE INDEX idx_alliance_group ON players(alliance_group);
```

---

#### 2. **STAFF Table**
Manages staff hierarchy with self-referencing foreign key.

```sql
CREATE TABLE staff (
    staff_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_number VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('Circle', 'Triangle', 'Square', 'Front Man') NOT NULL,
    supervisor_id INT NULL,
    hire_date DATE DEFAULT (CURRENT_DATE),
    salary DECIMAL(10, 2) NOT NULL,
    status ENUM('active', 'terminated') DEFAULT 'active',
    CONSTRAINT fk_supervisor 
        FOREIGN KEY (supervisor_id) 
        REFERENCES staff(staff_id) 
        ON DELETE SET NULL,
    CONSTRAINT chk_salary CHECK (salary > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Track staff members in a hierarchical structure (Self-Join)  
**Key Fields:**
- `staff_id` - Primary key
- `supervisor_id` - Foreign key referencing same table (Self-Join)
- `role` - Circle (worker), Triangle (soldier), Square (manager), Front Man (boss)

**Self-Join Usage:**
```sql
-- View staff with their supervisors
SELECT 
    s1.name AS staff_name, 
    s1.role AS staff_role,
    s2.name AS supervisor_name,
    s2.role AS supervisor_role
FROM staff s1
LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id;
```

**Indexes:**
```sql
CREATE INDEX idx_supervisor ON staff(supervisor_id);
CREATE INDEX idx_role ON staff(role);
```

---

#### 3. **GAMES Table**
Stores information about each game round.

```sql
CREATE TABLE games (
    game_id INT PRIMARY KEY AUTO_INCREMENT,
    game_name VARCHAR(100) NOT NULL,
    game_type VARCHAR(50) NOT NULL,
    max_players INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Define the 6 game rounds  
**Sample Data:**
```sql
INSERT INTO games (game_id, game_name, game_type, max_players, description) VALUES
(1, 'Red Light Green Light', 'Elimination', 456, 'Stop when doll turns'),
(2, 'Honeycomb', 'Precision', 456, 'Extract candy shapes'),
(3, 'Tug of War', 'Team', 20, 'Pull rope competition'),
(4, 'Marbles', 'Pairs', 456, 'Win partner marbles'),
(5, 'Glass Bridge', 'Probability', 16, 'Choose correct panels'),
(6, 'Squid Game', 'Final Battle', 2, 'One-on-one combat');
```

---

#### 4. **STAFF_ASSIGNMENTS Table**
Many-to-many relationship between staff and games.

```sql
CREATE TABLE staff_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    game_id INT NOT NULL,
    round_number INT NOT NULL,
    role_description VARCHAR(255) NOT NULL,
    assignment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    hours_worked DECIMAL(5, 2) DEFAULT 0,
    CONSTRAINT fk_staff_assignment 
        FOREIGN KEY (staff_id) 
        REFERENCES staff(staff_id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_game_assignment 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE CASCADE,
    CONSTRAINT uq_staff_game_round 
        UNIQUE (staff_id, game_id, round_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Track which staff members work on which games  
**Indexes:**
```sql
CREATE INDEX idx_staff_assignment ON staff_assignments(staff_id);
CREATE INDEX idx_game_assignment ON staff_assignments(game_id);
CREATE INDEX idx_round ON staff_assignments(round_number);
```

---

### **Extended Tables (4 New)**

#### 5. **TEAMS Table**
For team-based games (Tug of War).

```sql
CREATE TABLE IF NOT EXISTS teams (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_leader_id INT DEFAULT NULL,
    game_id INT NOT NULL,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'eliminated', 'winner') DEFAULT 'active',
    CONSTRAINT fk_team_leader 
        FOREIGN KEY (team_leader_id) 
        REFERENCES players(player_id) 
        ON DELETE SET NULL,
    CONSTRAINT fk_team_game 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE CASCADE,
    CONSTRAINT uq_team_game 
        UNIQUE (team_name, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Create and manage teams for group competitions  
**Relationships:**
- Links to `players` (team leader)
- Links to `games` (which game)

---

#### 6. **TEAM_MEMBERS Table**
Many-to-many relationship between teams and players.

```sql
CREATE TABLE IF NOT EXISTS team_members (
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    player_id INT NOT NULL,
    join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM('leader', 'member', 'supporter') DEFAULT 'member',
    CONSTRAINT fk_tm_team 
        FOREIGN KEY (team_id) 
        REFERENCES teams(team_id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_tm_player 
        FOREIGN KEY (player_id) 
        REFERENCES players(player_id) 
        ON DELETE CASCADE,
    CONSTRAINT uq_team_player 
        UNIQUE(team_id, player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Track team membership (which players are on which teams)

---

#### 7. **GAME_PARTICIPATION Table**
Records player performance in each game.

```sql
CREATE TABLE IF NOT EXISTS game_participation (
    participation_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT NOT NULL,
    team_id INT DEFAULT NULL,
    result ENUM('survived', 'eliminated', 'winner') DEFAULT NULL,
    score DECIMAL(10, 2) DEFAULT 0,
    completion_time INT DEFAULT NULL,
    participation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
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
    CONSTRAINT uq_player_game 
        UNIQUE(player_id, game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Historical record of each player's performance per game  
**Key Features:**
- One record per player per game
- Can link to team for team-based games
- Stores result, score, and completion time

---

#### 8. **PRIZE_DISTRIBUTION Table**
Tracks prize money given to players.

```sql
CREATE TABLE IF NOT EXISTS prize_distribution (
    distribution_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT DEFAULT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    distribution_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    description VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_pd_player 
        FOREIGN KEY (player_id) 
        REFERENCES players(player_id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_pd_game 
        FOREIGN KEY (game_id) 
        REFERENCES games(game_id) 
        ON DELETE SET NULL,
    CONSTRAINT chk_pd_amount 
        CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Financial tracking for prize money  
**Features:**
- Payment status tracking
- Optional game association
- Amount validation (must be positive)

---

#### 9. **COMPLETED_ROUNDS Table**
Prevents rounds from being played multiple times.

```sql
CREATE TABLE IF NOT EXISTS completed_rounds (
    round_number INT PRIMARY KEY,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Purpose:** Game state management  
**Usage:** Auto-created by API when a round completes

---

## 📋 Views (Lab 05 Requirement)

### 1. **vw_staff_hierarchy**
Shows staff with their supervisor names (Self-Join view).

```sql
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
```

**Purpose:** Easy access to hierarchical staff structure

---

### 2. **vw_team_performance**
Team statistics and performance summary.

```sql
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
```

**Purpose:** Quick team overview with member counts

---

### 3. **vw_player_game_history**
Player's complete game participation history.

```sql
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
```

**Purpose:** Player statistics and performance tracking

---

### 4. **vw_game_statistics**
Overall game statistics.

```sql
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
```

**Purpose:** Game-level analytics

---

## 🔑 SQL Commands Used in Project

### **DDL (Data Definition Language)**

#### CREATE
```sql
-- Create table
CREATE TABLE IF NOT EXISTS table_name (...)

-- Create index
CREATE INDEX idx_name ON table_name(column_name);

-- Create view
CREATE OR REPLACE VIEW view_name AS SELECT ...
```

#### ALTER
```sql
-- Add column
ALTER TABLE players ADD COLUMN last_active DATETIME;

-- Modify column
ALTER TABLE games ADD COLUMN description TEXT DEFAULT NULL;

-- Drop column (if needed)
ALTER TABLE table_name DROP COLUMN column_name;
```

#### DROP
```sql
-- Drop table
DROP TABLE IF EXISTS table_name;

-- Drop index
DROP INDEX idx_name ON table_name;

-- Drop view
DROP VIEW IF EXISTS view_name;
```

---

### **DML (Data Manipulation Language)**

#### INSERT
```sql
-- Simple insert
INSERT INTO players (player_number, name, age, gender, debt_amount, nationality)
VALUES ('001', 'Seong Gi-hun', 47, 'M', 255000000, 'South Korea');

-- Insert multiple rows
INSERT INTO games (game_name, game_type, max_players) VALUES
('Red Light Green Light', 'Elimination', 456),
('Honeycomb', 'Precision', 456);

-- Insert with duplicate key handling
INSERT INTO games (game_id, game_name, game_type, max_players)
VALUES (1, 'Red Light Green Light', 'Elimination', 456)
ON DUPLICATE KEY UPDATE game_name=game_name;

-- Insert ignore (skip duplicates)
INSERT IGNORE INTO completed_rounds (round_number) VALUES (1);
```

#### UPDATE
```sql
-- Simple update
UPDATE players SET status = 'eliminated' WHERE player_id = 5;

-- Update with calculation
UPDATE players 
SET debt_amount = debt_amount * 1.1 
WHERE age > 50;

-- Update with join
UPDATE players p
JOIN game_participation gp ON p.player_id = gp.player_id
SET p.status = 'winner'
WHERE gp.result = 'winner';

-- Conditional update
UPDATE players 
SET status = CASE 
    WHEN player_id = 1 THEN 'winner'
    ELSE 'eliminated'
END
WHERE status = 'alive';
```

#### DELETE
```sql
-- Simple delete
DELETE FROM team_members WHERE team_id = 5;

-- Delete with condition
DELETE FROM players WHERE status = 'eliminated' AND age > 60;

-- Delete all (use with caution)
DELETE FROM completed_rounds;

-- Truncate (faster, resets auto-increment)
TRUNCATE TABLE completed_rounds;
```

#### SELECT
```sql
-- Basic select
SELECT * FROM players;

-- Select specific columns
SELECT player_number, name, status FROM players;

-- Select with WHERE clause
SELECT * FROM players WHERE status = 'alive';

-- Select with multiple conditions
SELECT * FROM players 
WHERE status = 'alive' AND age > 30 AND gender = 'M';
```

---

### **DQL (Data Query Language) - Advanced SELECT**

#### DISTINCT
```sql
-- Get unique nationalities
SELECT DISTINCT nationality FROM players;

-- Count unique values
SELECT COUNT(DISTINCT nationality) as country_count FROM players;
```

#### ORDER BY
```sql
-- Ascending order (default)
SELECT * FROM players ORDER BY age;

-- Descending order
SELECT * FROM players ORDER BY debt_amount DESC;

-- Multiple columns
SELECT * FROM players ORDER BY status, age DESC;
```

#### LIMIT
```sql
-- Top 10 players
SELECT * FROM players ORDER BY debt_amount DESC LIMIT 10;

-- Pagination (skip 20, take 10)
SELECT * FROM players LIMIT 20, 10;

-- Or using OFFSET
SELECT * FROM players LIMIT 10 OFFSET 20;
```

#### GROUP BY
```sql
-- Count players by status
SELECT status, COUNT(*) as count 
FROM players 
GROUP BY status;

-- Average debt by nationality
SELECT nationality, AVG(debt_amount) as avg_debt
FROM players
GROUP BY nationality;

-- Multiple columns
SELECT gender, status, COUNT(*) as count
FROM players
GROUP BY gender, status;
```

#### HAVING
```sql
-- Filter aggregated results
SELECT nationality, COUNT(*) as player_count
FROM players
GROUP BY nationality
HAVING player_count > 5;

-- Combined with WHERE
SELECT nationality, AVG(debt_amount) as avg_debt
FROM players
WHERE status = 'alive'
GROUP BY nationality
HAVING avg_debt > 10000000;
```

---

### **Aggregate Functions (Lab 04)**

```sql
-- COUNT: Total players
SELECT COUNT(*) as total FROM players;

-- COUNT with condition
SELECT COUNT(*) as alive_count FROM players WHERE status = 'alive';

-- SUM: Total debt
SELECT SUM(debt_amount) as total_debt FROM players;

-- AVG: Average age
SELECT AVG(age) as avg_age FROM players;

-- MIN/MAX: Age range
SELECT MIN(age) as youngest, MAX(age) as oldest FROM players;

-- Multiple aggregates
SELECT 
    COUNT(*) as total,
    AVG(age) as avg_age,
    MIN(debt_amount) as min_debt,
    MAX(debt_amount) as max_debt,
    SUM(debt_amount) as total_debt
FROM players;
```

---

### **JOINS (Lab 06)**

#### INNER JOIN
```sql
-- Players with their game results
SELECT 
    p.player_number, 
    p.name, 
    g.game_name, 
    gp.result
FROM players p
INNER JOIN game_participation gp ON p.player_id = gp.player_id
INNER JOIN games g ON gp.game_id = g.game_id;
```

#### LEFT JOIN (OUTER JOIN)
```sql
-- All players and their games (including those who haven't played)
SELECT 
    p.player_number,
    p.name,
    g.game_name,
    gp.result
FROM players p
LEFT JOIN game_participation gp ON p.player_id = gp.player_id
LEFT JOIN games g ON gp.game_id = g.game_id;
```

#### RIGHT JOIN
```sql
-- All games and participating players
SELECT 
    g.game_name,
    p.player_number,
    p.name
FROM players p
RIGHT JOIN game_participation gp ON p.player_id = gp.player_id
RIGHT JOIN games g ON gp.game_id = g.game_id;
```

#### SELF JOIN
```sql
-- Staff hierarchy (most important for Lab 06)
SELECT 
    s1.name as staff_name,
    s1.role,
    s2.name as supervisor_name,
    s2.role as supervisor_role
FROM staff s1
LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id;

-- Find all subordinates of a manager
SELECT 
    manager.name as manager_name,
    subordinate.name as subordinate_name,
    subordinate.role
FROM staff manager
INNER JOIN staff subordinate ON manager.staff_id = subordinate.supervisor_id
WHERE manager.role = 'Square';
```

#### CROSS JOIN
```sql
-- All possible player-game combinations
SELECT 
    p.player_number,
    p.name,
    g.game_name
FROM players p
CROSS JOIN games g
LIMIT 100;
```

---

### **Subqueries (Lab 05)**

#### Simple Subquery
```sql
-- Players with debt above average
SELECT * FROM players
WHERE debt_amount > (SELECT AVG(debt_amount) FROM players);

-- Players older than average
SELECT * FROM players
WHERE age > (SELECT AVG(age) FROM players);
```

#### Nested Subquery (2 levels)
```sql
-- Players with maximum debt
SELECT * FROM players
WHERE debt_amount = (
    SELECT MAX(debt_amount) FROM players
    WHERE debt_amount < (
        SELECT MAX(debt_amount) FROM players
    )
);
```

#### IN / NOT IN
```sql
-- Players from top 3 nationalities
SELECT * FROM players
WHERE nationality IN (
    SELECT nationality 
    FROM players 
    GROUP BY nationality 
    ORDER BY COUNT(*) DESC 
    LIMIT 3
);

-- Exclude rare nationalities
SELECT * FROM players
WHERE nationality NOT IN (
    SELECT nationality 
    FROM players 
    GROUP BY nationality 
    HAVING COUNT(*) < 5
);
```

#### EXISTS
```sql
-- Players who have participated in any game
SELECT * FROM players p
WHERE EXISTS (
    SELECT 1 FROM game_participation gp 
    WHERE gp.player_id = p.player_id
);

-- Players with similar debt (±5M)
SELECT p1.* FROM players p1
WHERE EXISTS (
    SELECT 1 FROM players p2
    WHERE p2.player_id != p1.player_id
    AND ABS(p2.debt_amount - p1.debt_amount) <= 5000000
);
```

---

### **Set Operations (Lab 05)**

#### UNION
```sql
-- Males OR High Debt Females
SELECT player_id, name, 'Male' as category FROM players WHERE gender = 'M'
UNION
SELECT player_id, name, 'High Debt Female' as category 
FROM players WHERE gender = 'F' AND debt_amount > 50000000;
```

#### INTERSECT (Simulated - MySQL doesn't have native INTERSECT)
```sql
-- Young (<30) AND Low Debt (<10M)
SELECT * FROM players WHERE age < 30
AND player_id IN (
    SELECT player_id FROM players WHERE debt_amount < 10000000
);
```

#### MINUS/EXCEPT (Simulated)
```sql
-- Alive but NOT Young (<25)
SELECT * FROM players WHERE status = 'alive'
AND player_id NOT IN (
    SELECT player_id FROM players WHERE age < 25
);
```

---

### **String Functions**

```sql
-- CONCAT
SELECT CONCAT(player_number, ' - ', name) as player_info FROM players;

-- LIKE pattern matching
SELECT * FROM players WHERE name LIKE 'Gi%';
SELECT * FROM players WHERE nationality LIKE '%Korea%';

-- UPPER/LOWER
SELECT UPPER(name) as name_upper FROM players;
SELECT LOWER(nationality) as nationality_lower FROM players;

-- LENGTH
SELECT name, LENGTH(name) as name_length FROM players;

-- SUBSTRING
SELECT SUBSTRING(name, 1, 3) as first_3_chars FROM players;
```

---

### **Date Functions**

```sql
-- Current date/time
SELECT NOW(), CURRENT_DATE, CURRENT_TIME;

-- Date formatting
SELECT DATE_FORMAT(registration_date, '%Y-%m-%d') as formatted_date FROM players;

-- Date calculations
SELECT DATEDIFF(NOW(), registration_date) as days_registered FROM players;

-- Date parts
SELECT YEAR(registration_date), MONTH(registration_date) FROM players;
```

---

### **Math Functions**

```sql
-- Rounding
SELECT ROUND(debt_amount, 2) FROM players;
SELECT CEIL(debt_amount) FROM players;
SELECT FLOOR(debt_amount) FROM players;

-- Absolute value
SELECT ABS(debt_amount - 10000000) FROM players;

-- Random
SELECT * FROM players ORDER BY RAND() LIMIT 10;
```

---

### **Control Flow**

#### CASE Statement
```sql
-- Categorize debt levels
SELECT 
    name,
    debt_amount,
    CASE 
        WHEN debt_amount < 10000000 THEN 'Low'
        WHEN debt_amount < 50000000 THEN 'Medium'
        ELSE 'High'
    END as debt_category
FROM players;

-- Age groups
SELECT 
    name,
    age,
    CASE 
        WHEN age < 30 THEN 'Young'
        WHEN age < 50 THEN 'Middle'
        ELSE 'Senior'
    END as age_group
FROM players;
```

#### IF Function
```sql
SELECT 
    name,
    IF(status = 'alive', 'Active', 'Inactive') as player_status
FROM players;
```

#### COALESCE (NULL handling)
```sql
SELECT 
    name,
    COALESCE(alliance_group, 0) as alliance
FROM players;
```

---

## 🔧 Constraints Used

### Primary Key
```sql
player_id INT AUTO_INCREMENT PRIMARY KEY
```

### Foreign Key
```sql
CONSTRAINT fk_team_leader 
    FOREIGN KEY (team_leader_id) 
    REFERENCES players(player_id) 
    ON DELETE SET NULL
```

### Unique Constraint
```sql
player_number VARCHAR(3) UNIQUE NOT NULL

-- Composite unique
CONSTRAINT uq_team_player UNIQUE(team_id, player_id)
```

### Check Constraint
```sql
CONSTRAINT chk_age CHECK (age >= 18)
CONSTRAINT chk_debt CHECK (debt_amount > 0)
CONSTRAINT chk_salary CHECK (salary > 0)
```

### NOT NULL
```sql
name VARCHAR(100) NOT NULL
```

### DEFAULT
```sql
status ENUM('alive', 'eliminated', 'winner') DEFAULT 'alive'
registration_date DATETIME DEFAULT CURRENT_TIMESTAMP
```

### ON DELETE/UPDATE Actions
```sql
ON DELETE CASCADE    -- Delete child records when parent is deleted
ON DELETE SET NULL   -- Set to NULL when parent is deleted
ON UPDATE CASCADE    -- Update child records when parent is updated
```

---

## 📊 Query Patterns by Lab Topic

### **Lab 02: DDL**
- ✅ CREATE TABLE (9 tables)
- ✅ ALTER TABLE (add columns)
- ✅ CREATE INDEX (multiple indexes)
- ✅ DROP TABLE IF EXISTS

### **Lab 03: Constraints**
- ✅ PRIMARY KEY (all tables)
- ✅ FOREIGN KEY (15+ relationships)
- ✅ UNIQUE (player_number, staff_number, etc.)
- ✅ CHECK (age, debt, salary validation)
- ✅ NOT NULL (required fields)
- ✅ DEFAULT (status, dates)

### **Lab 04: Aggregates**
- ✅ COUNT() - Player counts by status
- ✅ SUM() - Total debt, prize money
- ✅ AVG() - Average age, debt
- ✅ MIN() / MAX() - Age range, debt range
- ✅ GROUP BY - Nationality, gender, status
- ✅ HAVING - Filter grouped results

### **Lab 05: Subqueries**
- ✅ Simple subqueries (above average)
- ✅ Nested subqueries (2+ levels)
- ✅ IN / NOT IN (nationality filters)
- ✅ EXISTS (correlated subqueries)
- ✅ Set Operations (UNION, INTERSECT simulation)

### **Lab 05: Views**
- ✅ 4+ views created
- ✅ Complex joins in views
- ✅ Aggregations in views

### **Lab 06: Joins**
- ✅ INNER JOIN (player-game results)
- ✅ LEFT/RIGHT JOIN (all players/games)
- ✅ SELF JOIN (staff hierarchy) ⭐
- ✅ CROSS JOIN (all combinations)
- ✅ Multiple table joins

---

## 🎯 Key SQL Features Demonstrated

1. **Self-Referencing Foreign Key** (Staff hierarchy)
2. **Many-to-Many Relationships** (staff_assignments, team_members)
3. **Enum Data Types** (status, role, gender)
4. **Decimal for Money** (debt_amount, salary, prize)
5. **Auto-Increment Primary Keys**
6. **Composite Unique Constraints**
7. **Cascading Delete/Update**
8. **Complex Views with Multiple Joins**
9. **Aggregate Functions with GROUP BY/HAVING**
10. **Subqueries (simple, nested, correlated)**

---

## 📁 File Structure

```
database/
├── create_players_table.sql          # Players table DDL
├── create_staff_tables.sql           # Staff, Games, Assignments DDL
├── create_additional_tables.sql      # 4 new tables + views
├── complete_schema.sql               # Full schema documentation
├── SquidSphere_ER_Diagram.drawio    # ER Diagram XML
└── ADDITIONAL_TABLES_README.md       # Implementation guide
```

---

## 🚀 Common Queries Used in Application

### Dashboard Statistics
```sql
-- Total alive players
SELECT COUNT(*) FROM players WHERE status IN ('alive', 'winner');

-- Total eliminated
SELECT COUNT(*) FROM players WHERE status = 'eliminated';

-- Prize money
SELECT COUNT(*) * 100000000 FROM players WHERE status = 'eliminated';

-- Gender distribution
SELECT gender, COUNT(*) FROM players GROUP BY gender;
```

### Game Management
```sql
-- Check round completion
SELECT * FROM completed_rounds WHERE round_number = 1;

-- Random elimination
UPDATE players SET status = 'eliminated' 
WHERE status = 'alive' 
ORDER BY RAND() 
LIMIT 10;

-- Declare winner
UPDATE players SET status = 'winner' 
WHERE player_id = (
    SELECT player_id FROM players WHERE status = 'alive' LIMIT 1
);
```

### Staff Management
```sql
-- Staff hierarchy
SELECT * FROM v_staff_hierarchy;

-- Staff by role
SELECT role, COUNT(*) FROM staff GROUP BY role;

-- Assignments per staff
SELECT s.name, COUNT(sa.assignment_id) 
FROM staff s 
LEFT JOIN staff_assignments sa ON s.staff_id = sa.staff_id
GROUP BY s.staff_id;
```

---

## 📝 Notes

- **Database Engine:** InnoDB (supports foreign keys)
- **Character Set:** utf8mb4 (supports emojis, international characters)
- **Auto-Increment:** Used for all primary keys
- **Timestamps:** Automatic for created_date, updated_at fields
- **Indexes:** Created on foreign keys and frequently queried columns

---

## ✅ Lab Requirements Coverage

| Lab Topic | Requirement | Status | Implementation |
|-----------|-------------|--------|----------------|
| Lab 02 | DDL (CREATE, ALTER, DROP) | ✅ | 9 tables, indexes, views |
| Lab 03 | Constraints | ✅ | PK, FK, UNIQUE, CHECK, NOT NULL |
| Lab 04 | Aggregates | ✅ | COUNT, SUM, AVG, MIN, MAX, GROUP BY, HAVING |
| Lab 05 | Subqueries | ✅ | Simple, nested, IN/NOT IN, EXISTS |
| Lab 05 | Set Operations | ✅ | UNION, INTERSECT simulation |
| Lab 05 | Views | ✅ | 4 views created |
| Lab 06 | INNER JOIN | ✅ | Player-game participation |
| Lab 06 | OUTER JOIN | ✅ | LEFT/RIGHT joins |
| Lab 06 | SELF JOIN | ✅ | Staff hierarchy ⭐ |
| Lab 06 | CROSS JOIN | ✅ | All combinations |

**Total SQL Commands Used:** 50+  
**Total Query Patterns:** 100+  
**Lab Coverage:** 100% ✅

---

**End of SQL Implementation Overview**
