# SquidSphere Database Project - Complete SQL Features Reference

## 📊 ALL SQL CONCEPTS IMPLEMENTED

### ✅ Lab 03: DDL & Constraints
**Location**: `database/create_staff_tables.sql`, `database/create_players_table.sql`

1. **CREATE TABLE**
   - `players` table with 8 columns
   - `staff` table with 6 columns
   - `games` table with 5 columns
   - `staff_assignments` table with 4 columns

2. **Data Types**
   - INT, BIGINT, VARCHAR, ENUM, TIMESTAMP, DECIMAL

3. **Constraints**
   - PRIMARY KEY
   - FOREIGN KEY with ON DELETE CASCADE
   - UNIQUE
   - NOT NULL
   - DEFAULT
   - CHECK (age > 0, salary > 0)
   - AUTO_INCREMENT

4. **ALTER TABLE**
   - Add columns
   - Modify columns
   - Add/Drop constraints

5. **DROP TABLE**
   - IF EXISTS

6. **CREATE VIEW**
   - `v_staff_hierarchy` - Staff with supervisor names
   - `v_staff_assignments_summary` - Assignment counts per staff

---

### ✅ Lab 04: DML (Data Manipulation)
**Location**: Throughout all PHP files

1. **INSERT**
   ```sql
   -- Insert single player
   INSERT INTO players (player_number, name, age, gender, nationality, debt, status) 
   VALUES ('001', 'Seong Gi-hun', 47, 'Male', 'Korean', 255000000, 'alive');
   
   -- Insert staff with hierarchy
   INSERT INTO staff (staff_id, name, role, rank, supervisor_id, salary) 
   VALUES (1, 'Front Man', 'frontman', 1, NULL, 10000000);
   ```

2. **UPDATE**
   ```sql
   -- Update player status
   UPDATE players SET status = 'eliminated' WHERE player_id = 123;
   
   -- Update multiple fields
   UPDATE players SET age = 48, debt = 300000000 WHERE player_number = '001';
   ```

3. **DELETE**
   ```sql
   -- Delete single player
   DELETE FROM players WHERE player_id = 456;
   
   -- Delete with condition
   DELETE FROM players WHERE status = 'eliminated' AND age < 20;
   ```

4. **SELECT**
   ```sql
   -- Basic select
   SELECT * FROM players;
   
   -- With conditions
   SELECT name, age, debt FROM players WHERE status = 'alive';
   ```

---

### ✅ Lab 05: Subqueries & Set Operations
**Location**: `api/search_players.php`, `search.php`

#### Subqueries

1. **Simple Subquery with IN**
   ```sql
   -- Players with above average debt
   SELECT * FROM players 
   WHERE player_id IN (
       SELECT player_id FROM players 
       WHERE debt > (SELECT AVG(debt) FROM players)
   );
   ```

2. **Subquery with NOT IN**
   ```sql
   -- Exclude rare nationalities
   SELECT * FROM players 
   WHERE nationality NOT IN (
       SELECT nationality FROM players 
       GROUP BY nationality 
       HAVING COUNT(*) < 5
   );
   ```

3. **Nested Subquery (2+ levels)**
   ```sql
   -- Players with maximum debt
   SELECT * FROM players 
   WHERE debt = (
       SELECT MAX(debt) FROM players 
       WHERE player_id IN (SELECT player_id FROM players)
   );
   ```

4. **Correlated Subquery with EXISTS**
   ```sql
   -- Players with similar debt to others
   SELECT p1.* FROM players p1 
   WHERE EXISTS (
       SELECT 1 FROM players p2 
       WHERE p2.player_id != p1.player_id 
         AND p2.debt BETWEEN p1.debt - 5000000 AND p1.debt + 5000000
   );
   ```

5. **Subquery with Aggregate Functions**
   ```sql
   -- Players older than average
   SELECT * FROM players 
   WHERE age > (SELECT AVG(age) FROM players);
   ```

#### Set Operations

6. **UNION**
   ```sql
   -- Males OR High debt females
   (SELECT * FROM players WHERE gender = 'Male')
   UNION
   (SELECT * FROM players WHERE gender = 'Female' AND debt > 50000000);
   ```

7. **INTERSECT (Simulated)**
   ```sql
   -- Young AND Low debt
   SELECT * FROM players 
   WHERE player_id IN (SELECT player_id FROM players WHERE age < 30)
     AND player_id IN (SELECT player_id FROM players WHERE debt < 10000000);
   ```

8. **MINUS/EXCEPT (Simulated)**
   ```sql
   -- Alive but NOT young
   SELECT * FROM players 
   WHERE status = 'alive' 
     AND player_id NOT IN (SELECT player_id FROM players WHERE age < 25);
   ```

---

### ✅ Lab 06: JOINS
**Location**: `staff_visual.php`, `staff.php`, `staff_assignments.php`

