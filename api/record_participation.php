<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get parameters
    $gameId = isset($_POST['gameId']) ? intval($_POST['gameId']) : 0;
    $playerId = isset($_POST['playerId']) ? intval($_POST['playerId']) : 0;
    $result = isset($_POST['result']) ? $_POST['result'] : null;
    $score = isset($_POST['score']) ? floatval($_POST['score']) : 0;
    $teamId = isset($_POST['teamId']) ? intval($_POST['teamId']) : null;
    
    if ($gameId <= 0 || $playerId <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid game ID or player ID'
        ]);
        exit;
    }
    
    // Check if participation already exists
    $checkSQL = "SELECT participation_id FROM game_participation 
                 WHERE player_id = ? AND game_id = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param('ii', $playerId, $gameId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing record
        $updateSQL = "UPDATE game_participation 
                     SET result = ?, score = ?, team_id = ?
                     WHERE player_id = ? AND game_id = ?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param('sdiii', $result, $score, $teamId, $playerId, $gameId);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Participation record updated',
            'action' => 'updated'
        ]);
    } else {
        // Insert new record
        $insertSQL = "INSERT INTO game_participation 
                     (player_id, game_id, team_id, result, score) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSQL);
        $stmt->bind_param('iiisd', $playerId, $gameId, $teamId, $result, $score);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Participation record created',
            'action' => 'created',
            'participation_id' => $stmt->insert_id
        ]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
