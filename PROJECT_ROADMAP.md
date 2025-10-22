# SquidSphere Database Lab Project - Complete Roadmap
## Best Project Award Strategy 🏆

---

## Database Schema Overview

### Tables Created:
1. **players** - Main player data (456 players)
2. **games** - Game definitions (6 games from Squid Game)
3. **teams** - Team formation for team-based games
4. **team_members** - Many-to-many relationship for teams
5. **game_participation** - Track player performance in each game
6. **staff** - Staff hierarchy (for SELF JOIN)
7. **votes** - Voting system (continue vs stop)
8. **prize_distribution** - Prize money distribution

---

## Lab Topics Coverage Map

### ✅ Lab 02: DDL (Data Definition Language)
**Location:** `database/complete_schema.sql`

**Coverage:**
- ✓ CREATE TABLE statements for 8 tables
- ✓ ALTER TABLE statements (adding columns, indexes)
- ✓ DROP/MODIFY examples

**Where to Demonstrate:**
- Show table creation in phpMyAdmin
- Display ALTER statements adding new features
- Schema modification page in web interface

---

### ✅ Lab 03: Constraints
**Coverage in ALL Tables:**

| Constraint | Example Tables | Implementation |
|------------|---------------|----------------|
| PRIMARY KEY | All tables | Auto-increment IDs |
| FOREIGN KEY | teams, team_members, game_participation, votes, prize_distribution | Proper relationships |
| UNIQUE | players.player_number, games.game_name | No duplicates |
| NOT NULL | All critical fields | Data integrity |
| CHECK | players.age >= 18, debt_amount > 0 | Business rules |
| DEFAULT | status fields, timestamps | Auto-fill values |
| ON DELETE CASCADE | Maintain referential integrity | Clean deletions |

**Where to Demonstrate:**
- Player registration form (validates constraints)
- Try adding duplicate player number (UNIQUE constraint)
- Try adding player age < 18 (CHECK constraint)

---

### ✅ Lab 03: SELECT Basics
**Features to Implement:**

**Pages:**
1. **Player Search Page**
   - Search by name, nationality, age range
   - Filter by status (alive/eliminated/winner)
   - ORDER BY: name, age, debt, player_number
   - LIMIT and OFFSET for pagination

2. **Game Browser**
   - Filter by difficulty, type, status
   - ORDER BY game_order, prize_pool

**SQL Examples:**
```sql
-- Search with WHERE
SELECT * FROM players WHERE name LIKE '%kim%';

-- Multiple conditions
SELECT * FROM players WHERE age BETWEEN 20 AND 30 AND nationality = 'South Korea';

-- ORDER BY
SELECT * FROM players ORDER BY debt_amount DESC LIMIT 10;
```

---

### ✅ Lab 04: Aggregates (Dashboard Page)
**Dashboard Features:**

**Statistics to Display:**
1. **Overall Statistics**
   ```sql
   SELECT 
       COUNT(*) as total_players,
       COUNT(CASE WHEN status='alive' THEN 1 END) as alive,
       COUNT(CASE WHEN status='eliminated' THEN 1 END) as eliminated,
       SUM(debt_amount) as total_debt,
       AVG(debt_amount) as avg_debt,
       MIN(age) as youngest,
       MAX(age) as oldest,
       AVG(age) as avg_age
   FROM players;
   ```

2. **Group By Nationality**
   ```sql
   SELECT 
       nationality,
       COUNT(*) as player_count,
       AVG(age) as avg_age,
       SUM(debt_amount) as total_debt
   FROM players
   GROUP BY nationality
   HAVING player_count >= 5
   ORDER BY player_count DESC;
   ```

3. **Group By Gender**
   ```sql
   SELECT gender, COUNT(*) as count, AVG(debt_amount) as avg_debt
   FROM players
   GROUP BY gender;
   ```

4. **Game Statistics**
   ```sql
   SELECT 
       g.game_name,
       COUNT(gp.player_id) as participants,
       AVG(gp.score) as avg_score,
       MAX(gp.score) as high_score
   FROM games g
   LEFT JOIN game_participation gp ON g.game_id = gp.game_id
   GROUP BY g.game_id;
   ```

