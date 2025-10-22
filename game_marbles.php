<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marbles - SquidSphere</title>
    <link rel="stylesheet" href="game_marbles.css">
</head>
<body>
    <div class="game-container">
        <!-- Game Status -->
        <div class="game-status" id="gameStatus">
            <h2>Marbles Game</h2>
            <p id="statusText">Players betting marbles...</p>
        </div>
        
        <!-- Game Play Area -->
        <div class="game-play active" id="gamePlay">
            <!-- Player 1 (Left) -->
            <div class="player player-1" id="player1">
                <img src="images/green_player2.png" alt="Player 1" class="player-image">
                <div class="player-info">
                    <span class="player-number">067</span>
                    <div class="marbles-container" id="marbles1">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                        <img src="images/marble1.png" alt="Marble" class="marble">
                    </div>
                    <div class="marble-count" id="count1">10</div>
                </div>
            </div>

            <!-- Center Board -->
            <div class="center-board">
                <img src="images/squid_back6round.png" alt="Game Board" class="game-board">
                <div class="round-display" id="roundDisplay">Round 1</div>
            </div>

            <!-- Player 2 (Right) -->
            <div class="player player-2" id="player2">
                <img src="images/green_player3.png" alt="Player 2" class="player-image">
                <div class="player-info">
                    <span class="player-number">456</span>
                    <div class="marbles-container" id="marbles2">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                        <img src="images/marble2.png" alt="Marble" class="marble">
                    </div>
                    <div class="marble-count" id="count2">10</div>
                </div>
            </div>
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
                        <p class="winner-marbles" id="winnerMarbles">20 Marbles</p>
                    </div>
                </div>
                <div class="result-stats">
                    <div class="stat-item">
                        <span class="stat-label">Survivor:</span>
                        <span class="stat-value survivors" id="survivors">1 player</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Eliminated:</span>
                        <span class="stat-value eliminated" id="eliminated">1 player</span>
                    </div>
                </div>
                <button class="btn-back" onclick="window.location.href='games.php'">Back to Games</button>
            </div>
        </div>
    </div>

    <script src="game_marbles.js"></script>
</body>
</html>
