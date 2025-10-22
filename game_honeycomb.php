<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honeycomb Game - SquidSphere</title>
    <link rel="stylesheet" href="game_honeycomb.css">
</head>
<body>
    <div class="game-container">
        <!-- Game Status -->
        <div class="game-status" id="gameStatus">
            <h2>Honeycomb Challenge</h2>
            <p id="statusText">Players extracting candies...</p>
        </div>
        
        <!-- Game Play Area - Multiple Candies -->
        <div class="game-play active" id="gamePlay">
            <div class="candies-grid">
                <div class="candy-slot">
                    <img src="images/triangle_candy.png" alt="Triangle" class="candy-image">
                    <div class="needle-container">
                        <img src="images/niddle.png" alt="Needle" class="needle">
                    </div>
                    <div class="player-label">Player 1</div>
                </div>
                <div class="candy-slot">
                    <img src="images/circle_candy.png" alt="Circle" class="candy-image">
                    <div class="needle-container">
                        <img src="images/niddle.png" alt="Needle" class="needle">
                    </div>
                    <div class="player-label">Player 2</div>
                </div>
                <div class="candy-slot">
                    <img src="images/star_candy.png" alt="Star" class="candy-image">
                    <div class="needle-container">
                        <img src="images/niddle.png" alt="Needle" class="needle">
                    </div>
                    <div class="player-label">Player 3</div>
                </div>
                <div class="candy-slot">
                    <img src="images/umbrella_candy.png" alt="Umbrella" class="candy-image">
                    <div class="needle-container">
                        <img src="images/niddle.png" alt="Needle" class="needle">
                    </div>
                    <div class="player-label">Player 4</div>
                </div>
            </div>
        </div>
        
        <!-- Result Screen -->
        <div class="result-screen" id="resultScreen">
            <div class="result-content">
                <div class="result-image" id="resultImage"></div>
                <h1 id="resultTitle"></h1>
                <p id="resultMessage"></p>
                <button class="btn-return" onclick="window.location.href='games.php'">Back to Games</button>
            </div>
        </div>
    </div>

    <script src="game_honeycomb.js"></script>
</body>
</html>