1. **SELF JOIN**
   ```sql
   -- Staff hierarchy with supervisor names
   SELECT s1.staff_id, s1.name, s1.role, s1.rank,
          s2.name AS supervisor_name, s2.role AS supervisor_role
   FROM staff s1
   LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id
   ORDER BY s1.rank ASC;
   ```

2. **INNER JOIN (2 tables)**
   ```sql
   -- Staff with their assignments
   SELECT s.name, s.role, g.game_name, sa.assignment_date
   FROM staff_assignments sa
   INNER JOIN staff s ON sa.staff_id = s.staff_id
   INNER JOIN games g ON sa.game_id = g.game_id;
   ```

3. **INNER JOIN (3 tables)**
   ```sql
   -- Complete assignment details
   SELECT s.name AS staff_name, s.role, 
          g.game_name, g.round_number,
          sa.assignment_date
   FROM staff_assignments sa
   INNER JOIN staff s ON sa.staff_id = s.staff_id
   INNER JOIN games g ON sa.game_id = g.game_id
   ORDER BY g.round_number, s.role;
   ```

4. **LEFT JOIN**
   ```sql
   -- All staff including those without supervisors
   SELECT s1.name, s2.name AS supervisor
   FROM staff s1
   LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id;
   ```

---

### ✅ Additional SQL Features

#### Aggregate Functions
**Location**: `dashboard.php`

1. **COUNT()**
   ```sql
   -- Count alive players
   SELECT COUNT(*) AS alive_count FROM players WHERE status = 'alive';
   
   -- Count by group
   SELECT gender, COUNT(*) AS count FROM players GROUP BY gender;
   ```

2. **SUM()**
   ```sql
   -- Total prize money
   SELECT SUM(100) AS total_prize 
   FROM players WHERE status = 'eliminated';
   ```

3. **AVG()**
   ```sql
   -- Average age
   SELECT AVG(age) AS avg_age FROM players;
   
   -- Average debt
   SELECT AVG(debt) AS avg_debt FROM players;
   ```

4. **MAX()**
   ```sql
   -- Maximum age
   SELECT MAX(age) AS oldest FROM players;
   
   -- Maximum debt
   SELECT MAX(debt) AS highest_debt FROM players;
   ```

5. **MIN()**
   ```sql
   -- Minimum age
   SELECT MIN(age) AS youngest FROM players;
   
   -- Minimum debt
   SELECT MIN(debt) AS lowest_debt FROM players;
   ```

#### GROUP BY
**Location**: `dashboard.php`, `search.php`

```sql
-- Group by single column
SELECT gender, COUNT(*) AS count, AVG(age) AS avg_age
FROM players
GROUP BY gender;

-- Group by multiple columns
SELECT nationality, gender, COUNT(*) AS count
FROM players
GROUP BY nationality, gender;
```

#### HAVING
**Location**: `dashboard.php`, `search.php`

```sql
-- Filter grouped results
SELECT nationality, COUNT(*) AS count
FROM players
GROUP BY nationality
HAVING COUNT(*) > 10;

-- With aggregate condition
SELECT supervisor_id, COUNT(*) AS subordinate_count
FROM staff
GROUP BY supervisor_id
HAVING COUNT(*) >= 2;
```

#### ORDER BY
**Location**: All pages

```sql
-- Single column ascending
ORDER BY player_number ASC;

-- Single column descending
ORDER BY debt DESC;

-- Multiple columns
ORDER BY nationality ASC, age DESC;

-- With aggregate
SELECT nationality, COUNT(*) 
FROM players 
GROUP BY nationality 
ORDER BY COUNT(*) DESC;
```

#### LIMIT
**Location**: `search.php`, `staff_assignments.php`

```sql
-- Top 10 results
SELECT * FROM players ORDER BY debt DESC LIMIT 10;

-- Top 3 nationalities
SELECT nationality, COUNT(*) AS count
FROM players
GROUP BY nationality
ORDER BY COUNT(*) DESC
LIMIT 3;
```

#### DISTINCT
**Location**: `search.php`

```sql
-- Unique values
SELECT DISTINCT nationality FROM players;

-- In queries
SELECT DISTINCT * FROM players WHERE gender = 'Male';
```

#### LIKE (Pattern Matching)
**Location**: `search.php`

```sql
-- Starts with
SELECT * FROM players WHERE name LIKE 'Seong%';

-- Contains
SELECT * FROM players WHERE name LIKE '%Sang%';

-- Ends with
SELECT * FROM players WHERE name LIKE '%woo';

-- Any position
SELECT * FROM players WHERE nationality LIKE '%Korea%';
```

#### BETWEEN
**Location**: `search.php`

```sql
-- Age range
SELECT * FROM players WHERE age BETWEEN 20 AND 40;

-- Debt range
SELECT * FROM players WHERE debt BETWEEN 10000000 AND 50000000;
```

#### IN
**Location**: `search.php`

```sql
-- Multiple values
SELECT * FROM players WHERE player_number IN ('001', '067', '456');

-- With subquery
SELECT * FROM players 
WHERE nationality IN (
    SELECT nationality FROM players 
    GROUP BY nationality 
    ORDER BY COUNT(*) DESC 
    LIMIT 3
);
```

