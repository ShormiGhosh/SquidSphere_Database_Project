<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get current alive players
    $aliveQuery = "SELECT COUNT(*) as alive_count FROM players WHERE status = 'alive'";
    $aliveResult = $conn->query($aliveQuery);
    $aliveCount = $aliveResult->fetch_assoc()['alive_count'];
    
    if ($aliveCount == 0) {
        throw new Exception("No alive players");
    }
    
    if ($aliveCount == 1) {
        // Only one player alive, make them winner
        $winnerQuery = "SELECT player_id, player_number, name, age FROM players WHERE status = 'alive' LIMIT 1";
        $winnerResult = $conn->query($winnerQuery);
        $winner = $winnerResult->fetch_assoc();
        
        // Update to winner
        $updateQuery = "UPDATE players SET status = 'winner' WHERE player_id = " . $winner['player_id'];
        $conn->query($updateQuery);
        
    } else {
        // Multiple players alive, eliminate all but one random winner
        // First, select a random winner
        $winnerQuery = "SELECT player_id, player_number, name, age 
                       FROM players 
                       WHERE status = 'alive' 
                       ORDER BY RAND() 
                       LIMIT 1";
        $winnerResult = $conn->query($winnerQuery);
        $winner = $winnerResult->fetch_assoc();
        
        // Eliminate all other alive players
        $eliminateQuery = "UPDATE players 
                          SET status = 'eliminated' 
                          WHERE status = 'alive' AND player_id != " . $winner['player_id'];
        $conn->query($eliminateQuery);
        
        // Set the winner
        $updateQuery = "UPDATE players SET status = 'winner' WHERE player_id = " . $winner['player_id'];
        $conn->query($updateQuery);
    }
    
    // Get final counts
    $eliminatedResult = $conn->query("SELECT COUNT(*) as eliminated_count FROM players WHERE status = 'eliminated'");
    $eliminatedCount = $eliminatedResult->fetch_assoc()['eliminated_count'];
    
    // Calculate total prize money
    $prizeMoney = $eliminatedCount * 100000000;
    
    echo json_encode([
        'success' => true,
        'message' => 'Winner declared!',
        'winner' => $winner,
        'totalEliminated' => $eliminatedCount,
        'prizeMoney' => $prizeMoney,
        'prizeMoneyFormatted' => '₩' . number_format($prizeMoney)
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
