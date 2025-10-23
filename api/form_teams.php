<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get the formation strategy from POST
    $strategy = $_POST['strategy'] ?? '';
    
    if (empty($strategy)) {
        throw new Exception('Team formation strategy is required');
    }
    
    // Reset all teams first
    $conn->query("UPDATE players SET team = NULL WHERE status = 'alive'");
    
    // Get alive players count
    $countResult = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $aliveCount = $countResult->fetch_assoc()['count'];
    
    if ($aliveCount == 0) {
        throw new Exception('No alive players to form teams');
    }
    
    $teamACount = 0;
    $teamBCount = 0;
    
    switch ($strategy) {
        case 'age':
            // Strategy 2: Age-based (Younger vs Older)
            // Calculate median age
            $medianQuery = "SELECT age FROM players WHERE status = 'alive' ORDER BY age LIMIT 1 OFFSET " . floor($aliveCount / 2);
            $medianResult = $conn->query($medianQuery);
            $medianAge = $medianResult->fetch_assoc()['age'];
            
            // Team A: Younger players (age < median)
            $conn->query("UPDATE players SET team = 'Team A' WHERE status = 'alive' AND age < $medianAge");
            $teamACount = $conn->affected_rows;
            
            // Team B: Older players (age >= median)
            $conn->query("UPDATE players SET team = 'Team B' WHERE status = 'alive' AND age >= $medianAge");
            $teamBCount = $conn->affected_rows;
            
            $strategyName = "Age-Based: Young vs Old (Split at age $medianAge)";
            break;
            
        case 'debt':
            // Strategy 3: Debt-based (High debt vs Low debt)
            // Calculate median debt
            $medianQuery = "SELECT debt_amount FROM players WHERE status = 'alive' ORDER BY debt_amount LIMIT 1 OFFSET " . floor($aliveCount / 2);
            $medianResult = $conn->query($medianQuery);
            $medianDebt = $medianResult->fetch_assoc()['debt_amount'];
            
            // Team A: High debt players (desperate)
            $conn->query("UPDATE players SET team = 'Team A' WHERE status = 'alive' AND debt_amount >= $medianDebt");
            $teamACount = $conn->affected_rows;
            
            // Team B: Low debt players (less desperate)
            $conn->query("UPDATE players SET team = 'Team B' WHERE status = 'alive' AND debt_amount < $medianDebt");
            $teamBCount = $conn->affected_rows;
            
            $strategyName = "Debt-Based: High Debt vs Low Debt (Split at ₩" . number_format($medianDebt) . ")";
            break;
            
        case 'player_number':
            // Strategy 4: Player number (Odd vs Even)
            // Team A: Odd player numbers
            $conn->query("UPDATE players SET team = 'Team A' WHERE status = 'alive' AND player_number % 2 = 1");
            $teamACount = $conn->affected_rows;
            
            // Team B: Even player numbers
            $conn->query("UPDATE players SET team = 'Team B' WHERE status = 'alive' AND player_number % 2 = 0");
            $teamBCount = $conn->affected_rows;
            
            $strategyName = "Player Number: Odd vs Even";
            break;
            
        case 'nationality':
            // Strategy 5: Nationality-based (Korean vs Foreign)
            // Team A: Korean nationals
            $conn->query("UPDATE players SET team = 'Team A' WHERE status = 'alive' AND nationality = 'South Korea'");
            $teamACount = $conn->affected_rows;
            
            // Team B: Foreign nationals
            $conn->query("UPDATE players SET team = 'Team B' WHERE status = 'alive' AND nationality != 'South Korea'");
            $teamBCount = $conn->affected_rows;
            
            $strategyName = "Nationality: Korean vs Foreign";
            break;
            
        default:
            throw new Exception('Invalid strategy selected');
    }
    
    // Get team details
    $teamAQuery = "SELECT player_id, player_number, name, age, gender, debt_amount, nationality 
                   FROM players WHERE status = 'alive' AND team = 'Team A' ORDER BY player_number";
    $teamAResult = $conn->query($teamAQuery);
    $teamAPlayers = [];
    while ($row = $teamAResult->fetch_assoc()) {
        $teamAPlayers[] = $row;
    }
    
    $teamBQuery = "SELECT player_id, player_number, name, age, gender, debt_amount, nationality 
                   FROM players WHERE status = 'alive' AND team = 'Team B' ORDER BY player_number";
    $teamBResult = $conn->query($teamBQuery);
    $teamBPlayers = [];
    while ($row = $teamBResult->fetch_assoc()) {
        $teamBPlayers[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Teams formed successfully',
        'strategy' => $strategyName,
        'teamA' => [
            'name' => 'Team A',
            'count' => count($teamAPlayers),
            'players' => $teamAPlayers
        ],
        'teamB' => [
            'name' => 'Team B',
            'count' => count($teamBPlayers),
            'players' => $teamBPlayers
        ],
        'totalPlayers' => $aliveCount
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
