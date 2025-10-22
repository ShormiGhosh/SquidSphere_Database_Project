<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Squid Game - SquidSphere</title>
    <link rel="stylesheet" href="game_squid_game.css">
</head>
<body>
    <div class="game-container">
        <!-- Game Status -->
        <div class="game-status" id="gameStatus">
            <h2>Squid Game - Final Round</h2>
            <p id="statusText">The final battle begins...</p>
        </div>
        
        <!-- Game Play Area -->
        <div class="game-play active" id="gamePlay">
            <!-- Player 1 -->
            <div class="player player-1" id="player1">
                <img src="images/green_player2.png" alt="Player 1">
                <div class="player-info">
                    <span class="player-number">067</span>
                    <div class="health-bar">
                        <div class="health-fill" id="health1"></div>
                    </div>
                </div>
            </div>

            <!-- Player 2 -->
            <div class="player player-2" id="player2">
                <img src="images/green_player3.png" alt="Player 2">
                <div class="player-info">
                    <span class="player-number">456</span>
                    <div class="health-bar">
                        <div class="health-fill" id="health2"></div>
                    </div>
                </div>
            </div>

            <!-- VS Text -->
            <div class="vs-text">VS</div>
        </div>

        <!-- Result Screen -->
        <div class="result-screen" id="resultScreen">
            <div class="result-content">
                <h2 id="resultTitle">Game Over</h2>
                <div class="winner-display">
                    <img id="winnerImage" src="" alt="Winner">
                    <div class="winner-info">
                        <p class="winner-label">Winner</p>
                        <p class="winner-number" id="winnerNumber">-</p>
                    </div>
                </div>
                <div class="result-stats">
                    <div class="stat-item">
                        <span class="stat-label">Prize Money:</span>
                        <span class="stat-value prize" id="prizeMoney">₩45.6 Billion</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Survivors:</span>
                        <span class="stat-value survivors" id="survivors">1 player</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Eliminated:</span>
                        <span class="stat-value eliminated" id="eliminated">455 players</span>
                    </div>
                </div>
                <button class="btn-back" onclick="window.location.href='games.php'">Back to Games</button>
            </div>
        </div>
    </div>

    <script src="game_squid_game.js"></script>
</body>
</html>
