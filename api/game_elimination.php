<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

// Game elimination rules
$ELIMINATION_RULES = [
    'red_light' => [
        'name' => 'Red Light Green Light',
        'round' => 1,
        'elimination_rate' => 0.55, // More than half (55%)
        'min_survivors' => 200
    ],
    'honeycomb' => [
        'name' => 'Honeycomb',
        'round' => 2,
        'elimination_rate' => 0.30, // 30%
        'min_survivors' => 100
    ],
    'tug_of_war' => [
        'name' => 'Tug of War',
        'round' => 3,
        'elimination_rate' => 0.50, // Half
        'min_survivors' => 40
    ],
    'marbles' => [
        'name' => 'Marbles',
        'round' => 4,
        'elimination_rate' => 0.50, // Half
        'min_survivors' => 16
    ],
    'glass_bridge' => [
        'name' => 'Glass Bridge',
        'round' => 5,
        'elimination_rate' => 0.87, // Leave only 2 players
        'survivors_count' => 2
    ],
    'squid_game' => [
        'name' => 'Squid Game',
        'round' => 6,
        'elimination_rate' => 0.50, // 1 winner
        'survivors_count' => 1
    ]
];

try {
    $conn = getDBConnection();
    
    $action = $_GET['action'] ?? 'status';
    $game = $_GET['game'] ?? '';
    
    if ($action === 'status') {
        // Get current game status
        echo json_encode(getGameStatus($conn));
    } 
    elseif ($action === 'eliminate' && !empty($game)) {
        // Eliminate players for specific game
        if (!isset($ELIMINATION_RULES[$game])) {
            throw new Exception("Invalid game: $game");
        }
        
        $result = eliminatePlayers($conn, $game, $ELIMINATION_RULES[$game]);
        echo json_encode($result);
    }
    elseif ($action === 'reset') {
        // Reset all players to alive
        $conn->query("UPDATE players SET status = 'alive'");
        echo json_encode([
            'success' => true,
            'message' => 'All players reset to alive status',
            'alive_count' => $conn->affected_rows
        ]);
    }
    else {
        throw new Exception("Invalid action or missing game parameter");
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function getGameStatus($conn) {
    // Count alive and eliminated players
    $result = $conn->query("SELECT status, COUNT(*) as count FROM players GROUP BY status");
    
    $status = [
        'alive' => 0,
        'eliminated' => 0,
        'winner' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $status[$row['status']] = (int)$row['count'];
    }
    
    // Calculate prize money (100 million won per eliminated player)
    $prize_money = $status['eliminated'] * 100000000;
    
    // Determine current round based on alive count
    $alive = $status['alive'];
    $current_round = 'not_started';
    
    if ($alive == 456) {
        $current_round = 'ready_for_round_1';
    } elseif ($alive > 180 && $alive < 456) {
        $current_round = 'round_1_completed';
    } elseif ($alive > 80 && $alive <= 180) {
        $current_round = 'round_2_completed';
    } elseif ($alive > 30 && $alive <= 80) {
        $current_round = 'round_3_completed';
    } elseif ($alive > 10 && $alive <= 30) {
        $current_round = 'round_4_completed';
    } elseif ($alive > 1 && $alive <= 10) {
        $current_round = 'round_5_completed';
    } elseif ($alive == 1) {
        $current_round = 'game_over_winner';
    }
    
    return [
        'success' => true,
        'status' => $status,
        'prize_money' => $prize_money,
        'current_round' => $current_round,
        'alive_count' => $alive,
        'total_players' => 456
    ];
}

function eliminatePlayers($conn, $game, $rules) {
    // Get current alive players
    $result = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $row = $result->fetch_assoc();
    $alive_count = (int)$row['count'];
    
    if ($alive_count == 0) {
        throw new Exception("No alive players to eliminate!");
    }
    
    // Calculate how many to eliminate
    $to_eliminate = 0;
    
    if (isset($rules['survivors_count'])) {
        // Fixed number of survivors (e.g., Glass Bridge: 2, Squid Game: 1)
        $to_eliminate = max(0, $alive_count - $rules['survivors_count']);
    } else {
        // Percentage-based elimination
        $to_eliminate = (int)ceil($alive_count * $rules['elimination_rate']);
        
        // Ensure minimum survivors if specified
        if (isset($rules['min_survivors'])) {
            $max_eliminate = $alive_count - $rules['min_survivors'];
            $to_eliminate = min($to_eliminate, $max_eliminate);
        }
    }
    
    // Ensure we don't eliminate all players (unless it's the final round)
    if ($game !== 'squid_game' && $to_eliminate >= $alive_count) {
        $to_eliminate = $alive_count - 1;
    }
    
    if ($to_eliminate <= 0) {
        throw new Exception("Cannot eliminate players. Check game rules.");
    }
    
    // Randomly select players to eliminate
    $sql = "UPDATE players 
            SET status = 'eliminated' 
            WHERE player_id IN (
                SELECT player_id FROM (
                    SELECT player_id FROM players 
                    WHERE status = 'alive' 
                    ORDER BY RAND() 
                    LIMIT $to_eliminate
                ) as temp
            )";
    
    $conn->query($sql);
    $eliminated = $conn->affected_rows;
    
    // Check if there's a winner
    $result = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $row = $result->fetch_assoc();
    $remaining = (int)$row['count'];
    
    // If only 1 player remains after Squid Game, mark as winner
    if ($game === 'squid_game' && $remaining == 1) {
        $conn->query("UPDATE players SET status = 'winner' WHERE status = 'alive'");
        
        // Get winner details
        $winner_result = $conn->query("SELECT player_number, name FROM players WHERE status = 'winner' LIMIT 1");
        $winner = $winner_result->fetch_assoc();
        
        $total_eliminated = 455; // All except winner
        $prize_money = $total_eliminated * 100000000; // 100M per eliminated player
        
        return [
            'success' => true,
            'game' => $rules['name'],
            'round' => $rules['round'],
            'eliminated' => $eliminated,
            'remaining' => 0,
            'winner' => $winner,
            'prize_money' => $prize_money,
            'message' => "🏆 We have a WINNER! {$winner['name']} (#{$winner['player_number']}) wins ₩" . number_format($prize_money) . "!"
        ];
    }
    
    return [
        'success' => true,
        'game' => $rules['name'],
        'round' => $rules['round'],
        'eliminated' => $eliminated,
        'remaining' => $remaining,
        'message' => "{$eliminated} players eliminated in {$rules['name']}. {$remaining} players remaining."
    ];
}
?>
