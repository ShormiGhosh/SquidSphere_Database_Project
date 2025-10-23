<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - SquidSphere</title>
    <link rel="stylesheet" href="players.css">
    <link rel="stylesheet" href="search.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-logo">
            <span class="logo-text">SquidSphere</span>
        </div>
        <ul class="nav-links">
            <li><a href="players.php">Players</a></li>
            <li><a href="search.php" class="active">Search</a></li>
            <li><a href="games.php">Gameplay</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="staff_visual.php">Staff</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1 class="title">Advanced Player Search</h1>
        
        <!-- Search Filters -->
        <div class="search-panel">
            <div class="filter-section">
                <h3>Basic Search</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Player Number (LIKE)</label>
                        <input type="text" id="playerNumber" placeholder="e.g., 001, 456, %5% (pattern)">
                    </div>
                    <div class="filter-group">
                        <label>Name (LIKE)</label>
                        <input type="text" id="playerName" placeholder="e.g., Seong%, %Sang%, Ali">
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>Demographics</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Gender</label>
                        <select id="gender">
                            <option value="">All</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Age Range</label>
                        <input type="number" id="minAge" placeholder="Min" style="width: 45%;">
                        <input type="number" id="maxAge" placeholder="Max" style="width: 45%;">
                    </div>
                    <div class="filter-group">
                        <label>Nationality (LIKE)</label>
                        <input type="text" id="nationality" placeholder="e.g., Korean, %Pakistan%">
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>Financial Status</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Debt Range</label>
                        <input type="number" id="minDebt" placeholder="Min" style="width: 45%;">
                        <input type="number" id="maxDebt" placeholder="Max" style="width: 45%;">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select id="status">
                            <option value="">All</option>
                            <option value="alive">Alive</option>
                            <option value="eliminated">Eliminated</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>Advanced Queries (Subqueries & Set Operations)</h3>
                <div class="filter-row">
                    <div class="filter-group full-width">
                        <label>Special Filters</label>
                        <select id="advancedQuery">
                            <option value="">None</option>
                            <optgroup label="Status-Based Queries">
                                <option value="alive_high_debt">Alive Players with High Debt (>30M)</option>
                                <option value="eliminated_young">Eliminated Young Players (<35)</option>
                                <option value="union_alive_eliminated">UNION: Alive Rich OR Eliminated Young</option>
                            </optgroup>
                            <optgroup label="Subqueries (IN, NOT IN)">
                                <option value="above_avg_debt">Players with debt ABOVE AVERAGE (Subquery)</option>
                                <option value="below_avg_debt">Players with debt BELOW AVERAGE (Subquery)</option>
                                <option value="above_avg_age">Players OLDER than average (Subquery)</option>
                                <option value="below_avg_age">Players YOUNGER than average (Subquery)</option>
                                <option value="max_debt">Players with MAXIMUM debt (Nested Subquery)</option>
                                <option value="min_debt">Players with MINIMUM debt (Nested Subquery)</option>
                            </optgroup>
                            <optgroup label="Set Operations">
                                <option value="union_male_female">UNION: Males OR High Debt Females (>50M)</option>
                                <option value="intersect_young_lowdebt">INTERSECT: Young (<30) AND Low Debt (<10M)</option>
                                <option value="minus_alive_young">MINUS: Alive but NOT Young (<25)</option>
                            </optgroup>
                            <optgroup label="Complex Conditions">
                                <option value="in_top_nationalities">IN: Top 3 Nationalities (Most Players)</option>
                                <option value="not_in_rare_nationalities">NOT IN: Rare Nationalities (<5 Players)</option>
                                <option value="exists_similar_debt">EXISTS: Players with Similar Debt (±5M)</option>
                            </optgroup>
                            <optgroup label="JOIN Operations">
                                <option value="equi_join">EQUI JOIN: Players with Game Participation</option>
                                <option value="right_join">RIGHT JOIN: All Games (even without players)</option>
                                <option value="cross_join">CROSS JOIN: All Player-Game Combinations (Limited)</option>
                                <option value="non_equi_join">NON-EQUI JOIN: Players with Similar Debt (±10M)</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>Sorting</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Sort By</label>
                        <select id="sortBy">
                            <option value="player_number ASC">Player Number (ASC)</option>
                            <option value="player_number DESC">Player Number (DESC)</option>
                            <option value="name ASC">Name (A-Z)</option>
                            <option value="name DESC">Name (Z-A)</option>
                            <option value="age ASC">Age (Youngest First)</option>
                            <option value="age DESC">Age (Oldest First)</option>
                            <option value="debt_amount ASC">Debt (Lowest First)</option>
                            <option value="debt_amount DESC">Debt (Highest First)</option>
                            <option value="nationality ASC">Nationality (A-Z)</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Limit Results</label>
                        <input type="number" id="limitResults" placeholder="e.g., 10, 50, 100" value="100">
                    </div>
                </div>
            </div>

            <div class="button-row">
                <button class="search-btn" onclick="searchPlayers()">🔍 Search Players</button>
                <button class="reset-btn" onclick="resetFilters()">🔄 Reset Filters</button>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <h2>Search Results: <span id="resultCount">0</span> Players</h2>
                <div id="queryInfo" class="query-info"></div>
            </div>
            <div class="players-grid" id="searchResults">
                <p class="no-results">Use filters above to search players...</p>
            </div>
        </div>
    </div>

    <script src="search.js"></script>
</body>
</html>
