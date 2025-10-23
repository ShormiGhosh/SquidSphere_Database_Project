<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get round number from POST
    $roundNumber = isset($_POST['roundNumber']) ? intval($_POST['roundNumber']) : 0;
    
    if ($roundNumber < 1 || $roundNumber > 6) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid round number'
        ]);
        exit;
    }
    
    // Create completed_rounds table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS completed_rounds (
        round_number INT PRIMARY KEY,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTableSQL);
    
    // Mark round as complete (INSERT IGNORE to avoid duplicates)
    $sql = "INSERT IGNORE INTO completed_rounds (round_number) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $roundNumber);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => "Round $roundNumber marked as complete"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
