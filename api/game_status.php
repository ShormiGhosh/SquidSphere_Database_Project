<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get player counts by status
    $aliveResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $aliveCount = $aliveResult->fetch_assoc()['count'];
    
    $eliminatedResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'eliminated'");
    $eliminatedCount = $eliminatedResult->fetch_assoc()['count'];
    
    $winnerResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'winner'");
    $winnerCount = $winnerResult->fetch_assoc()['count'];
    
    // Get winner details if exists
    $winner = null;
    if ($winnerCount > 0) {
        $winnerQuery = "SELECT player_id, player_number, name, age, gender, nationality, debt_amount 
                       FROM players WHERE status = 'winner' LIMIT 1";
        $winnerData = $conn->query($winnerQuery);
        $winner = $winnerData->fetch_assoc();
    }
    
    // Calculate prize money
    $prizeMoney = $eliminatedCount * 100000000;
    
    // Determine game phase
    $gamePhase = '';
    $nextRound = '';
    $currentRound = 0;
    $unlockedRounds = [];
    
    if ($aliveCount == 456) {
        $gamePhase = 'Not Started';
        $nextRound = 'Red Light, Green Light';
        $currentRound = 1;
        $unlockedRounds = [1]; // Only Round 1 available
    } else if ($aliveCount > 206) {
        $gamePhase = 'Round 1 - Red Light, Green Light';
        $nextRound = 'Continue Round 1';
        $currentRound = 1;
        $unlockedRounds = [1]; // Only Round 1 available
    } else if ($aliveCount == 206) {
        $gamePhase = 'Round 1 Complete';
        $nextRound = 'Honeycomb - Unlocked!';
        $currentRound = 2;
        $unlockedRounds = [1, 2]; // Round 2 unlocked
    } else if ($aliveCount > 106 && $aliveCount < 206) {
        $gamePhase = 'Round 2 - Honeycomb';
        $nextRound = 'Continue Round 2';
        $currentRound = 2;
        $unlockedRounds = [1, 2]; // Round 2 available
    } else if ($aliveCount == 106) {
        $gamePhase = 'Round 2 Complete';
        $nextRound = 'Tug of War - Unlocked!';
        $currentRound = 3;
        $unlockedRounds = [1, 2, 3]; // Round 3 unlocked
    } else if ($aliveCount > 56 && $aliveCount < 106) {
        $gamePhase = 'Round 3 - Tug of War';
        $nextRound = 'Continue Round 3';
        $currentRound = 3;
        $unlockedRounds = [1, 2, 3]; // Round 3 available
    } else if ($aliveCount == 56) {
        $gamePhase = 'Round 3 Complete';
        $nextRound = 'Marbles - Unlocked!';
        $currentRound = 4;
        $unlockedRounds = [1, 2, 3, 4]; // Round 4 unlocked
    } else if ($aliveCount > 28 && $aliveCount < 56) {
        $gamePhase = 'Round 4 - Marbles';
        $nextRound = 'Continue Round 4';
        $currentRound = 4;
        $unlockedRounds = [1, 2, 3, 4]; // Round 4 available
    } else if ($aliveCount == 28) {
        $gamePhase = 'Round 4 Complete';
        $nextRound = 'Glass Bridge - Unlocked!';
        $currentRound = 5;
        $unlockedRounds = [1, 2, 3, 4, 5]; // Round 5 unlocked
    } else if ($aliveCount > 2 && $aliveCount < 28) {
        $gamePhase = 'Round 5 - Glass Bridge';
        $nextRound = 'Continue Round 5';
        $currentRound = 5;
        $unlockedRounds = [1, 2, 3, 4, 5]; // Round 5 available
    } else if ($aliveCount == 2) {
        $gamePhase = 'Round 5 Complete';
        $nextRound = 'Squid Game (Final) - Unlocked!';
        $currentRound = 6;
        $unlockedRounds = [1, 2, 3, 4, 5, 6]; // Round 6 unlocked
    } else if ($aliveCount == 1 && $winnerCount == 0) {
        $gamePhase = 'Final Round - Squid Game';
        $nextRound = 'Declare Winner';
        $currentRound = 6;
        $unlockedRounds = [1, 2, 3, 4, 5, 6]; // Round 6 available
    } else if ($winnerCount > 0) {
        $gamePhase = 'Game Completed';
        $nextRound = 'None';
        $currentRound = 7;
        $unlockedRounds = [1, 2, 3, 4, 5, 6]; // All completed
    } else {
        $gamePhase = 'Unknown';
        $nextRound = 'Error - No alive players';
        $currentRound = 0;
        $unlockedRounds = [];
    }
    
    echo json_encode([
        'success' => true,
        'aliveCount' => $aliveCount,
        'eliminatedCount' => $eliminatedCount,
        'winnerCount' => $winnerCount,
        'totalPlayers' => 456,
        'prizeMoney' => $prizeMoney,
        'prizeMoneyFormatted' => '₩' . number_format($prizeMoney),
        'gamePhase' => $gamePhase,
        'nextRound' => $nextRound,
        'currentRound' => $currentRound,
        'unlockedRounds' => $unlockedRounds,
        'winner' => $winner
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
