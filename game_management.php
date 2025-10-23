<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Management - SquidSphere</title>
    <link rel="stylesheet" href="players.css">
    <link rel="stylesheet" href="game_management.css">
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
            <li><a href="games.php" class="active">Gameplay</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="staff_visual.php">Staff</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1 class="title">Game Management & Elimination Control</h1>
        
        <!-- Current Status -->
        <div class="status-panel">
            <h2>Current Game Status</h2>
            <div class="status-grid">
                <div class="status-card alive-card">
                    <div class="status-icon">👥</div>
                    <div class="status-number" id="aliveCount">456</div>
                    <div class="status-label">Alive Players</div>
                </div>
                <div class="status-card eliminated-card">
                    <div class="status-icon">💀</div>
                    <div class="status-number" id="eliminatedCount">0</div>
                    <div class="status-label">Eliminated</div>
                </div>
                <div class="status-card winner-card">
                    <div class="status-icon">🏆</div>
                    <div class="status-number" id="winnerCount">0</div>
                    <div class="status-label">Winner</div>
                </div>
                <div class="status-card prize-card">
                    <div class="status-icon">💰</div>
                    <div class="status-number" id="prizeMoney">₩0</div>
                    <div class="status-label">Prize Money</div>
                </div>
            </div>
            <div class="current-round" id="currentRound">
                Status: Ready to Start
            </div>
        </div>

        <!-- Game Rounds -->
        <div class="rounds-section">
            <h2>Game Rounds - Elimination Controls</h2>
            
            <!-- Round 1: Red Light Green Light -->
            <div class="round-card">
                <div class="round-header">
                    <div class="round-number">Round 1</div>
                    <div class="round-title">
                        <h3>🚦 Red Light, Green Light</h3>
                        <p class="round-rule">Eliminates: ~55% (More than half of players)</p>
                    </div>
                </div>
                <div class="round-actions">
                    <button class="btn-play" onclick="window.location.href='game_red_light.php'">▶ Play Game</button>
                    <button class="btn-eliminate" onclick="eliminateGame('red_light')">⚡ Auto Eliminate</button>
                </div>
                <div class="round-status" id="round1Status">Not Started</div>
            </div>

            <!-- Round 2: Honeycomb -->
            <div class="round-card">
                <div class="round-header">
                    <div class="round-number">Round 2</div>
                    <div class="round-title">
                        <h3>🍪 Honeycomb</h3>
                        <p class="round-rule">Eliminates: ~30% of remaining players</p>
                    </div>
                </div>
                <div class="round-actions">
                    <button class="btn-play" onclick="window.location.href='game_honeycomb.php'">▶ Play Game</button>
                    <button class="btn-eliminate" onclick="eliminateGame('honeycomb')">⚡ Auto Eliminate</button>
                </div>
                <div class="round-status" id="round2Status">Not Started</div>
            </div>

            <!-- Round 3: Tug of War -->
            <div class="round-card">
                <div class="round-header">
                    <div class="round-number">Round 3</div>
                    <div class="round-title">
                        <h3>🪢 Tug of War</h3>
                        <p class="round-rule">Eliminates: 50% (Half of remaining players)</p>
                    </div>
                </div>
                <div class="round-actions">
                    <button class="btn-play" onclick="window.location.href='game_tug_of_war.php'">▶ Play Game</button>
                    <button class="btn-eliminate" onclick="eliminateGame('tug_of_war')">⚡ Auto Eliminate</button>
                </div>
                <div class="round-status" id="round3Status">Not Started</div>
            </div>

            <!-- Round 4: Marbles -->
            <div class="round-card">
                <div class="round-header">
                    <div class="round-number">Round 4</div>
                    <div class="round-title">
                        <h3>🎯 Marbles</h3>
                        <p class="round-rule">Eliminates: 50% (Half of remaining players)</p>
                    </div>
                </div>
                <div class="round-actions">
                    <button class="btn-play" onclick="window.location.href='game_marbles.php'">▶ Play Game</button>
                    <button class="btn-eliminate" onclick="eliminateGame('marbles')">⚡ Auto Eliminate</button>
                </div>
                <div class="round-status" id="round4Status">Not Started</div>
            </div>

            <!-- Round 5: Glass Bridge -->
            <div class="round-card">
                <div class="round-header">
                    <div class="round-number">Round 5</div>
                    <div class="round-title">
                        <h3>🌉 Glass Bridge</h3>
                        <p class="round-rule">Eliminates: All but 2 players (Only 2 survivors)</p>
                    </div>
                </div>
                <div class="round-actions">
                    <button class="btn-play" onclick="window.location.href='game_glass_bridge.php'">▶ Play Game</button>
                    <button class="btn-eliminate" onclick="eliminateGame('glass_bridge')">⚡ Auto Eliminate</button>
                </div>
                <div class="round-status" id="round5Status">Not Started</div>
            </div>

            <!-- Round 6: Squid Game (Final) -->
            <div class="round-card final-round">
                <div class="round-header">
                    <div class="round-number">Final</div>
                    <div class="round-title">
                        <h3>🦑 Squid Game</h3>
                        <p class="round-rule">Final Round: Only 1 WINNER! 🏆</p>
                    </div>
                </div>
                <div class="round-actions">
                    <button class="btn-play" onclick="window.location.href='game_squid_game.php'">▶ Play Game</button>
                    <button class="btn-eliminate final" onclick="eliminateGame('squid_game')">⚡ Determine Winner</button>
                </div>
                <div class="round-status" id="round6Status">Not Started</div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="control-panel">
            <h3>Game Controls</h3>
            <div class="control-buttons">
                <button class="btn-reset" onclick="resetGame()">🔄 Reset All Players</button>
                <button class="btn-refresh" onclick="loadGameStatus()">♻️ Refresh Status</button>
                <button class="btn-view" onclick="window.location.href='players.php'">👥 View All Players</button>
            </div>
        </div>

        <!-- Winner Announcement -->
        <div class="winner-panel" id="winnerPanel" style="display: none;">
            <div class="winner-content">
                <h2>🎉 WE HAVE A WINNER! 🎉</h2>
                <div class="winner-info" id="winnerInfo"></div>
            </div>
        </div>
    </div>

    <script src="game_management.js"></script>
</body>
</html>
