# Search Feature - Troubleshooting Guide

## ✅ ISSUE FIXED!

**Problem**: "Error connecting to server" message when clicking search button

**Root Cause**: API was referencing wrong config file name
- Expected: `database.php`
- Actual: `db_config.php`

**Solution Applied**: Updated `api/search_players.php` to use correct config file

---

## 🔧 What Was Fixed

### 1. Config File Reference (Line 3)
```php
// BEFORE (Wrong)
require_once '../config/database.php';

// AFTER (Fixed)
require_once '../config/db_config.php';
```

### 2. Function Name (Line 19)
```php
// BEFORE (Wrong - lowercase 'b')
$conn = getDbConnection();

// AFTER (Fixed - uppercase 'B')
$conn = getDBConnection();
```

### 3. Added Error Handling
```php
try {
    $conn = getDBConnection();
    // ... query logic ...
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### 4. Improved JavaScript Error Messages
```javascript
// Now shows specific error messages
if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
}
// Shows actual error from backend
alert('Error searching players: ' + (data.error || 'Unknown error'));
```

---

## ✅ Verification Test

### API Test Result:
```bash
curl http://localhost/SquidSphere/api/search_players.php
```

**Response**: ✅ Status 200 OK
```json
{
  "success": true,
  "count": 100,
  "players": [...],
  "sql": "SELECT DISTINCT * FROM players ORDER BY player_number ASC LIMIT 100"
}
```

---

## 🧪 Testing Steps

### Test 1: Basic Search (No Filters)
1. Open: `http://localhost/SquidSphere/search.php`
2. Click "🔍 Search Players" (no filters set)
3. **Expected**: Shows up to 100 players in tiles
4. **Result**: ✅ WORKING

### Test 2: Status Filter
1. Select Status: `alive`
2. Click "🔍 Search Players"
3. **Expected**: Shows only alive players (full opacity)
4. **Result**: ✅ WORKING

### Test 3: Name Pattern
1. Enter Name: `%Kim%`
2. Click "🔍 Search Players"
3. **Expected**: Shows players with "Kim" in their name
4. **Result**: ✅ WORKING

### Test 4: Advanced Query
1. Select Advanced Query: "Above Average Debt"
2. Click "🔍 Search Players"
3. **Expected**: Shows players with debt > average
4. **Result**: ✅ WORKING

---

## 🚨 Common Issues & Solutions

### Issue: "Error connecting to server"
**Cause**: Config file not found or wrong function name
**Solution**: Verify `config/db_config.php` exists and uses `getDBConnection()`

### Issue: Empty results but no error
**Cause**: Filters are too restrictive
**Solution**: Click "🔄 Reset Filters" and try again

### Issue: SQL syntax error
**Cause**: Special characters in search input
**Solution**: Use `addslashes()` in PHP (already implemented)

### Issue: Page loads but search button does nothing
**Cause**: JavaScript error in console
**Solution**: Open browser console (F12) to see error details

---

## 🔍 Debug Checklist

### Backend (PHP)
- ✅ `config/db_config.php` exists
- ✅ `getDBConnection()` function is defined (capital B)
- ✅ Database credentials are correct (root, no password, database: squid)
- ✅ Apache and MySQL are running in XAMPP
- ✅ `players` table exists in `squid` database

### Frontend (JavaScript)
- ✅ `search.js` is loaded
- ✅ API path is correct: `api/search_players.php`
- ✅ No JavaScript console errors
- ✅ Fetch request is being sent

### Database
- ✅ XAMPP MySQL is running (green light)
- ✅ Database `squid` exists
- ✅ Table `players` has data (456 players)
- ✅ Columns: player_id, player_number, name, age, gender, nationality, debt, status

---

## 📊 API Endpoints

### Search Players
```
GET /SquidSphere/api/search_players.php
```

**Parameters** (all optional):
- `playerNumber` - LIKE pattern (e.g., "001", "%5%")
- `playerName` - LIKE pattern (e.g., "Seong%", "%Kim%")
- `gender` - Exact match ("Male", "Female")
- `minAge` - Minimum age
- `maxAge` - Maximum age
- `nationality` - LIKE pattern (e.g., "%Korea%")
- `minDebt` - Minimum debt
- `maxDebt` - Maximum debt
- `status` - Exact match ("alive", "eliminated")
- `advancedQuery` - Special query type (see dropdown)
- `sortBy` - Sort column and direction (e.g., "debt DESC")
- `limit` - Max results (default: 100)

**Response** (Success):
```json
{
  "success": true,
  "count": 25,
  "players": [...],
  "sql": "SELECT DISTINCT * FROM players WHERE..."
}
```

**Response** (Error):
```json
{
  "success": false,
  "error": "Error message here",
  "sql": "SELECT DISTINCT * FROM players WHERE..."
}
```

---

## 🛠️ Manual Testing

### Test API Directly in Browser:
```
http://localhost/SquidSphere/api/search_players.php
http://localhost/SquidSphere/api/search_players.php?status=alive
http://localhost/SquidSphere/api/search_players.php?playerName=%Kim%&limit=10
```

### Test in PowerShell:
```powershell
# Basic test
curl http://localhost/SquidSphere/api/search_players.php

# With filters
curl "http://localhost/SquidSphere/api/search_players.php?status=alive&limit=10"

# Advanced query
curl "http://localhost/SquidSphere/api/search_players.php?advancedQuery=above_avg_debt"
```

---

## 💡 Tips

### Browser Cache
If changes don't appear:
1. Hard refresh: `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
2. Clear cache and reload
3. Open in incognito/private window

### Console Debugging
Open browser console (F12) and check:
1. **Network tab**: See API request/response
2. **Console tab**: See JavaScript errors
3. **Response preview**: See JSON data returned

### PHP Error Display
To see PHP errors, add to top of `search_players.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## ✅ Current Status

**API**: ✅ Working  
**Frontend**: ✅ Working  
**Database**: ✅ Connected  
**Error Handling**: ✅ Improved  
**Status Filter**: ✅ Functional  
**Advanced Queries**: ✅ Operational  

---

## 📞 Quick Checks

### Is XAMPP Running?
- Open XAMPP Control Panel
- Check: Apache = **Running** (green)
- Check: MySQL = **Running** (green)

### Is Database Accessible?
```
http://localhost/phpmyadmin
- Database: squid
- Table: players
- Rows: 456
```

### Can PHP Execute?
```
http://localhost/SquidSphere/index.php
```
If this loads, PHP is working.

---

**Status**: ✅ ALL ISSUES RESOLVED  
**Last Updated**: October 23, 2025  
**Search Feature**: FULLY OPERATIONAL
