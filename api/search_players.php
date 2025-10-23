<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

// Get filter parameters
$playerNumber = $_GET['playerNumber'] ?? '';
$playerName = $_GET['playerName'] ?? '';
$gender = $_GET['gender'] ?? '';
$minAge = $_GET['minAge'] ?? '';
$maxAge = $_GET['maxAge'] ?? '';
$nationality = $_GET['nationality'] ?? '';
$minDebt = $_GET['minDebt'] ?? '';
$maxDebt = $_GET['maxDebt'] ?? '';
$status = $_GET['status'] ?? '';
$advancedQuery = $_GET['advancedQuery'] ?? '';
$sortBy = $_GET['sortBy'] ?? 'player_number ASC';
$limit = $_GET['limit'] ?? 100;

try {
    $conn = getDBConnection();

    // Build the SQL query based on advanced query type
    $sql = buildAdvancedQuery($advancedQuery, $playerNumber, $playerName, $gender, $minAge, $maxAge, 
                              $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit);

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $players = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $players[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'count' => count($players),
        'players' => $players,
        'sql' => $sql  // Return SQL for educational purposes
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'sql' => isset($sql) ? $sql : 'Query not built'
    ]);
}

function buildAdvancedQuery($advancedQuery, $playerNumber, $playerName, $gender, $minAge, $maxAge, 
                            $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit) {
    
    // Handle special advanced queries with subqueries and set operations
    if (!empty($advancedQuery)) {
        switch ($advancedQuery) {
            
            // STATUS-BASED: Alive with high debt
            case 'alive_high_debt':
                return buildBaseQuery(
                    "status = 'alive' AND debt_amount > 30000000",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, '', $sortBy, $limit
                );
            
            // STATUS-BASED: Eliminated young players
            case 'eliminated_young':
                return buildBaseQuery(
                    "status = 'eliminated' AND age < 35",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, '', $sortBy, $limit
                );
            
            // UNION: Alive rich OR Eliminated young
            case 'union_alive_eliminated':
                $baseWhere = buildWhereConditions($playerNumber, $playerName, $gender, $minAge, $maxAge, 
                                                  $nationality, $minDebt, $maxDebt, '');
                $query1 = "SELECT DISTINCT * FROM players WHERE status = 'alive' AND debt_amount > 50000000" . ($baseWhere ? " AND " . substr($baseWhere, 6) : "");
                $query2 = "SELECT DISTINCT * FROM players WHERE status = 'eliminated' AND age < 30" . ($baseWhere ? " AND " . substr($baseWhere, 6) : "");
                return "SELECT * FROM (($query1) UNION ($query2)) AS union_result ORDER BY $sortBy LIMIT $limit";
            
            // SUBQUERY: Above average debt
            case 'above_avg_debt':
                return buildBaseQuery(
                    "player_id IN (SELECT player_id FROM players WHERE debt_amount > (SELECT AVG(debt_amount) FROM players))",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // SUBQUERY: Below average debt
            case 'below_avg_debt':
                return buildBaseQuery(
                    "player_id IN (SELECT player_id FROM players WHERE debt_amount < (SELECT AVG(debt_amount) FROM players))",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // SUBQUERY: Above average age
            case 'above_avg_age':
                return buildBaseQuery(
                    "player_id IN (SELECT player_id FROM players WHERE age > (SELECT AVG(age) FROM players))",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // SUBQUERY: Below average age
            case 'below_avg_age':
                return buildBaseQuery(
                    "player_id IN (SELECT player_id FROM players WHERE age < (SELECT AVG(age) FROM players))",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // NESTED SUBQUERY: Maximum debt
            case 'max_debt':
                return buildBaseQuery(
                    "debt_amount = (SELECT MAX(debt_amount) FROM players WHERE player_id IN (SELECT player_id FROM players))",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // NESTED SUBQUERY: Minimum debt
            case 'min_debt':
                return buildBaseQuery(
                    "debt_amount = (SELECT MIN(debt_amount) FROM players WHERE player_id IN (SELECT player_id FROM players))",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // UNION: Males OR High debt females (>50M)
            case 'union_male_female':
                $baseWhere = buildWhereConditions($playerNumber, $playerName, '', $minAge, $maxAge, 
                                                  $nationality, $minDebt, $maxDebt, $status);
                $query1 = "SELECT DISTINCT * FROM players WHERE gender = 'M'" . ($baseWhere ? " AND " . substr($baseWhere, 6) : "");
                $query2 = "SELECT DISTINCT * FROM players WHERE gender = 'F' AND debt_amount > 50000000" . ($baseWhere ? " AND " . substr($baseWhere, 6) : "");
                return "SELECT * FROM (($query1) UNION ($query2)) AS union_result ORDER BY $sortBy LIMIT $limit";
            
            // INTERSECT (simulated): Young (<30) AND Low debt (<10M)
            case 'intersect_young_lowdebt':
                return buildBaseQuery(
                    "player_id IN (SELECT player_id FROM players WHERE age < 30) AND player_id IN (SELECT player_id FROM players WHERE debt_amount < 10000000)",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // MINUS (simulated with NOT IN): Alive but NOT young (<25)
            case 'minus_alive_young':
                return buildBaseQuery(
                    "status = 'alive' AND player_id NOT IN (SELECT player_id FROM players WHERE age < 25)",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // IN: Top 3 nationalities
            case 'in_top_nationalities':
                return buildBaseQuery(
                    "nationality IN (SELECT nationality FROM players GROUP BY nationality ORDER BY COUNT(*) DESC LIMIT 3)",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // NOT IN: Rare nationalities (<5 players)
            case 'not_in_rare_nationalities':
                return buildBaseQuery(
                    "nationality NOT IN (SELECT nationality FROM players GROUP BY nationality HAVING COUNT(*) < 5)",
                    $playerNumber, $playerName, $gender, $minAge, $maxAge, $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit
                );
            
            // EXISTS: Players with similar debt (±5M)
            case 'exists_similar_debt':
                return "SELECT p1.* FROM players p1 WHERE EXISTS (
                    SELECT 1 FROM players p2 
                    WHERE p2.player_id != p1.player_id 
                    AND p2.debt_amount BETWEEN p1.debt_amount - 5000000 AND p1.debt_amount + 5000000
                ) " . buildWhereConditions($playerNumber, $playerName, $gender, $minAge, $maxAge, 
                                           $nationality, $minDebt, $maxDebt, $status) . 
                " ORDER BY $sortBy LIMIT $limit";
            
            // EQUI JOIN: Players with Game Participation (equality condition)
            case 'equi_join':
                $baseWhere = buildWhereConditions($playerNumber, $playerName, $gender, $minAge, $maxAge, 
                                                  $nationality, $minDebt, $maxDebt, $status);
                return "SELECT DISTINCT p.* FROM players p 
                        INNER JOIN game_participation gp ON p.player_id = gp.player_id 
                        INNER JOIN games g ON gp.game_id = g.game_id" . 
                        $baseWhere . " ORDER BY $sortBy LIMIT $limit";
            
            // RIGHT JOIN: All Games (even without players)
            case 'right_join':
                // This shows games perspective, but returns player data where available
                return "SELECT DISTINCT p.player_number, p.name, p.age, p.gender, p.nationality, 
                        p.debt_amount, p.status, g.game_name, g.round_number 
                        FROM game_participation gp 
                        RIGHT JOIN games g ON gp.game_id = g.game_id 
                        LEFT JOIN players p ON gp.player_id = p.player_id 
                        ORDER BY g.round_number, p.player_number LIMIT $limit";
            
            // CROSS JOIN: All possible player-game combinations (LIMITED for performance)
            case 'cross_join':
                return "SELECT p.player_number, p.name, p.age, p.gender, p.nationality, 
                        p.debt_amount, p.status, g.game_name, g.round_number 
                        FROM players p 
                        CROSS JOIN games g 
                        WHERE p.player_number <= '010' 
                        ORDER BY p.player_number, g.round_number 
                        LIMIT $limit";
            
            // NON-EQUI JOIN: Players with Similar Debt (using range/inequality)
            case 'non_equi_join':
                return "SELECT DISTINCT p1.player_number, p1.name, p1.age, p1.gender, p1.nationality, 
                        p1.debt_amount, p1.status, 
                        COUNT(DISTINCT p2.player_id) as similar_debt_players 
                        FROM players p1 
                        JOIN players p2 ON p1.player_id != p2.player_id 
                            AND p2.debt_amount BETWEEN p1.debt_amount - 10000000 AND p1.debt_amount + 10000000 
                        GROUP BY p1.player_id, p1.player_number, p1.name, p1.age, p1.gender, 
                                 p1.nationality, p1.debt_amount, p1.status 
                        HAVING similar_debt_players > 0 
                        ORDER BY similar_debt_players DESC, $sortBy 
                        LIMIT $limit";
        }
    }
    
    // Standard query with basic filters
    return buildBaseQuery('', $playerNumber, $playerName, $gender, $minAge, $maxAge, 
                         $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit);
}

function buildBaseQuery($extraCondition, $playerNumber, $playerName, $gender, $minAge, $maxAge, 
                       $nationality, $minDebt, $maxDebt, $status, $sortBy, $limit) {
    $sql = "SELECT DISTINCT * FROM players";
    $where = buildWhereConditions($playerNumber, $playerName, $gender, $minAge, $maxAge, 
                                  $nationality, $minDebt, $maxDebt, $status);
    
    if (!empty($extraCondition)) {
        $where .= ($where ? " AND " : " WHERE ") . $extraCondition;
    }
    
    $sql .= $where . " ORDER BY $sortBy LIMIT $limit";
    return $sql;
}

function buildWhereConditions($playerNumber, $playerName, $gender, $minAge, $maxAge, 
                              $nationality, $minDebt, $maxDebt, $status) {
    $conditions = [];
    
    // LIKE for player number
    if (!empty($playerNumber)) {
        $playerNumber = addslashes($playerNumber);
        $conditions[] = "player_number LIKE '$playerNumber'";
    }
    
    // LIKE for name
    if (!empty($playerName)) {
        $playerName = addslashes($playerName);
        $conditions[] = "name LIKE '$playerName'";
    }
    
    // Exact match for gender
    if (!empty($gender)) {
        $gender = addslashes($gender);
        $conditions[] = "gender = '$gender'";
    }
    
    // Age range
    if (!empty($minAge)) {
        $conditions[] = "age >= " . intval($minAge);
    }
    if (!empty($maxAge)) {
        $conditions[] = "age <= " . intval($maxAge);
    }
    
    // LIKE for nationality
    if (!empty($nationality)) {
        $nationality = addslashes($nationality);
        $conditions[] = "nationality LIKE '$nationality'";
    }
    
    // Debt range
    if (!empty($minDebt)) {
        $conditions[] = "debt_amount >= " . intval($minDebt);
    }
    if (!empty($maxDebt)) {
        $conditions[] = "debt_amount <= " . intval($maxDebt);
    }
    
    // Status
    if (!empty($status)) {
        $status = addslashes($status);
        $conditions[] = "status = '$status'";
    }
    
    return $conditions ? " WHERE " . implode(" AND ", $conditions) : "";
}
?>
