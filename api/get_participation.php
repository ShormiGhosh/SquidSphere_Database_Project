<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get parameters
    $playerId = isset($_GET['playerId']) ? intval($_GET['playerId']) : 0;
    $gameId = isset($_GET['gameId']) ? intval($_GET['gameId']) : 0;
    
    // Base query
    $sql = "SELECT 
                gp.participation_id,
                gp.player_id,
                gp.game_id,
                p.player_number,
                p.name as player_name,
                g.game_name,
                gp.result,
                gp.score,
                gp.completion_time,
                gp.participation_date,
                gp.team_id,
                t.team_name
            FROM game_participation gp
            JOIN players p ON gp.player_id = p.player_id
            JOIN games g ON gp.game_id = g.game_id
            LEFT JOIN teams t ON gp.team_id = t.team_id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Add filters
    if ($playerId > 0) {
        $sql .= " AND gp.player_id = ?";
        $params[] = $playerId;
        $types .= 'i';
    }
    
    if ($gameId > 0) {
        $sql .= " AND gp.game_id = ?";
        $params[] = $gameId;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY gp.participation_date DESC";
    
    // Execute query
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    $participations = [];
    while ($row = $result->fetch_assoc()) {
        $participations[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($participations),
        'participations' => $participations
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
