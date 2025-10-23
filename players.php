<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SquidSphere - Players</title>
    <link rel="stylesheet" href="players.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-logo">
            <span class="logo-text">SquidSphere</span>
        </div>
        <ul class="nav-links">
            <li><a href="players.php" class="active">Players</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="games.php">Gameplay</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="staff_visual.php">Staff</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1 class="title">Player List</h1>
        
        <button class="add-player-btn" onclick="addPlayer()">+ Add Player</button>
        
        <div class="players-grid" id="playersGrid">
            <!-- Player tiles will be added here dynamically -->
        </div>
    </div>

    <script src="players.js"></script>
</body>
</html>
