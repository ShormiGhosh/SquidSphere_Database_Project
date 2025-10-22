<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addPlayer();
        break;
    case 'get_all':
        getAllPlayers();
        break;
    case 'get_one':
        getPlayer();
        break;
    case 'update':
        updatePlayer();
        break;
    case 'delete':
        deletePlayer();
        break;
    case 'get_next_number':
        getNextPlayerNumber();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addPlayer() {
    $conn = getDBConnection();
    
    // Get input data
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? 0;
    $gender = $_POST['gender'] ?? 'Other';
    $debt_amount = $_POST['debt_amount'] ?? 0;
    $nationality = $_POST['nationality'] ?? '';
    $alliance_group = $_POST['alliance_group'] ?? null;
    
    // Generate next player number
    $player_number = getNextPlayerNumberValue($conn);
    
    // Validate input
    if (empty($name) || $age < 18 || $debt_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        closeDBConnection($conn);
        return;
    }
    
    // Prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO players (player_number, name, age, gender, debt_amount, nationality, alliance_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssi", $player_number, $name, $age, $gender, $debt_amount, $nationality, $alliance_group);
    
    if ($stmt->execute()) {
        $player_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Player added successfully',
            'player_id' => $player_id,
            'player_number' => $player_number
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add player: ' . $stmt->error]);
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

function getAllPlayers() {
    $conn = getDBConnection();
    
    $result = $conn->query("SELECT * FROM players ORDER BY player_number ASC");
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        closeDBConnection($conn);
        return;
    }
    
    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
    
    echo json_encode(['success' => true, 'players' => $players, 'count' => count($players)]);
    
    closeDBConnection($conn);
}

function getPlayer() {
    $conn = getDBConnection();
    
    $player_id = $_GET['player_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM players WHERE player_id = ?");
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $player = $result->fetch_assoc();
    
    if ($player) {
        echo json_encode(['success' => true, 'player' => $player]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Player not found']);
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

function updatePlayer() {
    $conn = getDBConnection();
    
    $player_id = $_POST['player_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? 0;
    $gender = $_POST['gender'] ?? '';
    $status = $_POST['status'] ?? '';
    $debt_amount = $_POST['debt_amount'] ?? 0;
    $nationality = $_POST['nationality'] ?? '';
    $alliance_group = $_POST['alliance_group'] ?? null;
    
    $stmt = $conn->prepare("UPDATE players SET name=?, age=?, gender=?, status=?, debt_amount=?, nationality=?, alliance_group=? WHERE player_id=?");
    $stmt->bind_param("sissdsii", $name, $age, $gender, $status, $debt_amount, $nationality, $alliance_group, $player_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Player updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update player']);
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

function deletePlayer() {
    $conn = getDBConnection();
    
    $player_id = $_POST['player_id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM players WHERE player_id = ?");
    $stmt->bind_param("i", $player_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Player deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete player']);
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

function getNextPlayerNumber() {
    $conn = getDBConnection();
    $number = getNextPlayerNumberValue($conn);
    echo json_encode(['success' => true, 'player_number' => $number]);
    closeDBConnection($conn);
}

function getNextPlayerNumberValue($conn) {
    $result = $conn->query("SELECT MAX(CAST(player_number AS UNSIGNED)) as max_number FROM players");
    $row = $result->fetch_assoc();
    $next_number = ($row['max_number'] ?? 0) + 1;
    
    // Format with leading zeros (001, 002, etc.)
    return str_pad($next_number, 3, '0', STR_PAD_LEFT);
}
?>
