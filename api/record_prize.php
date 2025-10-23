<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get parameters
    $playerId = isset($_POST['playerId']) ? intval($_POST['playerId']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $gameId = isset($_POST['gameId']) ? intval($_POST['gameId']) : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    
    if ($playerId <= 0 || $amount <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid player ID or amount'
        ]);
        exit;
    }
    
    // Insert prize distribution record
    $sql = "INSERT INTO prize_distribution 
            (player_id, game_id, amount, payment_status, description) 
            VALUES (?, ?, ?, 'pending', ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iids', $playerId, $gameId, $amount, $description);
    $stmt->execute();
    
    $distributionId = $stmt->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Prize distribution recorded',
        'distribution_id' => $distributionId,
        'amount' => $amount,
        'formatted_amount' => '₩' . number_format($amount)
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
