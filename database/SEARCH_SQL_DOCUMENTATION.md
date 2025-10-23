# Advanced Search SQL Queries Documentation

## Overview
This document details all SQL queries implemented in the SquidSphere search functionality, demonstrating various SQL concepts including SUBQUERIES, NESTED SUBQUERIES, SET OPERATIONS (UNION, INTERSECT, MINUS), and advanced filtering techniques.

---

## 1. BASIC FILTERING QUERIES

### 1.1 LIKE Pattern Matching
```sql
-- Search by player number pattern
SELECT * FROM players WHERE player_number LIKE '001';
SELECT * FROM players WHERE player_number LIKE '%5%';  -- Contains 5

-- Search by name pattern
SELECT * FROM players WHERE name LIKE 'Seong%';        -- Starts with Seong
SELECT * FROM players WHERE name LIKE '%Sang%';        -- Contains Sang
SELECT * FROM players WHERE name LIKE '%Ali%';         -- Contains Ali

-- Search by nationality pattern
SELECT * FROM players WHERE nationality LIKE '%Korea%';
SELECT * FROM players WHERE nationality LIKE 'Pakistan%';
```

### 1.2 Range Filtering
```sql
-- Age range
SELECT * FROM players WHERE age >= 20 AND age <= 40;

-- Debt range
SELECT * FROM players WHERE debt >= 10000000 AND debt <= 50000000;
```

### 1.3 DISTINCT - Remove Duplicates
```sql
-- All queries use DISTINCT to ensure unique results
SELECT DISTINCT * FROM players WHERE gender = 'Male';
```

---

## 2. SUBQUERIES (IN, NOT IN)

### 2.1 Above Average Debt (Subquery)
```sql
SELECT * FROM players 
WHERE player_id IN (
    SELECT player_id FROM players 
    WHERE debt > (SELECT AVG(debt) FROM players)
)
ORDER BY debt DESC;
```
**Explanation**: Uses a subquery to calculate average debt, then selects players with debt above that average.

### 2.2 Below Average Debt (Subquery)
```sql
SELECT * FROM players 
WHERE player_id IN (
    SELECT player_id FROM players 
    WHERE debt < (SELECT AVG(debt) FROM players)
)
ORDER BY debt ASC;
```

### 2.3 Above Average Age (Subquery)
```sql
SELECT * FROM players 
WHERE player_id IN (
    SELECT player_id FROM players 
    WHERE age > (SELECT AVG(age) FROM players)
)
ORDER BY age DESC;
```

### 2.4 Below Average Age (Subquery)
```sql
SELECT * FROM players 
WHERE player_id IN (
    SELECT player_id FROM players 
    WHERE age < (SELECT AVG(age) FROM players)
)
ORDER BY age ASC;
```

---

## 3. NESTED SUBQUERIES

### 3.1 Maximum Debt (Nested Subquery)
```sql
SELECT * FROM players 
WHERE debt = (
    SELECT MAX(debt) FROM players 
    WHERE player_id IN (SELECT player_id FROM players)
);
```
**Explanation**: Two-level nested subquery - inner most selects all player IDs, middle finds MAX debt, outer matches that debt.

### 3.2 Minimum Debt (Nested Subquery)
```sql
SELECT * FROM players 
WHERE debt = (
    SELECT MIN(debt) FROM players 
    WHERE player_id IN (SELECT player_id FROM players)
);
```

---

## 4. SET OPERATIONS

### 4.1 UNION - Males OR High Debt Females
```sql
SELECT * FROM (
    (SELECT DISTINCT * FROM players WHERE gender = 'Male')
    UNION
    (SELECT DISTINCT * FROM players WHERE gender = 'Female' AND debt > 50000000)
) AS union_result 
ORDER BY player_number ASC;
```
**Explanation**: Combines two result sets - all males and females with debt over 50M. UNION removes duplicates automatically.

### 4.2 INTERSECT - Young AND Low Debt (Simulated)
```sql
SELECT * FROM players 
WHERE player_id IN (SELECT player_id FROM players WHERE age < 30)
  AND player_id IN (SELECT player_id FROM players WHERE debt < 10000000)
ORDER BY age ASC;
```
**Explanation**: MySQL doesn't have native INTERSECT, so we simulate it using multiple IN clauses - players who are both young AND have low debt.

### 4.3 MINUS/EXCEPT - Alive but NOT Young (Simulated)
```sql
SELECT * FROM players 
WHERE status = 'alive' 
  AND player_id NOT IN (SELECT player_id FROM players WHERE age < 25)
ORDER BY age DESC;
```
**Explanation**: MySQL doesn't have native MINUS/EXCEPT, so we simulate it using NOT IN - alive players excluding those under 25.

---

## 5. IN / NOT IN QUERIES

### 5.1 IN - Top 3 Nationalities (Most Players)
```sql
SELECT * FROM players 
WHERE nationality IN (
    SELECT nationality FROM players 
    GROUP BY nationality 
    ORDER BY COUNT(*) DESC 
    LIMIT 3
)
ORDER BY nationality ASC;
```
**Explanation**: Subquery finds top 3 nationalities by player count, outer query selects all players from those nationalities.

