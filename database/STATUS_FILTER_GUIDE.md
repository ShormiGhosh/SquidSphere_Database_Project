# Status Filter (Alive vs Eliminated) - Complete Guide

## ✅ Status Filter is FULLY IMPLEMENTED

The status filter is working in the SquidSphere search feature! Here's how it works:

---

## 🎯 Basic Status Filtering

### Using the Status Dropdown

**Location**: Search Panel → Financial Status Section → Status dropdown

**Options**:
- **All** - Show both alive and eliminated players
- **Alive** - Show only surviving players
- **Eliminated** - Show only eliminated players

### How to Use:
1. Go to `http://localhost/SquidSphere/search.php`
2. Scroll to "Financial Status" section
3. Select status from dropdown:
   - `alive` - Shows only living players
   - `eliminated` - Shows only eliminated players
   - Leave empty for all players

---

## 💻 Backend Implementation

### In `api/search_players.php` (Lines 206-209):

```php
// Status filter
if (!empty($status)) {
    $status = addslashes($status);
    $conditions[] = "status = '$status'";
}
```

### Generated SQL:
```sql
-- Show only alive players
SELECT DISTINCT * FROM players 
WHERE status = 'alive'
ORDER BY player_number ASC;

-- Show only eliminated players
SELECT DISTINCT * FROM players 
WHERE status = 'eliminated'
ORDER BY player_number ASC;
```

---

## 🔍 Advanced Status-Based Queries

### NEW! Status-Focused Advanced Options

I've added **3 new advanced query options** that specifically showcase alive vs eliminated filtering:

#### 1. **Alive Players with High Debt (>30M)**
```sql
SELECT DISTINCT * FROM players 
WHERE status = 'alive' AND debt > 30000000
ORDER BY debt DESC;
```
**Use Case**: Find survivors who are still heavily in debt

#### 2. **Eliminated Young Players (<35)**
```sql
SELECT DISTINCT * FROM players 
WHERE status = 'eliminated' AND age < 35
ORDER BY age ASC;
```
**Use Case**: See which young players didn't survive

#### 3. **UNION: Alive Rich OR Eliminated Young**
```sql
(SELECT DISTINCT * FROM players WHERE status = 'alive' AND debt > 50000000)
UNION
(SELECT DISTINCT * FROM players WHERE status = 'eliminated' AND age < 30)
ORDER BY player_number ASC;
```
**Use Case**: Set operation combining alive rich players with eliminated young players

---

## 🎨 Visual Differences

### How Status Appears in Results:

#### Alive Players:
- ✅ Full opacity (100%)
- ✅ Full color
- ✅ Green "ALIVE" badge
- ✅ Clear, vibrant appearance

#### Eliminated Players:
- 💀 Reduced opacity (30%)
- 💀 Grayscale filter
- 💀 Red "ELIMINATED" badge
- 💀 Faded, desaturated appearance (like in Squid Game)

### CSS Implementation:
```css
/* In players.css */
.status-eliminated {
    opacity: 0.3;
    filter: grayscale(100%);
}
```

---

## 📊 Example Search Scenarios

### Scenario 1: Find All Survivors
**Steps:**
1. Status: Select `alive`
2. Sort By: `Player Number (ASC)`
3. Click "🔍 Search Players"

**Result**: Shows all living players in order

---

### Scenario 2: Find Eliminated Players with High Debt
**Steps:**
1. Status: Select `eliminated`
2. Debt Range Min: `20000000`
3. Sort By: `Debt (Highest First)`
4. Click "🔍 Search Players"

**SQL Generated:**
```sql
SELECT DISTINCT * FROM players 
WHERE status = 'eliminated' AND debt >= 20000000
ORDER BY debt DESC 
LIMIT 100;
```

---

### Scenario 3: Young Survivors Only
**Steps:**
1. Status: Select `alive`
2. Age Range Max: `30`
3. Sort By: `Age (Youngest First)`
4. Click "🔍 Search Players"

**SQL Generated:**
```sql
SELECT DISTINCT * FROM players 
WHERE status = 'alive' AND age <= 30
ORDER BY age ASC 
LIMIT 100;
```

---

### Scenario 4: Compare Alive vs Eliminated (UNION)
**Steps:**
1. Advanced Query: Select `UNION: Alive Rich OR Eliminated Young`
2. Click "🔍 Search Players"

**SQL Generated:**
```sql
(SELECT DISTINCT * FROM players WHERE status = 'alive' AND debt > 50000000)
UNION
(SELECT DISTINCT * FROM players WHERE status = 'eliminated' AND age < 30)
ORDER BY player_number ASC 
LIMIT 100;
```

