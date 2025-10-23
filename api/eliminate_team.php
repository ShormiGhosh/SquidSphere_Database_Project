<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get the losing team from POST
    $losingTeam = $_POST['losingTeam'] ?? '';
    
    if (empty($losingTeam) || !in_array($losingTeam, ['Team A', 'Team B'])) {
        throw new Exception('Valid losing team (Team A or Team B) is required');
    }
    
    // Get losing team players before elimination
    $losingPlayersQuery = "SELECT player_id, player_number, name FROM players 
                           WHERE status = 'alive' AND team = '$losingTeam'";
    $losingPlayersResult = $conn->query($losingPlayersQuery);
    
    $losingPlayers = [];
    while ($row = $losingPlayersResult->fetch_assoc()) {
        $losingPlayers[] = $row;
    }
    
    $eliminatedCount = count($losingPlayers);
    
    if ($eliminatedCount == 0) {
        throw new Exception('No players found in ' . $losingTeam);
    }
    
    // Eliminate the losing team
    $updateQuery = "UPDATE players SET status = 'eliminated', team = NULL 
                    WHERE status = 'alive' AND team = '$losingTeam'";
    $conn->query($updateQuery);
    
    // Clear team assignments for survivors
    $conn->query("UPDATE players SET team = NULL WHERE status = 'alive'");
    
    // Get updated counts
    $aliveResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $aliveCount = $aliveResult->fetch_assoc()['count'];
    
    $eliminatedResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'eliminated'");
    $totalEliminatedCount = $eliminatedResult->fetch_assoc()['count'];
    
    $winningTeam = ($losingTeam == 'Team A') ? 'Team B' : 'Team A';
    
    echo json_encode([
        'success' => true,
        'message' => $winningTeam . ' wins! ' . $losingTeam . ' has been eliminated.',
        'losingTeam' => $losingTeam,
        'winningTeam' => $winningTeam,
        'eliminatedCount' => $eliminatedCount,
        'eliminatedPlayers' => $losingPlayers,
        'remainingPlayers' => $aliveCount,
        'totalEliminated' => $totalEliminatedCount,
        'prizeMoney' => $totalEliminatedCount * 100000000
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
