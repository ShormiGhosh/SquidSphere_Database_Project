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

try {
    $conn = getDBConnection();
    
    // Check if table exists and is empty or has less than 456 players
    $result = $conn->query("SELECT COUNT(*) as count FROM players");
    $row = $result->fetch_assoc();
    $existing_count = $row['count'];
    
    if ($existing_count >= 456) {
        echo "<p class='error'>⚠️ Database already has {$existing_count} players (maximum is 456).</p>";
        echo "<p class='info'>Do you want to delete existing players and regenerate?</p>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='regenerate' class='btn'>Yes, Delete All & Regenerate</button> ";
        echo "<a href='../players.php' class='btn' style='background: #555;'>No, Go Back</a>";
        echo "</form>";
        
        if (isset($_POST['regenerate'])) {
            $conn->query("DELETE FROM players");
            echo "<p class='success'>✓ Deleted all existing players. Regenerating...</p>";
            echo "<script>setTimeout(() => window.location.reload(), 1000);</script>";
        }
    } else {
        echo "<p class='info'>Current players in database: {$existing_count}</p>";
        echo "<p class='info'>Generating " . (456 - $existing_count) . " new players...</p>";
        
        $stmt = $conn->prepare("INSERT INTO players (player_number, name, age, gender, debt_amount, nationality, alliance_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $success_count = 0;
        $error_count = 0;
        
        for ($i = $existing_count + 1; $i <= 456; $i++) {
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
                echo "<p class='success'>✓ Player #{$player['player_number']}: {$player['name']} - {$player['nationality']}</p>";
            } else {
                $error_count++;
                echo "<p class='error'>✗ Failed to add player #{$player['player_number']}: " . $stmt->error . "</p>";
            }
            
            // Flush output for real-time display
            flush();
            ob_flush();
            usleep(10000); // Small delay for visual effect
        }
        
        $stmt->close();
        
        echo "<hr style='border-color: #d70078; margin: 30px 0;'>";
        echo "<h2 style='color: #d70078;'>Summary</h2>";
        echo "<p class='success'>✓ Successfully generated: {$success_count} players</p>";
        if ($error_count > 0) {
            echo "<p class='error'>✗ Failed: {$error_count} players</p>";
        }
        echo "<p class='info'>Total players in database: " . ($existing_count + $success_count) . "</p>";
        
        echo "<a href='../players.php' class='btn'>View Players</a>";
    }
    
    closeDBConnection($conn);
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
echo "</body></html>";
?>
