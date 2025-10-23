# SquidSphere Advanced Search - User Guide

## 🔍 How to Use the Search Feature

### Basic Search Options

#### 1. **Player Number (LIKE)**
- Search for exact number: `001`, `456`
- Search for pattern: `%5%` (contains 5), `01%` (starts with 01)

#### 2. **Name (LIKE)**
- Search for exact name: `Seong Gi-hun`
- Search for pattern: `Seong%` (starts with Seong), `%Sang%` (contains Sang)

#### 3. **Gender**
- Select: All, Male, or Female

#### 4. **Age Range**
- Set minimum and/or maximum age
- Example: Min: 25, Max: 40

#### 5. **Nationality (LIKE)**
- Search for exact: `Korean`, `Pakistani`
- Search for pattern: `%Korea%`, `%Pakistan%`

#### 6. **Debt Range**
- Set minimum and/or maximum debt
- Example: Min: 10000000, Max: 50000000

#### 7. **Status**
- Select: All, Alive, or Eliminated

---

## 🎯 Advanced Query Options

### Subqueries (IN, NOT IN)

**Above Average Debt**
- Finds players whose debt is higher than the average debt of all players
- SQL: Uses subquery with AVG() function

**Below Average Debt**
- Finds players with debt lower than average

**Above/Below Average Age**
- Similar to debt, but compares age to average age

**Maximum/Minimum Debt**
- Finds players with the exact highest or lowest debt
- SQL: Uses nested subquery with MAX()/MIN()

---

### Set Operations

**UNION: Males OR High Debt Females (>50M)**
- Combines all male players with female players who have debt over 50 million
- SQL: Uses UNION to merge two separate queries

**INTERSECT: Young (<30) AND Low Debt (<10M)**
- Finds players who are BOTH under 30 years old AND have debt under 10 million
- SQL: Simulates INTERSECT using multiple IN clauses

**MINUS: Alive but NOT Young (<25)**
- Finds alive players who are NOT under 25 years old
- SQL: Uses NOT IN to exclude young players

---

### Complex Conditions

**IN: Top 3 Nationalities (Most Players)**
- Shows only players from the 3 most common nationalities
- SQL: Subquery with GROUP BY and ORDER BY COUNT()

**NOT IN: Rare Nationalities (<5 Players)**
- Excludes players from rare nationalities (less than 5 players)
- SQL: Uses NOT IN with GROUP BY HAVING

**EXISTS: Players with Similar Debt (±5M)**
- Finds players who have at least one other player with similar debt (within 5 million)
- SQL: Correlated subquery with EXISTS

---

## 📊 Sorting Options

- **Player Number**: Ascending (001→456) or Descending (456→001)
- **Name**: Alphabetically (A→Z) or Reverse (Z→A)
- **Age**: Youngest First or Oldest First
- **Debt**: Lowest First or Highest First
- **Nationality**: Alphabetically

---

## 💡 Example Search Scenarios

### Scenario 1: Find Korean Players with High Debt
1. Nationality: `%Korea%`
2. Debt Range Min: `50000000`
3. Advanced Query: "Above Average Debt"
4. Sort By: "Debt (Highest First)"
5. Click "🔍 Search Players"

### Scenario 2: Young Survivors with Low Debt
1. Status: `alive`
2. Advanced Query: "INTERSECT: Young (<30) AND Low Debt (<10M)"
3. Sort By: "Age (Youngest First)"
4. Click "🔍 Search Players"

### Scenario 3: Players from Major Nationalities Only
1. Advanced Query: "IN: Top 3 Nationalities"
2. Sort By: "Nationality (A-Z)"
3. Limit: `100`
4. Click "🔍 Search Players"

### Scenario 4: Find Players Named "Ali"
1. Name: `%Ali%`
2. Sort By: "Player Number (ASC)"
3. Click "🔍 Search Players"

### Scenario 5: Middle-Aged Players
1. Age Range: Min: `30`, Max: `50`
2. Sort By: "Age (Youngest First)"
3. Click "🔍 Search Players"

---

## 🎨 Understanding Results

### Player Tile Display
Each player appears in a tile showing:
- **Player Number**: Large number at top
- **Name**: Player's full name
- **Age**: Player's age
- **Gender**: Male/Female
- **Nationality**: Country of origin
- **Debt**: Total debt in Korean Won (₩)
- **Status**: ALIVE or ELIMINATED

### Eliminated Players
- Appear with reduced opacity (30%)
- Grayscale filter applied
- Similar to visual style in Squid Game series

### SQL Query Display
- Shows the actual SQL query executed
- Keywords highlighted in pink
- Useful for learning SQL

---

## 🔄 Reset Filters

Click **"🔄 Reset Filters"** to clear all search criteria and start fresh.

---

## 📝 SQL Concepts Demonstrated

This search feature demonstrates:
- ✅ **LIKE** pattern matching
- ✅ **IN / NOT IN** operators
- ✅ **Subqueries** (simple and nested)
- ✅ **UNION** set operation
- ✅ **INTERSECT** simulation
- ✅ **MINUS/EXCEPT** simulation
- ✅ **EXISTS** correlated subquery
- ✅ **DISTINCT** for unique results
- ✅ **Aggregate functions** (AVG, MAX, MIN, COUNT)
- ✅ **GROUP BY** with **HAVING**
- ✅ **ORDER BY** for sorting
- ✅ **LIMIT** for pagination

---

## 🚀 Tips

1. **Use % Wildcard**: For flexible pattern matching
   - `%Korea%` - Contains "Korea"
   - `Seong%` - Starts with "Seong"
   - `%woo` - Ends with "woo"

2. **Combine Filters**: Mix basic and advanced filters for precise results

3. **Check SQL Query**: Learn SQL by viewing the generated query

4. **Adjust Limit**: Use lower limits (10-20) for quick tests, higher (100+) for comprehensive results

5. **Sort Smartly**: Choose sort order based on what you want to analyze

---

**Happy Searching! 🎯**