### 5.2 NOT IN - Exclude Rare Nationalities (<5 Players)
```sql
SELECT * FROM players 
WHERE nationality NOT IN (
    SELECT nationality FROM players 
    GROUP BY nationality 
    HAVING COUNT(*) < 5
)
ORDER BY nationality ASC;
```
**Explanation**: Subquery with HAVING finds rare nationalities (fewer than 5 players), outer query excludes those players.

---

## 6. EXISTS / NOT EXISTS

### 6.1 EXISTS - Players with Similar Debt (±5M)
```sql
SELECT p1.* FROM players p1 
WHERE EXISTS (
    SELECT 1 FROM players p2 
    WHERE p2.player_id != p1.player_id 
      AND p2.debt BETWEEN p1.debt - 5000000 AND p1.debt + 5000000
)
ORDER BY debt ASC;
```
**Explanation**: For each player, checks if there EXISTS another player with similar debt (within 5 million). Demonstrates correlated subquery.

---

## 7. SORTING (ORDER BY)

### 7.1 Available Sort Options
```sql
-- Player Number Ascending/Descending
ORDER BY player_number ASC;
ORDER BY player_number DESC;

-- Name Alphabetical
ORDER BY name ASC;    -- A-Z
ORDER BY name DESC;   -- Z-A

-- Age
ORDER BY age ASC;     -- Youngest first
ORDER BY age DESC;    -- Oldest first

-- Debt
ORDER BY debt ASC;    -- Lowest first
ORDER BY debt DESC;   -- Highest first

-- Nationality Alphabetical
ORDER BY nationality ASC;
```

---

## 8. LIMIT - Restrict Results

```sql
-- Limit to specific number of results
SELECT * FROM players ORDER BY player_number ASC LIMIT 10;
SELECT * FROM players ORDER BY debt DESC LIMIT 50;
SELECT * FROM players ORDER BY age ASC LIMIT 100;
```

---

## 9. COMBINED EXAMPLE QUERIES

### 9.1 Complex Multi-Condition Search
```sql
SELECT DISTINCT * FROM players 
WHERE name LIKE '%Seong%'
  AND gender = 'Male'
  AND age BETWEEN 30 AND 40
  AND nationality LIKE '%Korea%'
  AND debt > 10000000
  AND status = 'alive'
  AND player_id IN (
      SELECT player_id FROM players 
      WHERE debt > (SELECT AVG(debt) FROM players)
  )
ORDER BY debt DESC 
LIMIT 20;
```
**Explanation**: Combines LIKE, exact match, BETWEEN, subquery with AVG, ORDER BY, and LIMIT.

### 9.2 Union with Additional Filters
```sql
SELECT * FROM (
    (SELECT DISTINCT * FROM players WHERE gender = 'Male' AND age > 30)
    UNION
    (SELECT DISTINCT * FROM players WHERE gender = 'Female' AND debt > 50000000)
) AS union_result 
WHERE nationality LIKE '%Korea%'
ORDER BY debt DESC 
LIMIT 50;
```

---

## 10. SQL CONCEPTS DEMONSTRATED

### Lab 05 - Subqueries & Set Operations
✅ **Subqueries**:
- Simple subqueries with IN
- Nested subqueries (2+ levels)
- Correlated subqueries with EXISTS
- Subqueries with aggregate functions (AVG, MAX, MIN)

✅ **Set Operations**:
- UNION (combine two result sets)
- INTERSECT simulation (multiple IN clauses)
- MINUS/EXCEPT simulation (NOT IN)

✅ **Advanced Filtering**:
- IN operator with subquery
- NOT IN operator with subquery
- EXISTS with correlated subquery
- LIKE pattern matching
- BETWEEN for ranges
- DISTINCT for unique results

✅ **Sorting & Limiting**:
- ORDER BY with ASC/DESC
- LIMIT for pagination
- Multiple sort criteria

### Additional SQL Features Used
- **Aggregate Functions**: AVG(), MAX(), MIN(), COUNT()
- **GROUP BY**: Used in subqueries for set operations
- **HAVING**: Used with GROUP BY in subqueries
- **Comparison Operators**: =, <, >, <=, >=, !=, BETWEEN
- **Logical Operators**: AND, OR
- **Pattern Matching**: LIKE with % wildcard

---

## 11. Usage Examples

### Search for Korean players with high debt:
```
Player Name: (empty)
Gender: All
Age Range: (empty)
Nationality: %Korea%
Debt Range: Min: 50000000
Status: alive
Special Filter: Above Average Debt
Sort By: Debt (Highest First)
Limit: 20
```

### Find young players who survived:
```
Advanced Query: INTERSECT: Young (<30) AND Low Debt (<10M)
Status: alive
Sort By: Age (Youngest First)
Limit: 50
```

### Players from top nationalities only:
```
Advanced Query: IN: Top 3 Nationalities
Sort By: Nationality (A-Z)
Limit: 100
```

---

## Database Schema Reference

```sql
CREATE TABLE players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    player_number VARCHAR(3) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender VARCHAR(10) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    debt BIGINT NOT NULL,
    status VARCHAR(20) DEFAULT 'alive'
);
```

---

**End of Documentation**
