<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games - SquidSphere</title>
    <link rel="stylesheet" href="games.css">
</head>
<body>
    <!-- Game Controls -->
    <div class="game-controls">
        <button class="control-btn continue-btn" onclick="window.location.href='players.php'" title="Continue to Players">
            <div class="circle blue-circle">▶</div>
        </button>
        <button class="control-btn discontinue-btn" onclick="window.location.href='index.php'" title="Exit to Home">
            <div class="circle red-circle">✕</div>
        </button>
    </div>

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
        <div class="header">
            <h1 class="title">Game Rounds</h1>
            <p class="subtitle">Complete rounds sequentially - each round unlocks the next</p>
            <div class="game-stats">
                <div class="stat">
                    <span class="stat-value" id="aliveCount">456</span>
                    <span class="stat-label">Alive Players</span>
                </div>
                <div class="stat">
                    <span class="stat-value" id="eliminatedCount">0</span>
                    <span class="stat-label">Eliminated</span>
                </div>
                <div class="stat">
                    <span class="stat-value" id="prizeAmount">₩0</span>
                    <span class="stat-label">Prize Money</span>
                </div>
            </div>
        </div>
        
        <div class="games-grid">
            <!-- Game 1: Red Light Green Light -->
            <div class="game-card" data-round="1" onclick="openGame(1, 'game_red_light.php')">
                <div class="game-number">01</div>
                <div class="game-icon">🚦</div>
                <h3 class="game-title">Red Light<br>Green Light</h3>
                <p class="game-description">Stop when the doll turns around</p>
                <p class="game-target">Target: ~250 eliminations</p>
                <div class="game-status">🔓 Available</div>
            </div>
            
            <!-- Game 2: Honeycomb -->
            <div class="game-card" data-round="2" onclick="openGame(2, 'game_honeycomb.php')">
                <div class="game-number">02</div>
                <div class="game-icon">🍪</div>
                <h3 class="game-title">Honeycomb</h3>
                <p class="game-description">Cut the shape without breaking</p>
                <p class="game-target">Target: ~100 eliminations</p>
                <div class="game-status">🔒 Locked</div>
            </div>
            
            <!-- Game 3: Tug of War -->
            <div class="game-card" data-round="3" onclick="openGame(3, 'tug_of_war.php')">
                <div class="game-number">03</div>
                <div class="game-icon">🪢</div>
                <h3 class="game-title">Tug of War</h3>
                <p class="game-description">Team battle - losers eliminated</p>
                <p class="game-target">Target: ~50 eliminations</p>
                <div class="game-status">🔒 Locked</div>
            </div>
            
            <!-- Game 4: Marbles -->
            <div class="game-card" data-round="4" onclick="openGame(4, 'game_marbles.php')">
                <div class="game-number">04</div>
                <div class="game-icon">⚪</div>
                <h3 class="game-title">Marbles</h3>
                <p class="game-description">Win your partner's marbles</p>
                <p class="game-target">Target: ~28 eliminations</p>
                <div class="game-status">🔒 Locked</div>
            </div>
            
            <!-- Game 5: Glass Bridge -->
            <div class="game-card" data-round="5" onclick="openGame(5, 'game_glass_bridge.php')">
                <div class="game-number">05</div>
                <div class="game-icon">🌉</div>
                <h3 class="game-title">Glass Bridge</h3>
                <p class="game-description">Choose the right glass panel</p>
                <p class="game-target">Target: Leave only 2 players</p>
                <div class="game-status">🔒 Locked</div>
            </div>
            
            <!-- Game 6: Squid Game -->
            <div class="game-card" data-round="6" onclick="openGame(6, 'game_squid_game.php')">
                <div class="game-number">06</div>
                <div class="game-icon">🦑</div>
                <h3 class="game-title">Squid Game</h3>
                <p class="game-description">Final battle - declare winner</p>
                <p class="game-target">Target: 1 winner remains</p>
                <div class="game-status">🔒 Locked</div>
            </div>
        </div>
    </div>

    <script src="games.js"></script>
</body>
</html>
