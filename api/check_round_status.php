<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    $conn = getDBConnection();
    
    // Get round number from GET
    $roundNumber = isset($_GET['roundNumber']) ? intval($_GET['roundNumber']) : 0;
    
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
    
    // Check if round is complete
    $sql = "SELECT COUNT(*) as count FROM completed_rounds WHERE round_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $roundNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $isComplete = $data['count'] > 0;
    
    echo json_encode([
        'success' => true,
        'roundNumber' => $roundNumber,
        'isComplete' => $isComplete
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
