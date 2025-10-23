# SQL Queries Documentation - SquidSphere Database Project

## Player Status Visualization Feature

### Overview
This document explains the SQL queries used to differentiate between alive and eliminated players on the player profile page. Eliminated players are displayed with reduced opacity (30%) and grayscale filter, just like in the Squid Game series.

---

## SQL Queries Used

### 1. **Fetch All Players with Status**
```sql
SELECT * FROM players ORDER BY player_number ASC
```
**Purpose:** Retrieves all players from the database, including their status field.

**Location:** `api/player_api.php` - `getAllPlayers()` function

**Fields Retrieved:**
- `player_id` - Unique identifier
- `player_number` - Player's game number (001-456)
- `name` - Player's name
- `age` - Player's age
- `gender` - Male/Female/Other
- `status` - **'alive' or 'eliminated'** (critical for opacity)
- `debt_amount` - Amount of debt
- `nationality` - Player's country
- `alliance_group` - Optional team/alliance
- `registration_date` - Date player was added

**Example Result:**
```
| player_id | player_number | name      | status      | ... |
|-----------|---------------|-----------|-------------|-----|
| 1         | 001           | John Doe  | alive       | ... |
| 2         | 002           | Jane Smith| eliminated  | ... |
| 3         | 003           | Bob Lee   | alive       | ... |
```

---

### 2. **Filter Only Alive Players**
```sql
SELECT * FROM players 
WHERE status = 'alive' OR status IS NULL 
ORDER BY player_number ASC
```
**Purpose:** Get only players who are still in the game.

**Used In:** Dashboard statistics, alive player count

**Why `status IS NULL`?**
- New players may have NULL status by default
- They should be treated as alive until eliminated

---

### 3. **Filter Only Eliminated Players**
```sql
SELECT * FROM players 
WHERE status = 'eliminated' 
ORDER BY player_number ASC
```
**Purpose:** Get only players who have been eliminated.

**Used In:** 
- Dashboard eliminated count
- Prize money calculation (COUNT * 100M)
- Visual differentiation on player tiles

---

### 4. **Count Players by Status**
```sql
SELECT 
    status,
    COUNT(*) as player_count
FROM players 
GROUP BY status
```
**Purpose:** Get distribution of alive vs eliminated players.

**Example Result:**
```
| status      | player_count |
|-------------|--------------|
| alive       | 256          |
| eliminated  | 200          |
```

---

### 5. **Update Player Status (After Game Round)**
```sql
UPDATE players 
SET status = 'eliminated' 
WHERE player_id = ?
```
**Purpose:** Mark a player as eliminated after they lose a game round.

**Location:** `api/player_api.php` - `updatePlayer()` function

**Usage Example:**
After Red Light Green Light, update eliminated players:
```sql
UPDATE players 
SET status = 'eliminated' 
WHERE player_number IN (005, 023, 067, 142, ...);
```

---

### 6. **Bulk Update Status for Multiple Players**
```sql
UPDATE players 
SET status = 'eliminated' 
WHERE player_id IN (?, ?, ?, ...)
```
**Purpose:** Eliminate multiple players at once after a game round.

**Example:** After Tug of War, eliminate losing team:
```sql
UPDATE players 
SET status = 'eliminated' 
WHERE player_id IN (45, 67, 89, 123, 145, 167, 189, 201, 223, 245);
```

---

### 7. **Get Elimination Statistics**
```sql
SELECT 
    COUNT(*) as total_eliminated,
    COUNT(*) * 100 as prize_money_millions
FROM players 
WHERE status = 'eliminated'
```
**Purpose:** Calculate total eliminations and accumulated prize money.

**Used In:** Dashboard prize money display

---

### 8. **Gender-Based Status Distribution**
```sql
SELECT 
    gender,
    COUNT(*) as total_count,
    SUM(CASE WHEN status = 'alive' OR status IS NULL THEN 1 ELSE 0 END) as alive_count,
    SUM(CASE WHEN status = 'eliminated' THEN 1 ELSE 0 END) as eliminated_count
FROM players 
GROUP BY gender
```
**Purpose:** Show survival rates by gender.

**Example Result:**
```
| gender | total_count | alive_count | eliminated_count |
|--------|-------------|-------------|------------------|
| Male   | 300         | 180         | 120              |
| Female | 150         | 75          | 75               |
| Other  | 6           | 1           | 5                |
```

---

## Frontend Implementation

### CSS Classes Applied Based on Status

**Alive Players (Normal Display):**
```css
.player-tile {
    opacity: 1;
    filter: none;
}
```

**Eliminated Players (Reduced Opacity):**
```css
.player-tile.status-eliminated {
    opacity: 0.3;              /* 30% opacity like in Squid Game */
    filter: grayscale(100%);   /* Grayscale effect */
    border-color: rgba(128, 128, 128, 0.3);
}
```

