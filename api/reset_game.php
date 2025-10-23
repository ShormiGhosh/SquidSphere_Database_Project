<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include database connection
require_once '../db_config.php';

// Reset all players to 'alive' status
$updateQuery = "UPDATE players SET status = 'alive' WHERE status IN ('eliminated', 'winner')";

if ($conn->query($updateQuery)) {
    $resetCount = $conn->affected_rows;
    
    // Clear team assignments if exists
    $clearTeamsSQL = "UPDATE players SET team = NULL WHERE team IS NOT NULL";
    $conn->query($clearTeamsSQL);
    
    // Clear completed_rounds table
    $conn->query("DROP TABLE IF EXISTS completed_rounds");
    $createTableSQL = "CREATE TABLE completed_rounds (
        round_number INT PRIMARY KEY,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTableSQL);
    
    // Get current counts
    $aliveResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $aliveCount = $aliveResult->fetch_assoc()['count'];
    
    $eliminatedResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'eliminated'");
    $eliminatedCount = $eliminatedResult->fetch_assoc()['count'];
    
    $winnerResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'winner'");
    $winnerCount = $winnerResult->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Game has been reset successfully',
        'resetCount' => $resetCount,
        'currentCounts' => [
            'alive' => $aliveCount,
            'eliminated' => $eliminatedCount,
            'winner' => $winnerCount
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reset game: ' . $conn->error
    ]);
}

$conn->close();
?>
