<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Light, Green Light - SquidSphere</title>
    <link rel="stylesheet" href="game_red_light.css">
</head>
<body>
    <div class="game-container">
        <!-- Doll -->
        <div class="doll-container">
            <img src="images/doll.png" alt="Doll" class="doll" id="doll">
        </div>
        
        <!-- Light Indicator -->
        <div class="light-indicator" id="lightIndicator">
            <div class="light green-light" id="greenLight"></div>
            <div class="light red-light" id="redLight"></div>
        </div>
        
        <!-- Players -->
        <div class="players-container" id="playersContainer">
            <!-- Players will be added dynamically -->
        </div>
        
        <!-- Start Line -->
        <div class="start-line"></div>
        
        <!-- Finish Line -->
        <div class="finish-line"></div>
        
        <!-- Game Status -->
        <div class="game-status" id="gameStatus">
            <h2>Red Light, Green Light</h2>
            <p id="statusText">Game starting...</p>
        </div>
        
        <!-- Result Screen -->
        <div class="result-screen" id="resultScreen">
            <div class="result-content">
                <h1 id="resultTitle"></h1>
                <p id="resultMessage"></p>
                <button class="btn-return" onclick="window.location.href='games.php'">Return to Games</button>
            </div>
        </div>
    </div>

    <script src="game_red_light.js"></script>
</body>
</html>