---

## 🔄 Combining Status with Other Filters

### Example: Korean Survivors with High Debt
```
Status: alive
Nationality: %Korea%
Debt Min: 30000000
Sort By: Debt (Highest First)
```

**Generated SQL:**
```sql
SELECT DISTINCT * FROM players 
WHERE status = 'alive' 
  AND nationality LIKE '%Korea%' 
  AND debt >= 30000000
ORDER BY debt DESC 
LIMIT 100;
```

### Example: Eliminated Males Under 40
```
Status: eliminated
Gender: Male
Age Max: 40
Sort By: Age (Youngest First)
```

**Generated SQL:**
```sql
SELECT DISTINCT * FROM players 
WHERE status = 'eliminated' 
  AND gender = 'Male' 
  AND age <= 40
ORDER BY age ASC 
LIMIT 100;
```

---

## ✨ Status in Advanced Queries

### Existing Advanced Queries That Use Status:

#### **MINUS: Alive but NOT Young (<25)**
```sql
SELECT * FROM players 
WHERE status = 'alive' 
  AND player_id NOT IN (
      SELECT player_id FROM players WHERE age < 25
  )
ORDER BY age DESC;
```

#### **Can Be Combined With Status Filter:**
- Use Status dropdown with any advanced query
- Status filter works alongside all other filters
- Creates compound conditions with AND logic

---

## 📈 Status Statistics

### Check Dashboard for Overall Stats:
- Total alive players
- Total eliminated players
- Prize money accumulated (₩100 per eliminated player)

**Dashboard URL**: `http://localhost/SquidSphere/dashboard.php`

---

## 🧪 Testing Status Filter

### Test Case 1: Only Alive
1. Status: `alive`
2. Search
3. Verify: All results show "ALIVE" badge
4. Verify: All tiles have full opacity and color

### Test Case 2: Only Eliminated
1. Status: `eliminated`
2. Search
3. Verify: All results show "ELIMINATED" badge
4. Verify: All tiles have 30% opacity and grayscale

### Test Case 3: Combined Filters
1. Status: `alive`
2. Gender: `Female`
3. Age Max: `35`
4. Search
5. Verify: Only young female survivors appear

### Test Case 4: Reset
1. Set status to `alive`
2. Search
3. Click "🔄 Reset Filters"
4. Verify: Status returns to "All"
5. Search shows both alive and eliminated

---

## 🎓 SQL Learning: Status Field

### Database Schema:
```sql
CREATE TABLE players (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    player_number VARCHAR(3) NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender VARCHAR(10) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    debt BIGINT NOT NULL,
    status VARCHAR(20) DEFAULT 'alive'  -- ← Status field
);
```

### Possible Values:
- `'alive'` - Player is still in the game
- `'eliminated'` - Player has been eliminated

### SQL Operations on Status:

#### Exact Match:
```sql
WHERE status = 'alive'
WHERE status = 'eliminated'
```

#### Count by Status:
```sql
SELECT status, COUNT(*) as count 
FROM players 
GROUP BY status;
```

#### Conditional:
```sql
WHERE status = 'alive' AND debt > 10000000
```

#### NOT Equal:
```sql
WHERE status != 'eliminated'  -- Same as status = 'alive'
```

---

## 🎯 Summary

### Status Filter Features:
✅ **Basic Dropdown Filter** - Select alive/eliminated/all  
✅ **Works with All Filters** - Combines with age, debt, nationality, etc.  
✅ **Visual Indicators** - Eliminated players appear faded and grayscale  
✅ **Advanced Queries** - 3 new status-focused options  
✅ **Set Operations** - UNION queries comparing alive vs eliminated  
✅ **SQL Display** - See exact query with status conditions  
✅ **Reset Function** - Clear status filter with reset button  

### Status Filter SQL Concepts:
- ✅ Exact match filtering (`WHERE status = 'alive'`)
- ✅ Combining conditions (`status = 'alive' AND age < 30`)
- ✅ GROUP BY status (in Dashboard)
- ✅ COUNT by status (in Dashboard)
- ✅ Status in UNION queries
- ✅ Status in complex WHERE clauses

---

## 🚀 Quick Access

**Search Page**: `http://localhost/SquidSphere/search.php`  
**Status Dropdown**: Financial Status section  
**New Advanced Options**: "Status-Based Queries" optgroup  

---

**Status Filter: ✅ FULLY FUNCTIONAL**  
**Visual Styling: ✅ IMPLEMENTED**  
**Backend Logic: ✅ WORKING**  
**Advanced Queries: ✅ ENHANCED**

---

Last Updated: October 23, 2025
