<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tug of War - SquidSphere</title>
    <link rel="stylesheet" href="game_tug_of_war.css">
</head>
<body>
    <div class="game-container">
        <!-- Game Status -->
        <div class="game-status" id="gameStatus">
            <h2>Tug of War</h2>
            <p id="statusText">Teams pulling the rope...</p>
        </div>
        
        <!-- Game Play Area -->
        <div class="game-play active" id="gamePlay">
            <!-- Team A (Left Side) -->
            <div class="team team-a" id="teamA">
                <div class="team-label">Team A</div>
                <div class="players-container">
                    <div class="player" data-player="a1">
                        <img src="images/green_player.png" alt="Player A1">
                        <span class="player-number">001</span>
                    </div>
                    <div class="player" data-player="a2">
                        <img src="images/green_player2.png" alt="Player A2">
                        <span class="player-number">067</span>
                    </div>
                    <div class="player" data-player="a3">
                        <img src="images/green_player3.png" alt="Player A3">
                        <span class="player-number">101</span>
                    </div>
                    <div class="player" data-player="a4">
                        <img src="images/green_player.png" alt="Player A4">
                        <span class="player-number">119</span>
                    </div>
                    <div class="player" data-player="a5">
                        <img src="images/green_player2.png" alt="Player A5">
                        <span class="player-number">148</span>
                    </div>
                    <div class="player" data-player="a6">
                        <img src="images/green_player3.png" alt="Player A6">
                        <span class="player-number">199</span>
                    </div>
                    <div class="player" data-player="a7">
                        <img src="images/green_player.png" alt="Player A7">
                        <span class="player-number">212</span>
                    </div>
                    <div class="player" data-player="a8">
                        <img src="images/green_player2.png" alt="Player A8">
                        <span class="player-number">240</span>
                    </div>
                    <div class="player" data-player="a9">
                        <img src="images/green_player3.png" alt="Player A9">
                        <span class="player-number">271</span>
                    </div>
                    <div class="player" data-player="a10">
                        <img src="images/green_player.png" alt="Player A10">
                        <span class="player-number">302</span>
                    </div>
                </div>
            </div>

            <!-- Rope -->
            <div class="rope-container" id="ropeContainer">
                <img src="" alt="" class="rope" id="rope">
                <div class="center-marker"></div>
            </div>

            <!-- Team B (Right Side) -->
            <div class="team team-b" id="teamB">
                <div class="team-label">Team B</div>
                <div class="players-container">
                    <div class="player" data-player="b1">
                        <img src="images/green_player3.png" alt="Player B1">
                        <span class="player-number">218</span>
                    </div>
                    <div class="player" data-player="b2">
                        <img src="images/green_player.png" alt="Player B2">
                        <span class="player-number">278</span>
                    </div>
                    <div class="player" data-player="b3">
                        <img src="images/green_player2.png" alt="Player B3">
                        <span class="player-number">324</span>
                    </div>
                    <div class="player" data-player="b4">
                        <img src="images/green_player3.png" alt="Player B4">
                        <span class="player-number">345</span>
                    </div>
                    <div class="player" data-player="b5">
                        <img src="images/green_player.png" alt="Player B5">
                        <span class="player-number">367</span>
                    </div>
                    <div class="player" data-player="b6">
                        <img src="images/green_player2.png" alt="Player B6">
                        <span class="player-number">389</span>
                    </div>
                    <div class="player" data-player="b7">
                        <img src="images/green_player3.png" alt="Player B7">
                        <span class="player-number">401</span>
                    </div>
                    <div class="player" data-player="b8">
                        <img src="images/green_player.png" alt="Player B8">
                        <span class="player-number">420</span>
                    </div>
                    <div class="player" data-player="b9">
                        <img src="images/green_player2.png" alt="Player B9">
                        <span class="player-number">443</span>
                    </div>
                    <div class="player" data-player="b10">
                        <img src="images/green_player3.png" alt="Player B10">
                        <span class="player-number">456</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Screen -->
        <div class="result-screen" id="resultScreen">
            <div class="result-content">
                <h2 id="resultTitle">Game Over</h2>
                <div class="result-stats">
                    <div class="stat-item">
                        <span class="stat-label">Winning Team:</span>
                        <span class="stat-value" id="winningTeam">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Survivors:</span>
                        <span class="stat-value survivors" id="survivors">10 players</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Eliminated:</span>
                        <span class="stat-value eliminated" id="eliminated">10 players</span>
                    </div>
                </div>
                <button class="btn-back" onclick="window.location.href='games.php'">Back to Games</button>
            </div>
        </div>
    </div>

    <script src="game_tug_of_war.js"></script>
</body>
</html>
