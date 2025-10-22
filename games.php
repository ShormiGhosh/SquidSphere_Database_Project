<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games - SquidSphere</title>
    <link rel="stylesheet" href="games.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-logo">
            <span class="logo-text">SquidSphere</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="players.php">Players</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="games.php" class="active">Gameplay</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="header">
            <h1 class="title">Select Your Game</h1>
            <p class="subtitle">Choose a game to simulate</p>
        </div>
        
        <div class="games-grid">
            <!-- Game 1: Red Light Green Light -->
            <div class="game-card available" onclick="window.location.href='game_red_light.php'">
                <div class="game-number">01</div>
                <div class="game-icon">🚦</div>
                <h3 class="game-title">Red Light<br>Green Light</h3>
                <p class="game-description">Stop when the doll turns around</p>
                <div class="game-status available-status">Available</div>
            </div>
            
            <!-- Game 2: Honeycomb -->
            <div class="game-card available" onclick="window.location.href='game_honeycomb.php'">
                <div class="game-number">02</div>
                <div class="game-icon">🍪</div>
                <h3 class="game-title">Honeycomb</h3>
                <p class="game-description">Cut the shape without breaking</p>
                <div class="game-status available-status">Available</div>
            </div>
            
            <!-- Game 3: Tug of War -->
            <div class="game-card available" onclick="window.location.href='game_tug_of_war.php'">
                <div class="game-number">03</div>
                <div class="game-icon">🪢</div>
                <h3 class="game-title">Tug of War</h3>
                <p class="game-description">Pull the opposing team</p>
                <div class="game-status available-status">Available</div>
            </div>
            
            <!-- Game 4: Marbles -->
            <div class="game-card available" onclick="window.location.href='game_marbles.php'">
                <div class="game-number">04</div>
                <div class="game-icon">⚪</div>
                <h3 class="game-title">Marbles</h3>
                <p class="game-description">Win your partner's marbles</p>
                <div class="game-status available-status">Available</div>
            </div>
            
            <!-- Game 5: Glass Bridge -->
            <div class="game-card available" onclick="window.location.href='game_glass_bridge.php'">
                <div class="game-number">05</div>
                <div class="game-icon">🌉</div>
                <h3 class="game-title">Glass Bridge</h3>
                <p class="game-description">Choose the right glass panel</p>
                <div class="game-status available-status">Available</div>
            </div>
            
            <!-- Game 6: Squid Game -->
            <div class="game-card available" onclick="window.location.href='game_squid_game.php'">
                <div class="game-number">06</div>
                <div class="game-icon">🦑</div>
                <h3 class="game-title">Squid Game</h3>
                <p class="game-description">Final battle - one winner</p>
                <div class="game-status available-status">Available</div>
            </div>
        </div>
        
        <div class="back-button-container" style="display: none;">
            <button class="back-button" onclick="window.location.href='players.php'">
                ← Back to Players
            </button>
        </div>
    </div>
</body>
</html>