---

### ✅ Lab 05: Subqueries
**Implementation Areas:**

1. **Voting System**
   ```sql
   -- Players who voted to continue
   SELECT * FROM players 
   WHERE player_id IN (SELECT voter_id FROM votes WHERE vote_choice='continue');
   
   -- Players with above-average debt
   SELECT * FROM players 
   WHERE debt_amount > (SELECT AVG(debt_amount) FROM players);
   ```

2. **Player Ranking**
   ```sql
   -- Top performers
   SELECT * FROM players
   WHERE player_id IN (
       SELECT player_id FROM game_participation 
       GROUP BY player_id 
       HAVING SUM(score) > 1000
   );
   ```

3. **Team Formation**
   ```sql
   -- Players not in any team
   SELECT * FROM players
   WHERE player_id NOT IN (SELECT player_id FROM team_members);
   ```

---

### ✅ Lab 05: Set Operations (Dedicated Page)
**Page: "Set Operations Showcase"**

1. **UNION - Combine High Debt and Senior Players**
   ```sql
   SELECT player_id, name, 'High Debt' as category FROM players WHERE debt_amount > 5000000
   UNION
   SELECT player_id, name, 'Senior' as category FROM players WHERE age > 60;
   ```

2. **INTERSECT - Players in both categories (simulate)**
   ```sql
   SELECT player_id, name FROM players WHERE debt_amount > 5000000
   AND player_id IN (SELECT player_id FROM players WHERE age > 60);
   ```

3. **MINUS/EXCEPT - Players who haven't played any game**
   ```sql
   SELECT player_id, name FROM players
   WHERE player_id NOT IN (SELECT player_id FROM game_participation);
   ```

---

### ✅ Lab 05: Views (4-5 Views)
**Created Views:**

1. **vw_active_players** - Active players with game statistics
2. **vw_game_statistics** - Game performance metrics
3. **vw_team_performance** - Team rankings and stats
4. **vw_player_rankings** - Player leaderboard
5. **vw_staff_hierarchy** - Staff organization chart

**Demonstration:**
- View management page
- Show data from views
- Update underlying tables and show view updates
- Explain view benefits

---

### ✅ Lab 06: INNER JOIN
**Multiple Join Examples:**

1. **Players + Games + Results**
   ```sql
   SELECT p.name, g.game_name, gp.result, gp.score
   FROM players p
   INNER JOIN game_participation gp ON p.player_id = gp.player_id
   INNER JOIN games g ON gp.game_id = g.game_id;
   ```

2. **Teams + Members + Players**
   ```sql
   SELECT t.team_name, p.name as member_name, tm.role
   FROM teams t
   INNER JOIN team_members tm ON t.team_id = tm.team_id
   INNER JOIN players p ON tm.player_id = p.player_id;
   ```

---

### ✅ Lab 06: OUTER JOIN (LEFT/RIGHT)
**Scenarios:**

1. **LEFT JOIN - All Players (even without games)**
   ```sql
   SELECT p.name, COUNT(gp.game_id) as games_played
   FROM players p
   LEFT JOIN game_participation gp ON p.player_id = gp.player_id
   GROUP BY p.player_id;
   ```

2. **RIGHT JOIN - All Games (even without participants)**
   ```sql
   SELECT g.game_name, COUNT(gp.player_id) as participants
   FROM game_participation gp
   RIGHT JOIN games g ON gp.game_id = g.game_id
   GROUP BY g.game_id;
   ```

---

### ✅ Lab 06: SELF JOIN
**Staff Hierarchy:**

```sql
SELECT 
    s.name as employee,
    s.role as position,
    m.name as manager,
    m.role as manager_position
FROM staff s
LEFT JOIN staff m ON s.manager_id = m.staff_id;
```

**Team Member Relationships:**
```sql
-- Find team members from same team
SELECT 
    t1.name as player1,
    t2.name as player2,
    tm1.team_id
FROM team_members tm1
JOIN team_members tm2 ON tm1.team_id = tm2.team_id AND tm1.player_id < tm2.player_id
JOIN players t1 ON tm1.player_id = t1.player_id
JOIN players t2 ON tm2.player_id = t2.player_id;
```

