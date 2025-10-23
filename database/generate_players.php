<?php
require_once '../config/db_config.php';

// Arrays for random data generation
$first_names = [
    'Min', 'Ji', 'Sung', 'Hyun', 'Young', 'Soo', 'Jin', 'Hae', 'Kyung', 'Sang',
    'John', 'Mary', 'Michael', 'Sarah', 'David', 'Emma', 'James', 'Lisa', 'Robert', 'Anna',
    'Wei', 'Ling', 'Chen', 'Yuki', 'Akira', 'Sakura', 'Ryu', 'Mei', 'Hiro', 'Aiko',
    'Ali', 'Fatima', 'Hassan', 'Leila', 'Ahmed', 'Zara', 'Omar', 'Noor', 'Karim', 'Amira',
    'Carlos', 'Maria', 'Jose', 'Ana', 'Luis', 'Sofia', 'Diego', 'Carmen', 'Pablo', 'Isabel'
];

$last_names = [
    'Kim', 'Lee', 'Park', 'Choi', 'Jung', 'Kang', 'Cho', 'Yoon', 'Jang', 'Lim',
    'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
    'Wang', 'Li', 'Zhang', 'Liu', 'Chen', 'Yang', 'Huang', 'Zhao', 'Wu', 'Zhou',
    'Tanaka', 'Suzuki', 'Takahashi', 'Watanabe', 'Yamamoto', 'Nakamura', 'Kobayashi', 'Kato', 'Yoshida', 'Yamada',
    'Ahmed', 'Hassan', 'Ali', 'Ibrahim', 'Mohamed', 'Abdullah', 'Khan', 'Rahman', 'Singh', 'Kumar'
];

$nationalities = [
    'South Korea', 'North Korea', 'Japan', 'China', 'USA', 'UK', 'Germany', 'France', 'Spain', 'Italy',
    'Philippines', 'Vietnam', 'Thailand', 'India', 'Pakistan', 'Bangladesh', 'Indonesia', 'Malaysia',
    'Brazil', 'Mexico', 'Argentina', 'Colombia', 'Chile', 'Peru',
    'Egypt', 'Saudi Arabia', 'UAE', 'Turkey', 'Iran', 'Iraq',
    'Russia', 'Ukraine', 'Poland', 'Netherlands', 'Belgium', 'Sweden', 'Norway', 'Denmark',
    'Australia', 'New Zealand', 'Canada', 'South Africa', 'Nigeria', 'Kenya'
];

$genders = ['M', 'F', 'Other'];

function generateRandomPlayer($player_number, $first_names, $last_names, $nationalities, $genders) {
    $first_name = $first_names[array_rand($first_names)];
    $last_name = $last_names[array_rand($last_names)];
    $name = $first_name . ' ' . $last_name;
    
    $age = rand(18, 75);
    $gender = $genders[array_rand($genders)];
    $debt_amount = rand(1000, 10000000) + (rand(0, 99) / 100); // Random debt between $1,000 and $10,000,000
    $nationality = $nationalities[array_rand($nationalities)];
    
    // 30% chance of being in an alliance group
    $alliance_group = (rand(1, 100) <= 30) ? rand(1, 10) : null;
    
    return [
        'player_number' => str_pad($player_number, 3, '0', STR_PAD_LEFT),
        'name' => $name,
        'age' => $age,
        'gender' => $gender,
        'debt_amount' => $debt_amount,
        'nationality' => $nationality,
        'alliance_group' => $alliance_group
    ];
}

// Main execution
// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$is_ajax = $is_ajax || (isset($_GET['ajax']) && $_GET['ajax'] == '1');

if (!$is_ajax) {
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Generate Players</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; background: #1a1a2e; color: white; padding: 40px; }
        h1 { color: #d70078; text-align: center; }
        .container { max-width: 800px; margin: 0 auto; background: #16213e; padding: 30px; border-radius: 10px; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; margin: 10px 0; }
        .btn { background: #d70078; color: white; padding: 15px 30px; border: none; border-radius: 5px; 
               cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; margin-top: 20px; }
        .btn:hover { background: #b8005f; }
    </style></head><body>";

    echo "<div class='container'>";
    echo "<h1>Generate Players up to 456</h1>";
}

try {
    $conn = getDBConnection();
    
    // Delete all existing players first (for fresh start)
    $conn->query("DELETE FROM players");
    
    if (!$is_ajax) {
        echo "<p class='info'>Generating 456 new players...</p>";
    }
    
    $stmt = $conn->prepare("INSERT INTO players (player_number, name, age, gender, debt_amount, nationality, alliance_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $success_count = 0;
    
    for ($i = 1; $i <= 456; $i++) {
        $player = generateRandomPlayer($i, $first_names, $last_names, $nationalities, $genders);
        
        $stmt->bind_param(
            "ssisssi",
            $player['player_number'],
            $player['name'],
            $player['age'],
            $player['gender'],
            $player['debt_amount'],
            $player['nationality'],
            $player['alliance_group']
        );
        
        if ($stmt->execute()) {
            $success_count++;
            if (!$is_ajax) {
                echo "<p class='success'>✓ Player #{$player['player_number']}: {$player['name']}</p>";
                flush();
                ob_flush();
            }
        }
    }
    
    $stmt->close();
    
    if (!$is_ajax) {
        echo "<hr style='border-color: #d70078; margin: 30px 0;'>";
        echo "<h2 style='color: #d70078;'>Summary</h2>";
        echo "<p class='success'>✓ Successfully generated: {$success_count} players</p>";
        echo "<a href='../players.php' class='btn'>View Players</a>";
    } else {
        echo json_encode(['success' => true, 'count' => $success_count]);
    }
    
    closeDBConnection($conn);
    
} catch (Exception $e) {
    if (!$is_ajax) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    } else {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if (!$is_ajax) {
    echo "</div>";
    echo "</body></html>";
}
?>