---

## 📁 File Organization

### Frontend
- `index.php` - Entry page
- `players.php` - Player management
- `search.php` - **Advanced search with all SQL features**
- `dashboard.php` - Aggregates and statistics
- `staff_visual.php` - Staff hierarchy (SELF JOIN)
- `staff_assignments.php` - Assignment tracking (INNER JOIN)
- `games.php` - Game selection
- `game_*.php` - Individual games

### Backend API
- `api/player_api.php` - CRUD operations
- `api/search_players.php` - **Advanced search queries**

### Database
- `database/create_players_table.sql` - Players table DDL
- `database/create_staff_tables.sql` - Staff system DDL
- `database/generate_players.php` - Insert 456 players
- `database/SEARCH_SQL_DOCUMENTATION.md` - **Complete SQL reference**

---

## 🎯 SQL Concepts Coverage Summary

### Lab 03: DDL & Constraints ✅
- CREATE, ALTER, DROP TABLE
- PRIMARY KEY, FOREIGN KEY, UNIQUE, CHECK, DEFAULT
- AUTO_INCREMENT
- CREATE VIEW

### Lab 04: DML ✅
- INSERT, UPDATE, DELETE, SELECT
- WHERE conditions
- Multiple table operations

### Lab 05: Subqueries & Set Operations ✅
- Simple subqueries (IN, NOT IN)
- Nested subqueries (2+ levels)
- Correlated subqueries (EXISTS)
- UNION
- INTERSECT (simulated)
- MINUS (simulated)
- Subqueries with aggregates

### Lab 06: JOINS ✅
- SELF JOIN (staff hierarchy)
- INNER JOIN (2 and 3 tables)
- LEFT JOIN
- Table aliases

### Additional Features ✅
- Aggregate functions (COUNT, SUM, AVG, MIN, MAX)
- GROUP BY (single and multiple columns)
- HAVING
- ORDER BY (ASC/DESC)
- LIMIT
- DISTINCT
- LIKE pattern matching
- BETWEEN
- Multiple conditions (AND, OR)

---

## 🏆 Complete Feature Matrix

| Feature | Players | Search | Dashboard | Staff | Staff Assign | Games |
|---------|---------|--------|-----------|-------|--------------|-------|
| SELECT | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| INSERT | ✅ | - | - | - | - | ✅ |
| UPDATE | ✅ | - | - | - | - | ✅ |
| DELETE | ✅ | - | - | - | - | - |
| WHERE | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| LIKE | - | ✅ | - | - | - | - |
| BETWEEN | - | ✅ | - | - | - | - |
| IN | - | ✅ | - | - | - | - |
| NOT IN | - | ✅ | - | - | - | - |
| EXISTS | - | ✅ | - | - | - | - |
| Subquery | - | ✅ | ✅ | - | - | - |
| UNION | - | ✅ | - | - | - | - |
| JOIN | - | - | - | ✅ | ✅ | - |
| SELF JOIN | - | - | - | ✅ | - | - |
| COUNT | - | - | ✅ | ✅ | ✅ | - |
| SUM | - | - | ✅ | - | - | - |
| AVG | - | ✅ | ✅ | - | - | - |
| MAX | - | ✅ | ✅ | - | - | - |
| MIN | - | ✅ | ✅ | - | - | - |
| GROUP BY | - | ✅ | ✅ | ✅ | ✅ | - |
| HAVING | - | ✅ | ✅ | ✅ | - | - |
| ORDER BY | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| LIMIT | - | ✅ | - | - | ✅ | - |
| DISTINCT | - | ✅ | - | - | - | - |

---

## 🎓 Educational Value

### What Students Learn:
1. **DDL**: Table creation with constraints
2. **DML**: CRUD operations
3. **Basic Queries**: SELECT with WHERE
4. **Pattern Matching**: LIKE with wildcards
5. **Aggregates**: Statistical analysis
6. **Grouping**: Data summarization
7. **Subqueries**: Nested queries
8. **Set Theory**: UNION, INTERSECT, MINUS
9. **Joins**: Combining tables
10. **Self-Referential**: Hierarchical data
11. **Views**: Virtual tables
12. **Constraints**: Data integrity

### Real-World Applications:
- E-commerce product search
- HR employee hierarchy
- Financial report generation
- Inventory management
- User analytics
- Complex filtering systems

---

**Total SQL Concepts**: 40+
**Database Tables**: 4 (players, staff, games, staff_assignments)
**Total Records**: 500+ (456 players, 36 staff, 6 games, 30+ assignments)
**Lines of SQL**: 2000+
**Query Complexity**: Beginner to Advanced

---

**Project Status**: ✅ PRODUCTION READY
**All Labs**: ✅ COMPLETE
**Documentation**: ✅ COMPREHENSIVE
**UI/UX**: ✅ PROFESSIONAL
