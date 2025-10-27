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
        
        // For tug_of_war, support passing winningTeam via GET to eliminate losing team
        $winningTeam = isset($_GET['winningTeam']) ? $_GET['winningTeam'] : null;

        $result = eliminatePlayers($conn, $game, $ELIMINATION_RULES[$game], $winningTeam);
        echo json_encode($result);
    }
    elseif ($action === 'reset') {
        // Reset all players to alive
        $conn->query("UPDATE players SET status = 'alive'");
        // Clear completed rounds
        $conn->query("DELETE FROM completed_rounds");
        // Clear game participation records
        $conn->query("DELETE FROM game_participation");
        // Clear prize distribution records
        $conn->query("DELETE FROM prize_distribution");
        // Clear team members
        $conn->query("DELETE FROM team_members");
        // Clear teams
        $conn->query("DELETE FROM teams");
        
        echo json_encode([
            'success' => true,
            'message' => 'All players reset to alive status, all game data cleared (rounds, participation, prizes, teams)',
            'alive_count' => 456
        ]);
    }
    elseif ($action === 'reset_rounds') {
        // Clear completed rounds tracking only
        $conn->query("DELETE FROM completed_rounds");
        echo json_encode([
            'success' => true,
            'message' => 'Completed rounds cleared'
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

function eliminatePlayers($conn, $game, $rules, $winningTeam = null) {
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
    
    // Special handling for tug_of_war: eliminate entire losing team
    if ($game === 'tug_of_war') {
        // Ensure teams exist in DB; if not, create two teams and assign alive players randomly
        // Teams table: teams(team_id, team_name, team_leader_id, game_id, created_date, status)
        // team_members: team_member_id, team_id, player_id

        // Check if teams exist for game_id 3 (Tug of War)
        $teamCheck = $conn->query("SELECT team_id, team_name FROM teams WHERE game_id = 3");
        $teams = [];
        while ($r = $teamCheck->fetch_assoc()) { $teams[] = $r; }

        if (count($teams) < 2) {
            // Create two teams
            $conn->query("INSERT INTO teams (team_name, game_id) VALUES ('Team A', 3), ('Team B', 3)");
            // Re-fetch teams
            $teamCheck = $conn->query("SELECT team_id, team_name FROM teams WHERE game_id = 3 ORDER BY team_id ASC");
            $teams = [];
            while ($r = $teamCheck->fetch_assoc()) { $teams[] = $r; }

            // Assign alive players randomly into two teams
            $aliveRes = $conn->query("SELECT player_id FROM players WHERE status = 'alive' ORDER BY RAND()");
            $i = 0;
            while ($p = $aliveRes->fetch_assoc()) {
                $teamId = $teams[$i % 2]['team_id'];
                $pid = (int)$p['player_id'];
                $conn->query("INSERT IGNORE INTO team_members (team_id, player_id) VALUES ($teamId, $pid)");
                $i++;
            }
        }

        // Determine losing team based on $winningTeam param
        if ($winningTeam === 'A') {
            $losingTeamName = 'Team B';
        } elseif ($winningTeam === 'B') {
            $losingTeamName = 'Team A';
        } else {
            // If not provided, pick a random losing team
            $losingTeamName = (rand(0,1) === 0) ? 'Team A' : 'Team B';
        }

        // Get team id for losing team
        $ltRes = $conn->query("SELECT team_id FROM teams WHERE game_id = 3 AND team_name = '" . $conn->real_escape_string($losingTeamName) . "' LIMIT 1");
        $ltRow = $ltRes->fetch_assoc();
        $losingTeamId = $ltRow ? (int)$ltRow['team_id'] : 0;

        if ($losingTeamId > 0) {
            // Eliminate all team members of losing team
            $sql = "UPDATE players p JOIN team_members tm ON p.player_id = tm.player_id SET p.status = 'eliminated' WHERE tm.team_id = " . $losingTeamId;
            $conn->query($sql);
            $eliminated = $conn->affected_rows;
        } else {
            throw new Exception('Losing team not found');
        }
    } else {
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
    }
    
    // Get game_id for current game
    $gameIdMap = [
        'red_light' => 1,
        'honeycomb' => 2,
        'tug_of_war' => 3,
        'marbles' => 4,
        'glass_bridge' => 5,
        'squid_game' => 6
    ];
    $gameId = $gameIdMap[$game] ?? 1;
    
    // Record game participation for ALL alive and just-eliminated players
    $allPlayersResult = $conn->query("SELECT player_id FROM players WHERE status IN ('alive', 'eliminated', 'winner')");
    while ($playerRow = $allPlayersResult->fetch_assoc()) {
        $playerId = $playerRow['player_id'];
        
        // Get player's current status
        $statusQuery = $conn->query("SELECT status FROM players WHERE player_id = $playerId");
        $statusRow = $statusQuery->fetch_assoc();
        $playerStatus = $statusRow['status'];
        
        // Determine result
        $result = ($playerStatus == 'eliminated') ? 'eliminated' : 'survived';
        
        // Get team_id if applicable (for tug_of_war)
        $teamId = null;
        if ($game === 'tug_of_war') {
            $teamQuery = $conn->query("SELECT team_id FROM team_members WHERE player_id = $playerId LIMIT 1");
            if ($teamRow = $teamQuery->fetch_assoc()) {
                $teamId = $teamRow['team_id'];
            }
        }
        
        // Random score between 0-100
        $score = rand(0, 100);
        
        // Check if participation record exists
        $checkParticipation = $conn->query("SELECT participation_id FROM game_participation WHERE player_id = $playerId AND game_id = $gameId");
        
        if ($checkParticipation->num_rows == 0) {
            // Insert new participation record
            if ($teamId) {
                $conn->query("INSERT INTO game_participation (player_id, game_id, team_id, result, score) 
                             VALUES ($playerId, $gameId, $teamId, '$result', $score)");
            } else {
                $conn->query("INSERT INTO game_participation (player_id, game_id, result, score) 
                             VALUES ($playerId, $gameId, '$result', $score)");
            }
        } else {
            // Update existing record
            $conn->query("UPDATE game_participation SET result = '$result', score = $score WHERE player_id = $playerId AND game_id = $gameId");
        }
    }
    
    // Record prize distribution for eliminated players
    $eliminatedPlayers = $conn->query("SELECT player_id FROM players WHERE status = 'eliminated'");
    $totalEliminated = $eliminatedPlayers->num_rows;
    $prizePerPlayer = 100000000; // 100 million won per eliminated player
    $totalPrize = $totalEliminated * $prizePerPlayer;
    
    while ($elimRow = $eliminatedPlayers->fetch_assoc()) {
        $playerId = $elimRow['player_id'];
        
        // Check if prize already distributed for this game
        $checkPrize = $conn->query("SELECT distribution_id FROM prize_distribution WHERE player_id = $playerId AND game_id = $gameId");
        
        if ($checkPrize->num_rows == 0) {
            // Insert prize distribution record (pending for eliminated players)
            $conn->query("INSERT INTO prize_distribution (player_id, game_id, amount, payment_status, description) 
                         VALUES ($playerId, $gameId, $prizePerPlayer, 'pending', 'Elimination reward - {$rules['name']}')");
        }
    }
    
    // Check if there's a winner
    $result = $conn->query("SELECT COUNT(*) as count FROM players WHERE status = 'alive'");
    $row = $result->fetch_assoc();
    $remaining = (int)$row['count'];
    
    // If only 1 player remains after Squid Game, mark as winner
    if ($game === 'squid_game' && $remaining == 1) {
        $conn->query("UPDATE players SET status = 'winner' WHERE status = 'alive'");
        
        // Get winner details
        $winner_result = $conn->query("SELECT player_id, player_number, name FROM players WHERE status = 'winner' LIMIT 1");
        $winner = $winner_result->fetch_assoc();
        
        $total_eliminated = 455; // All except winner
        $prize_money = $total_eliminated * 100000000; // 100M per eliminated player
        
        // Record winner's participation as 'winner'
        $winnerId = $winner['player_id'];
        $conn->query("UPDATE game_participation SET result = 'winner' WHERE player_id = $winnerId AND game_id = $gameId");
        
        // Award full prize to winner
        $checkWinnerPrize = $conn->query("SELECT distribution_id FROM prize_distribution WHERE player_id = $winnerId AND description LIKE '%Grand Prize%'");
        if ($checkWinnerPrize->num_rows == 0) {
            $conn->query("INSERT INTO prize_distribution (player_id, game_id, amount, payment_status, description) 
                         VALUES ($winnerId, $gameId, $prize_money, 'paid', 'Grand Prize - Squid Game Winner')");
        }
        
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