### JavaScript Logic
```javascript
// In players.js - createPlayerTile() function
if (player.status === 'eliminated') {
    tile.classList.add('status-eliminated');
}
```

---

## Database Schema

### Players Table Structure
```sql
CREATE TABLE players (
    player_id INT PRIMARY KEY AUTO_INCREMENT,
    player_number VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    status ENUM('alive', 'eliminated') DEFAULT 'alive',  -- KEY FIELD
    debt_amount DECIMAL(15,2) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    alliance_group INT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Key Field: `status`**
- Type: ENUM('alive', 'eliminated')
- Default: 'alive'
- Used for: Visual differentiation, statistics, prize calculation

---

## Game Round Update Process

### Step-by-Step Flow

1. **Game Round Executes** (e.g., Red Light Green Light)
   ```javascript
   // In game_red_light.js
   const eliminated = [/* players who lost */];
   ```

2. **Update Database**
   ```php
   // In api/player_api.php
   foreach($eliminated_ids as $player_id) {
       $stmt = $conn->prepare("UPDATE players SET status = 'eliminated' WHERE player_id = ?");
       $stmt->bind_param("i", $player_id);
       $stmt->execute();
   }
   ```

3. **Refresh Player Page**
   ```javascript
   // In players.js
   loadPlayers(); // Fetches updated data with new statuses
   ```

4. **CSS Automatically Applied**
   ```javascript
   // Eliminated players automatically get .status-eliminated class
   if (player.status === 'eliminated') {
       tile.classList.add('status-eliminated'); // 30% opacity applied
   }
   ```

---

## Visual Effect Demonstration

### Before Elimination:
```
┌─────────────────┐
│  🟢 Player 001  │  <- Full opacity (100%)
│    Gi-hun       │  <- Colorful, vibrant
└─────────────────┘
```

### After Elimination:
```
┌─────────────────┐
│  ⚫ Player 001  │  <- Reduced opacity (30%)
│    Gi-hun       │  <- Grayscale, dimmed
└─────────────────┘
```

---

## Advanced SQL Queries for Future Features

### 1. **Survival Rate by Nationality**
```sql
SELECT 
    nationality,
    COUNT(*) as total_players,
    SUM(CASE WHEN status = 'alive' THEN 1 ELSE 0 END) as survivors,
    ROUND(SUM(CASE WHEN status = 'alive' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as survival_percentage
FROM players 
GROUP BY nationality
HAVING COUNT(*) > 5
ORDER BY survival_percentage DESC;
```

### 2. **Age Group Survival Analysis**
```sql
SELECT 
    CASE 
        WHEN age BETWEEN 18 AND 25 THEN '18-25'
        WHEN age BETWEEN 26 AND 35 THEN '26-35'
        WHEN age BETWEEN 36 AND 45 THEN '36-45'
        WHEN age BETWEEN 46 AND 60 THEN '46-60'
        ELSE '60+'
    END as age_group,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'alive' THEN 1 ELSE 0 END) as alive,
    SUM(CASE WHEN status = 'eliminated' THEN 1 ELSE 0 END) as eliminated
FROM players
GROUP BY age_group
ORDER BY age_group;
```

### 3. **Find Players with Highest Debt (Still Alive)**
```sql
SELECT player_number, name, debt_amount, status
FROM players
WHERE status = 'alive' OR status IS NULL
ORDER BY debt_amount DESC
LIMIT 10;
```

---

## Testing Queries

### Manually Eliminate Test Players
```sql
-- Eliminate specific players for testing
UPDATE players 
SET status = 'eliminated' 
WHERE player_number IN ('001', '005', '010', '015', '020');
```

### Revive Players (Reset for Testing)
```sql
-- Reset all players to alive
UPDATE players 
SET status = 'alive';
```

### Check Status Distribution
```sql
SELECT 
    status,
    COUNT(*) as count,
    GROUP_CONCAT(player_number ORDER BY player_number SEPARATOR ', ') as players
FROM players
GROUP BY status;
```

---

## Key Takeaways

✅ **Main Query:** `SELECT * FROM players` retrieves all players with their status
✅ **Status Field:** `ENUM('alive', 'eliminated')` controls visual display
✅ **CSS Magic:** `.status-eliminated` class applies 30% opacity + grayscale
✅ **Update Query:** `UPDATE players SET status = 'eliminated' WHERE player_id = ?`
✅ **Count Query:** `SELECT COUNT(*) FROM players WHERE status = 'eliminated'`

The visual effect is achieved by combining SQL status data with CSS styling, creating an authentic Squid Game experience! 🎮
