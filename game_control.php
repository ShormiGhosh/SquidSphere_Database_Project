<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Control - SquidSphere</title>
    <link rel="stylesheet" href="players.css">
    <link rel="stylesheet" href="game_control.css">
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
            <li><a href="game_control.php" class="active">Game Control</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="staff_visual.php">Staff</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1 class="title">🎮 Game Control Center</h1>
        
        <!-- Game Status Dashboard -->
        <div class="status-dashboard">
            <div class="status-card alive-card">
                <div class="status-icon">👥</div>
                <div class="status-value" id="aliveCount">456</div>
                <div class="status-label">Alive Players</div>
            </div>
            
            <div class="status-card eliminated-card">
                <div class="status-icon">💀</div>
                <div class="status-value" id="eliminatedCount">0</div>
                <div class="status-label">Eliminated</div>
            </div>
            
            <div class="status-card prize-card">
                <div class="status-icon">💰</div>
                <div class="status-value" id="prizeMoney">₩0</div>
                <div class="status-label">Prize Money</div>
            </div>
            
            <div class="status-card phase-card">
                <div class="status-icon">🎯</div>
                <div class="status-value" id="gamePhase">Not Started</div>
                <div class="status-label">Current Phase</div>
            </div>
        </div>

        <!-- Winner Display -->
        <div id="winnerDisplay" class="winner-display" style="display: none;">
            <h2>🏆 Winner!</h2>
            <div id="winnerInfo"></div>
        </div>

        <!-- Game Round Controls -->
        <div class="game-rounds">
            <h2>Game Rounds</h2>
            
            <!-- Round 1: Red Light, Green Light -->
            <div class="round-card">
                <div class="round-header">
                    <h3>🚦 Round 1: Red Light, Green Light</h3>
                    <span class="round-number">Round 1/6</span>
                </div>
                <p class="round-description">
                    More than half of players will be eliminated. Random elimination of ~250 players.
                </p>
                <div class="round-controls">
                    <div class="input-group">
                        <label>Players to Eliminate:</label>
                        <input type="number" id="round1Eliminate" value="250" min="1" max="456">
                    </div>
                    <button class="eliminate-btn" onclick="eliminatePlayers('Red Light Green Light', 'round1Eliminate')">
                        ⚡ Eliminate Players
                    </button>
                </div>
            </div>

            <!-- Round 2: Honeycomb -->
            <div class="round-card">
                <div class="round-header">
                    <h3>🍪 Round 2: Honeycomb</h3>
                    <span class="round-number">Round 2/6</span>
                </div>
                <p class="round-description">
                    Eliminate remaining players. Recommended: ~100 players.
                </p>
                <div class="round-controls">
                    <div class="input-group">
                        <label>Players to Eliminate:</label>
                        <input type="number" id="round2Eliminate" value="100" min="1">
                    </div>
                    <button class="eliminate-btn" onclick="eliminatePlayers('Honeycomb', 'round2Eliminate')">
                        ⚡ Eliminate Players
                    </button>
                </div>
            </div>

            <!-- Round 3: Tug of War -->
            <div class="round-card">
                <div class="round-header">
                    <h3>🪢 Round 3: Tug of War</h3>
                    <span class="round-number">Round 3/6</span>
                </div>
                <p class="round-description">
                    Team-based elimination! Players divided into two teams. The losing team is eliminated.
                </p>
                <div class="round-controls">
                    <button class="eliminate-btn" onclick="window.location.href='tug_of_war.php'" style="width: 100%;">
                        🪢 Go to Tug of War Game
                    </button>
                </div>
            </div>

            <!-- Round 4: Marbles -->
            <div class="round-card">
                <div class="round-header">
                    <h3>🔮 Round 4: Marbles</h3>
                    <span class="round-number">Round 4/6</span>
                </div>
                <p class="round-description">
                    Half of remaining players eliminated. Recommended: ~25 players.
                </p>
                <div class="round-controls">
                    <div class="input-group">
                        <label>Players to Eliminate:</label>
                        <input type="number" id="round4Eliminate" value="25" min="1">
                    </div>
                    <button class="eliminate-btn" onclick="eliminatePlayers('Marbles', 'round4Eliminate')">
                        ⚡ Eliminate Players
                    </button>
                </div>
            </div>

            <!-- Round 5: Glass Bridge -->
            <div class="round-card">
                <div class="round-header">
                    <h3>🌉 Round 5: Glass Bridge</h3>
                    <span class="round-number">Round 5/6</span>
                </div>
                <p class="round-description">
                    Eliminate until only 2 players remain for the final round.
                </p>
                <div class="round-controls">
                    <div class="input-group">
                        <label>Players to Eliminate:</label>
                        <input type="number" id="round5Eliminate" value="23" min="1">
                        <button class="auto-btn" onclick="autoCalculate(5)">Auto (Leave 2)</button>
                    </div>
                    <button class="eliminate-btn" onclick="eliminatePlayers('Glass Bridge', 'round5Eliminate')">
                        ⚡ Eliminate Players
                    </button>
                </div>
            </div>

            <!-- Round 6: Squid Game (Final) -->
            <div class="round-card final-round">
                <div class="round-header">
                    <h3>🦑 Round 6: Squid Game (Final)</h3>
                    <span class="round-number">Final Round</span>
                </div>
                <p class="round-description">
                    Final showdown between 2 players. One will be the winner!
                </p>
                <div class="round-controls">
                    <div class="input-group">
                        <label>Declare Winner:</label>
                        <button class="winner-btn" onclick="declareWinner()">
                            👑 Randomly Select Winner
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 15px;">
                ⚠️ Note: Rounds unlock progressively. Players cannot be reset - they must progress through all rounds sequentially.
            </p>
            <div class="action-buttons">
                <button class="action-btn" onclick="loadGameStatus()">
                    🔄 Refresh Status
                </button>
                <button class="action-btn" onclick="autoPlayRounds()">
                    🎲 Auto-Play Remaining Rounds
                </button>
            </div>
        </div>
    </div>

    <script src="game_control.js"></script>
</body>
</html>
