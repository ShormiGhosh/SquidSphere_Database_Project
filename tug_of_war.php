<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tug of War - Team Formation</title>
    <link rel="stylesheet" href="players.css">
    <link rel="stylesheet" href="tug_of_war.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-logo">
            <span class="logo-text">SquidSphere</span>
        </div>
        <ul class="nav-links">
            <li><a href="players.php">Players</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="games.php">Gameplay</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="staff_visual.php">Staff</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="game-header">
            <h1 class="title">🪢 Round 3: Tug of War 🪢</h1>
            <p class="game-description">Form two teams and compete in the ultimate test of strength and strategy!</p>
        </div>

        <!-- Team Formation Section -->
        <div id="formationSection" class="formation-section">
            <h2>🎯 Select Team Formation Strategy</h2>
            <div class="strategy-selector">
                <div class="strategy-card">
                    <input type="radio" name="strategy" id="strategyAge" value="age">
                    <label for="strategyAge">
                        <div class="strategy-icon">👶👴</div>
                        <h3>Age-Based</h3>
                        <p>Young vs Old</p>
                        <span class="strategy-desc">Divide by median age - youth vs experience</span>
                    </label>
                </div>

                <div class="strategy-card">
                    <input type="radio" name="strategy" id="strategyDebt" value="debt" checked>
                    <label for="strategyDebt">
                        <div class="strategy-icon">💰💸</div>
                        <h3>Debt-Based</h3>
                        <p>High Debt vs Low Debt</p>
                        <span class="strategy-desc">Desperate vs less desperate players</span>
                    </label>
                </div>

                <div class="strategy-card">
                    <input type="radio" name="strategy" id="strategyNumber" value="player_number">
                    <label for="strategyNumber">
                        <div class="strategy-icon">🔢</div>
                        <h3>Player Number</h3>
                        <p>Odd vs Even</p>
                        <span class="strategy-desc">Perfectly balanced random split</span>
                    </label>
                </div>

                <div class="strategy-card">
                    <input type="radio" name="strategy" id="strategyNationality" value="nationality">
                    <label for="strategyNationality">
                        <div class="strategy-icon">🇰🇷🌍</div>
                        <h3>Nationality</h3>
                        <p>Korean vs Foreign</p>
                        <span class="strategy-desc">Home team vs international players</span>
                    </label>
                </div>
            </div>

            <button class="btn-primary" onclick="formTeams()">
                ✨ Form Teams
            </button>
        </div>

        <!-- Teams Display Section -->
        <div id="teamsSection" class="teams-section" style="display: none;">
            <div class="strategy-info">
                <h3>📋 Formation Strategy</h3>
                <p id="strategyUsed"></p>
            </div>

            <div class="teams-container">
                <!-- Team A -->
                <div class="team-panel team-a">
                    <div class="team-header">
                        <h2>⚔️ Team A</h2>
                        <div class="team-count">
                            <span id="teamACount">0</span> Players
                        </div>
                    </div>
                    <div class="team-players" id="teamAPlayers">
                        <!-- Team A players will be loaded here -->
                    </div>
                    <button class="btn-eliminate team-a-btn" onclick="eliminateTeam('Team A')">
                        ❌ Team A Lost - Eliminate All
                    </button>
                </div>

                <!-- VS Divider -->
                <div class="vs-divider">
                    <div class="vs-circle">VS</div>
                    <div class="rope-line"></div>
                </div>

                <!-- Team B -->
                <div class="team-panel team-b">
                    <div class="team-header">
                        <h2>🛡️ Team B</h2>
                        <div class="team-count">
                            <span id="teamBCount">0</span> Players
                        </div>
                    </div>
                    <div class="team-players" id="teamBPlayers">
                        <!-- Team B players will be loaded here -->
                    </div>
                    <button class="btn-eliminate team-b-btn" onclick="eliminateTeam('Team B')">
                        ❌ Team B Lost - Eliminate All
                    </button>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-secondary" onclick="resetFormation()">
                    🔄 Reset & Choose Different Strategy
                </button>
            </div>
        </div>

        <!-- Result Section -->
        <div id="resultSection" class="result-section" style="display: none;">
            <div class="result-card">
                <h2 id="resultTitle"></h2>
                <div class="result-stats">
                    <div class="stat-item">
                        <span class="stat-label">Eliminated:</span>
                        <span class="stat-value" id="resultEliminated">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Survivors:</span>
                        <span class="stat-value" id="resultSurvivors">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Prize Money:</span>
                        <span class="stat-value" id="resultPrize">₩0</span>
                    </div>
                </div>
                <button class="btn-primary" onclick="window.location.href='games.php'">
                    ➡️ Back to Games
                </button>
            </div>
        </div>
    </div>

    <script src="tug_of_war.js"></script>
</body>
</html>
