# Advanced Search Feature - Quick Reference Card

## 🎯 Search Page Layout

```
┌─────────────────────────────────────────────────────────────────┐
│                     SQUIDSPHERE NAVBAR                          │
│  Players | Search | Gameplay | Dashboard | Staff               │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│              ADVANCED PLAYER SEARCH                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  BASIC SEARCH                                                   │
│  ┌──────────────────┐  ┌──────────────────┐                   │
│  │ Player Number    │  │ Name (LIKE)      │                   │
│  │ e.g., 001, %5%   │  │ e.g., Seong%     │                   │
│  └──────────────────┘  └──────────────────┘                   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  DEMOGRAPHICS                                                   │
│  ┌────────┐  ┌─────────────┐  ┌──────────────┐               │
│  │ Gender │  │ Age Range   │  │ Nationality  │               │
│  │ [All ▼]│  │ [Min] [Max] │  │ %Korea%      │               │
│  └────────┘  └─────────────┘  └──────────────┘               │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  FINANCIAL STATUS                                               │
│  ┌─────────────┐  ┌────────┐                                  │
│  │ Debt Range  │  │ Status │                                  │
│  │ [Min] [Max] │  │ [All ▼]│                                  │
│  └─────────────┘  └────────┘                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  ADVANCED QUERIES (SUBQUERIES & SET OPERATIONS)                │
│  ┌────────────────────────────────────────────────────────────┐│
│  │ Special Filters                                   [None ▼]││
│  │ ┌─ Subqueries (IN, NOT IN) ────────────────────────────┐  ││
│  │ │ • Players with debt ABOVE AVERAGE (Subquery)        │  ││
│  │ │ • Players with debt BELOW AVERAGE (Subquery)        │  ││
│  │ │ • Players OLDER than average (Subquery)             │  ││
│  │ │ • Players YOUNGER than average (Subquery)           │  ││
│  │ │ • Players with MAXIMUM debt (Nested Subquery)       │  ││
│  │ │ • Players with MINIMUM debt (Nested Subquery)       │  ││
│  │ └────────────────────────────────────────────────────────┘  ││
│  │ ┌─ Set Operations ──────────────────────────────────────┐  ││
│  │ │ • UNION: Males OR High Debt Females (>50M)          │  ││
│  │ │ • INTERSECT: Young (<30) AND Low Debt (<10M)        │  ││
│  │ │ • MINUS: Alive but NOT Young (<25)                  │  ││
│  │ └────────────────────────────────────────────────────────┘  ││
│  │ ┌─ Complex Conditions ──────────────────────────────────┐  ││
│  │ │ • IN: Top 3 Nationalities (Most Players)            │  ││
│  │ │ • NOT IN: Rare Nationalities (<5 Players)           │  ││
│  │ │ • EXISTS: Players with Similar Debt (±5M)           │  ││
│  │ └────────────────────────────────────────────────────────┘  ││
│  └────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  SORTING                                                        │
│  ┌──────────────────────┐  ┌──────────────┐                   │
│  │ Sort By              │  │ Limit Results│                   │
│  │ [Player Number ASC ▼]│  │ [100]        │                   │
│  └──────────────────────┘  └──────────────┘                   │
└─────────────────────────────────────────────────────────────────┘

│  ┌──────────────────┐  ┌──────────────────┐                   │
│  │ 🔍 Search Players│  │ 🔄 Reset Filters │                   │
│  └──────────────────┘  └──────────────────┘                   │

┌─────────────────────────────────────────────────────────────────┐
│  SEARCH RESULTS: 25 Players                                    │
│                                                                 │
│  📊 SQL Query Used (25 results):                               │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ SELECT DISTINCT * FROM players                            │ │
│  │ WHERE debt > (SELECT AVG(debt) FROM players)              │ │
│  │ ORDER BY debt DESC LIMIT 100                              │ │
│  └───────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘

┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│   001    │ │   067    │ │   218    │ │   456    │
│ ──────── │ │ ──────── │ │ ──────── │ │ ──────── │
│ Gi-hun   │ │ Sang-woo │ │ Sae-byeok│ │ Ali      │
│ Age: 47  │ │ Age: 46  │ │ Age: 27  │ │ Age: 33  │
│ Male     │ │ Male     │ │ Female   │ │ Male     │
│ Korean   │ │ Korean   │ │ N.Korean │ │ Pakistani│
│ ₩255M    │ │ ₩6,000M  │ │ ₩27M     │ │ ₩1M      │
│ ALIVE    │ │ ALIVE    │ │ ALIVE    │ │ ALIVE    │
└──────────┘ └──────────┘ └──────────┘ └──────────┘
```

---

## 🔑 Quick Access Patterns

### Pattern Matching with LIKE
```
Search Term         Matches
───────────────────────────────────────
001                 Exact: 001
%5%                 Contains: 5 (015, 056, 250, etc.)
01%                 Starts: 01 (010, 011, 012, etc.)
%5                  Ends: 5 (005, 015, 025, etc.)
Seong%              Names starting with "Seong"
%Sang%              Names containing "Sang"
%Korea%             Nationalities with "Korea"
```

---

## 🎓 SQL Concepts Quick Reference

### Subqueries
```sql
IN          -- Player in result set
NOT IN      -- Player not in result set
EXISTS      -- At least one matching row exists
```

### Set Operations
```sql
UNION       -- Combine two result sets (remove duplicates)
INTERSECT   -- Common rows in both sets (simulated)
MINUS       -- Rows in first but not second (simulated)
```

### Aggregates in Subqueries
```sql
AVG()       -- Average value
MAX()       -- Maximum value
MIN()       -- Minimum value
COUNT()     -- Count rows
```

---

## 💡 Common Search Scenarios

| Goal | Settings |
|------|----------|
| **Rich Players** | Advanced: "Above Average Debt", Sort: "Debt DESC" |
| **Young Survivors** | Status: alive, Age Max: 30, Sort: "Age ASC" |
| **Korean Players** | Nationality: "%Korea%", Sort: "Name ASC" |
| **Major Nationalities** | Advanced: "IN: Top 3 Nationalities" |
| **Similar Debts** | Advanced: "EXISTS: Similar Debt (±5M)" |
| **Rare Groups** | Advanced: "NOT IN: Rare Nationalities" |

---

## 📱 Mobile View

On smaller screens:
- Filters stack vertically
- Full-width filter groups
- Buttons stack vertically
- Tiles display in single column

---

## ⚡ Performance Tips

1. Use LIMIT to restrict results (default: 100)
2. Combine filters to narrow search
3. Advanced queries may take longer (subqueries)
4. Sort by indexed columns (player_number) is faster

---

## 🎨 Visual Indicators

- **Active Filter**: Filled input field
- **SQL Keywords**: Pink highlight (#ff69b4)
- **Eliminated Players**: 30% opacity + grayscale
- **Alive Players**: Full opacity + color
- **Result Count**: Large pink number

---

**URL**: http://localhost/SquidSphere/search.php
**Documentation**: database/SEARCH_SQL_DOCUMENTATION.md
**User Guide**: database/SEARCH_USER_GUIDE.md
