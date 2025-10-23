<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

// Get parameters
$gameRound = $_POST['gameRound'] ?? '';
$eliminateCount = $_POST['eliminateCount'] ?? 0;

try {
    $conn = getDBConnection();
    
    // Get current alive players count
    $countQuery = "SELECT COUNT(*) as alive_count FROM players WHERE status = 'alive'";
    $countResult = $conn->query($countQuery);
    $aliveCount = $countResult->fetch_assoc()['alive_count'];
    
    if ($aliveCount == 0) {
        throw new Exception("No alive players to eliminate");
    }
    
    // Validate elimination count
    if ($eliminateCount <= 0) {
        throw new Exception("Invalid elimination count");
    }
    
    if ($eliminateCount > $aliveCount) {
        $eliminateCount = $aliveCount - 1; // Keep at least 1 player
    }
    
    // Get random alive players to eliminate
    $selectQuery = "SELECT player_id, player_number, name 
                    FROM players 
                    WHERE status = 'alive' 
                    ORDER BY RAND() 
                    LIMIT " . intval($eliminateCount);
    
    $result = $conn->query($selectQuery);
    
    if (!$result) {
        throw new Exception("Failed to select players: " . $conn->error);
    }
    
    $eliminatedPlayers = [];
    $playerIds = [];
    
    while ($row = $result->fetch_assoc()) {
        $eliminatedPlayers[] = $row;
        $playerIds[] = $row['player_id'];
    }
    
    // Eliminate selected players
    if (count($playerIds) > 0) {
        $idsString = implode(',', $playerIds);
        $updateQuery = "UPDATE players SET status = 'eliminated' WHERE player_id IN ($idsString)";
        
        if (!$conn->query($updateQuery)) {
            throw new Exception("Failed to eliminate players: " . $conn->error);
        }
    }
    
    // Get updated counts
    $afterCountResult = $conn->query("SELECT COUNT(*) as alive_count FROM players WHERE status = 'alive'");
    $afterAliveCount = $afterCountResult->fetch_assoc()['alive_count'];
    
    $afterEliminatedResult = $conn->query("SELECT COUNT(*) as eliminated_count FROM players WHERE status = 'eliminated'");
    $afterEliminatedCount = $afterEliminatedResult->fetch_assoc()['eliminated_count'];
    
    // Calculate prize money (100M KRW per eliminated player)
    $prizeMoney = $afterEliminatedCount * 100000000;
    
    echo json_encode([
        'success' => true,
        'message' => count($eliminatedPlayers) . ' players eliminated in ' . $gameRound,
        'gameRound' => $gameRound,
        'eliminatedPlayers' => $eliminatedPlayers,
        'eliminatedCount' => count($eliminatedPlayers),
        'remainingCount' => $afterAliveCount,
        'aliveCountBefore' => $aliveCount,
        'aliveCountAfter' => $afterAliveCount,
        'totalEliminated' => $afterEliminatedCount,
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