---

### ✅ Lab 06: NON-EQUI JOIN
**Examples:**

1. **Player Comparison by Debt Range**
   ```sql
   SELECT 
       p1.name as player1,
       p2.name as player2,
       p1.debt_amount as debt1,
       p2.debt_amount as debt2
   FROM players p1
   JOIN players p2 ON p1.debt_amount BETWEEN p2.debt_amount * 0.9 AND p2.debt_amount * 1.1
   WHERE p1.player_id < p2.player_id;
   ```

2. **Team Balancing by Age Range**
   ```sql
   SELECT t1.team_id, COUNT(*) as balanced_teams
   FROM teams t1
   JOIN teams t2 ON ABS(t1.avg_age - t2.avg_age) < 5
   WHERE t1.team_id != t2.team_id;
   ```

---

### ✅ Lab 06: CROSS JOIN (Bonus Feature)
**All Possible Player-Game Combinations:**

```sql
-- Generate all possible matchups
SELECT 
    p.player_number,
    p.name,
    g.game_name,
    g.difficulty_level
FROM players p
CROSS JOIN games g
ORDER BY p.player_number, g.game_order;
```

**Use Case:** Tournament bracket generation, simulation planning

---

## Web Pages to Implement

### 1. **Dashboard** (Lab 04 - Aggregates)
- Overall statistics with COUNT, SUM, AVG, MIN, MAX
- Charts showing GROUP BY results
- HAVING clause demonstrations

### 2. **Player Management** (Lab 03 - SELECT, Constraints)
- Player list with search/filter
- Add/Edit player (constraint validation)
- Player details popup

### 3. **Game Analytics** (Lab 06 - Joins)
- Game participation history
- Multiple table joins
- Performance metrics

### 4. **Team Management** (Lab 05 - Subqueries, Lab 06 - Joins)
- Team formation
- Member assignment
- Team rankings

### 5. **Set Operations Showcase** (Lab 05)
- Dedicated page showing UNION, INTERSECT, MINUS
- Visual representation of sets

### 6. **Views Management** (Lab 05)
- Display all 5 views
- Demonstrate view updates
- Explain view purposes

### 7. **Staff Hierarchy** (Lab 06 - Self Join)
- Organization chart
- Manager-employee relationships

### 8. **Advanced Queries** (Lab 06 - All Join Types)
- Showcase each join type
- Side-by-side comparison
- Query explanations

---

## Implementation Priority

### Phase 1: Foundation (DONE ✓)
- [x] Players table with 456 records
- [x] Basic player CRUD operations
- [x] Player list and details view

### Phase 2: Extended Schema (NEXT)
- [ ] Run `complete_schema.sql` to create all tables
- [ ] Insert sample data for games, staff
- [ ] Create relationships

### Phase 3: Feature Pages
- [ ] Dashboard with aggregates
- [ ] Search/Filter functionality
- [ ] Team management
- [ ] Voting system

### Phase 4: Advanced Demonstrations
- [ ] All join types showcase page
- [ ] Set operations page
- [ ] Views management
- [ ] Staff hierarchy visualization

### Phase 5: Polish
- [ ] Documentation
- [ ] Code comments explaining SQL concepts
- [ ] Presentation materials
- [ ] Video demo preparation

---

## Tips for Best Project Award

1. **Visual Appeal** - Make it look professional like Squid Game theme
2. **Clear Demonstrations** - Each page should clearly show which lab topic it covers
3. **Code Comments** - Explain SQL queries in comments
4. **Documentation** - Document all queries and their purposes
5. **Live Demo** - Prepare smooth demo flow
6. **Error Handling** - Show constraint violations gracefully
7. **Performance** - Use indexes, optimize queries
8. **Completeness** - Cover EVERY topic in the checklist

---

## Next Steps

1. Run `database/complete_schema.sql` in phpMyAdmin
2. I'll create the Dashboard page next
3. Then we'll build feature pages one by one
4. Each page will clearly demonstrate specific lab topics

Let's build the best database project! 🚀
