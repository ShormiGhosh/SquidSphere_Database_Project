# SquidSphere Database - Additional Tables Implementation

## Tables Added (4 New Tables)

### 1. **teams** table
- For team-based games (Tug of War)
- Tracks team leader, status (active/eliminated/winner)
- Links to games table

### 2. **team_members** table
- Many-to-many relationship: teams ↔ players
- Tracks which players are in which team
- Role: leader/member/supporter

### 3. **prize_distribution** table
- Records prize money given to players
- Tracks payment status (pending/paid/cancelled)
- Links to players and games

### 4. **game_participation** table
- Records every player's performance in each game
- Tracks: result (survived/eliminated/winner), score, completion_time
- Can link to teams for team-based games

### 5. **completed_rounds** table (Already created via API)
- Prevents rounds from being played multiple times
- Auto-created by mark_round_complete.php

## Views Created (4 New Views)

1. **vw_team_performance** - Team statistics and performance
2. **vw_player_game_history** - Player's game history and stats
3. **vw_game_statistics** - Overall game statistics
4. **vw_prize_summary** - Prize distribution per player

## API Endpoints Created

### Record Game Participation
```
POST api/record_participation.php
Parameters:
- playerId (int) - required
- gameId (int) - required
- result (string) - 'survived', 'eliminated', 'winner'
- score (float) - optional
- teamId (int) - optional (for team games)
```

### Get Participation Records
```
GET api/get_participation.php
Parameters:
- playerId (int) - optional filter
- gameId (int) - optional filter
```

### Record Prize Distribution
```
POST api/record_prize.php
Parameters:
- playerId (int) - required
- amount (float) - required
- gameId (int) - optional
- description (string) - optional
```

## Installation Steps

### Step 1: Run the SQL migration
```sql
-- In phpMyAdmin or MySQL command line:
source database/create_additional_tables.sql;

-- Or import the file via phpMyAdmin
```

### Step 2: Verify tables were created
```sql
SHOW TABLES;
-- Should show: teams, team_members, prize_distribution, game_participation
```

### Step 3: Check views
```sql
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';
-- Should show: vw_team_performance, vw_player_game_history, vw_game_statistics, vw_prize_summary
```

## Total Database Tables Now

1. **players** - Player information (existing)
2. **staff** - Staff hierarchy (existing)
3. **games** - Game details (existing)
4. **staff_assignments** - Staff-game assignments (existing)
5. **teams** - Team information (NEW)
6. **team_members** - Team membership (NEW)
7. **prize_distribution** - Prize tracking (NEW)
8. **game_participation** - Player-game records (NEW)
9. **completed_rounds** - Round tracking (auto-created)

**Total: 9 tables** (up from 4)

## Impact on Current Logic

✅ **NO CHANGES to existing features**
- All current elimination logic unchanged
- Game simulations work exactly the same
- Staff pages unaffected
- Player pages unaffected

✅ **New capabilities added**
- Can now track individual player performance per game
- Can create teams for Tug of War
- Can record prize distributions
- Can query game statistics via views

## Optional: Populate Sample Data

Uncomment the sample data section in `create_additional_tables.sql` to add test records.

## Usage Examples

### Example 1: Record a player's participation in Red Light
```javascript
const formData = new FormData();
formData.append('playerId', 1);
formData.append('gameId', 1);
formData.append('result', 'survived');
formData.append('score', 100);

await fetch('api/record_participation.php', {
    method: 'POST',
    body: formData
});
```

### Example 2: Get player's game history
```javascript
const response = await fetch('api/get_participation.php?playerId=1');
const data = await response.json();
console.log(data.participations);
```

### Example 3: Record prize money
```javascript
const formData = new FormData();
formData.append('playerId', 1);
formData.append('amount', 10000000);
formData.append('gameId', 1);
formData.append('description', 'Round 1 survival bonus');

await fetch('api/record_prize.php', {
    method: 'POST',
    body: formData
});
```

## Future Integration Ideas (Optional)

1. **Auto-record participation**: Modify elimination logic to automatically call `record_participation.php`
2. **Team creation UI**: Add interface to create teams for Tug of War
3. **Prize dashboard**: Show prize distribution statistics
4. **Player stats page**: Show individual player performance using views

All of these are optional and won't affect current functionality.
