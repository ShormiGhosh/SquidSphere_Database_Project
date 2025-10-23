<?php
require_once 'config/db_config.php';
$conn = getDBConnection();

// 1. COUNT: Total players alive (status = 'alive' or 'winner')
$alive_query = "SELECT COUNT(*) as total_alive FROM players WHERE status IN ('alive', 'winner')";
$alive_result = $conn->query($alive_query);
$total_alive = $alive_result->fetch_assoc()['total_alive'];

// 2. COUNT: Total players eliminated (status = 'eliminated')
$eliminated_query = "SELECT COUNT(*) as total_eliminated FROM players WHERE status = 'eliminated'";
$eliminated_result = $conn->query($eliminated_query);
$total_eliminated = $eliminated_result->fetch_assoc()['total_eliminated'];

// 3. Total money reserved (eliminated players * 100M)
$money_query = "SELECT COUNT(*) * 100 as prize_money FROM players WHERE status = 'eliminated'";
$money_result = $conn->query($money_query);
$prize_money = $money_result->fetch_assoc()['prize_money'];

// 4. GROUP BY: Gender distribution (alive and eliminated)
$gender_query = "
    SELECT 
        gender,
        COUNT(*) as total_count,
        SUM(CASE WHEN status IN ('alive', 'winner') THEN 1 ELSE 0 END) as alive_count,
        SUM(CASE WHEN status = 'eliminated' THEN 1 ELSE 0 END) as eliminated_count
    FROM players 
    GROUP BY gender
";
$gender_result = $conn->query($gender_query);

// 5. MIN and MAX: Age statistics
$age_stats_query = "
    SELECT 
        MIN(age) as min_age,
        MAX(age) as max_age,
        AVG(age) as avg_age
    FROM players
";
$age_stats = $conn->query($age_stats_query)->fetch_assoc();

// 6. MIN and MAX: Debt statistics
$debt_stats_query = "
    SELECT 
        MIN(debt_amount) as min_debt,
        MAX(debt_amount) as max_debt,
        AVG(debt_amount) as avg_debt,
        SUM(debt_amount) as total_debt
    FROM players
";
$debt_stats = $conn->query($debt_stats_query)->fetch_assoc();

// 7. GROUP BY with HAVING: Nationalities with more than 10 players
$nationality_query = "
    SELECT 
        nationality,
        COUNT(*) as player_count,
        AVG(age) as avg_age
    FROM players 
    GROUP BY nationality
    HAVING COUNT(*) > 10
    ORDER BY player_count DESC
";
$nationality_result = $conn->query($nationality_query);

// 8. Total players
$total_players_query = "SELECT COUNT(*) as total FROM players";
$total_players = $conn->query($total_players_query)->fetch_assoc()['total'];

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SquidSphere - Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-logo">
            <span class="logo-text">SQUIDSPHERE</span>
        </div>
        <ul class="nav-links">
            <li><a href="players.php">Players</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="games.php">Gameplay</a></li>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="staff_visual.php">Staff</a></li>
        </ul>
    </nav>

    <div class="dashboard-container">
        <h1 class="dashboard-title">Game Statistics Dashboard</h1>

        <!-- Prize Money Section -->
        <div class="prize-section">
            <div class="prize-box">
                <img src="images/money_bank.png" alt="Money Bank" class="prize-icon money-bank-img">
                <div class="prize-amount"><?php echo number_format($prize_money); ?>M ₩</div>
                <div class="prize-label">Total Prize Money</div>
                <div class="prize-note">100M per eliminated player</div>
            </div>
        </div>

        <!-- Player Statistics Grid -->
        <div class="stats-grid">
            <!-- Total Players -->
            <div class="stat-card total-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?php echo $total_players; ?></div>
                <div class="stat-label">Total Players</div>
            </div>

            <!-- Alive Players -->
            <div class="stat-card alive-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $total_alive; ?></div>
                <div class="stat-label">Players Alive</div>
            </div>

            <!-- Eliminated Players -->
            <div class="stat-card eliminated-card">
                <div class="stat-icon">❌</div>
                <div class="stat-value"><?php echo $total_eliminated; ?></div>
                <div class="stat-label">Players Eliminated</div>
            </div>

            <!-- Survival Rate -->
            <div class="stat-card rate-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value">
                    <?php echo $total_players > 0 ? number_format(($total_alive / $total_players) * 100, 1) : 0; ?>%
                </div>
                <div class="stat-label">Survival Rate</div>
            </div>
        </div>

        <!-- Gender Distribution -->
        <div class="section-container">
            <h2 class="section-title">Gender Distribution (GROUP BY)</h2>
            <div class="gender-grid">
                <?php while($row = $gender_result->fetch_assoc()): ?>
                    <div class="gender-card">
                        <div class="gender-icon">
                            <?php echo $row['gender'] === 'Male' ? '♂️' : ($row['gender'] === 'Female' ? '♀️' : '⚧'); ?>
                        </div>
                        <div class="gender-name"><?php echo htmlspecialchars($row['gender']); ?></div>
                        <div class="gender-stats">
                            <div class="gender-stat">
                                <span class="stat-num"><?php echo $row['total_count']; ?></span>
                                <span class="stat-text">Total</span>
                            </div>
                            <div class="gender-stat alive">
                                <span class="stat-num"><?php echo $row['alive_count']; ?></span>
                                <span class="stat-text">Alive</span>
                            </div>
                            <div class="gender-stat eliminated">
                                <span class="stat-num"><?php echo $row['eliminated_count']; ?></span>
                                <span class="stat-text">Eliminated</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Age Statistics (MIN, MAX, AVG) -->
        <div class="section-container">
            <h2 class="section-title">Age Statistics (MIN, MAX, AVG)</h2>
            <div class="stats-row">
                <div class="stat-box min-box">
                    <div class="stat-box-icon">📉</div>
                    <div class="stat-box-value"><?php echo $age_stats['min_age']; ?></div>
                    <div class="stat-box-label">Minimum Age</div>
                </div>
                <div class="stat-box avg-box">
                    <div class="stat-box-icon">📊</div>
                    <div class="stat-box-value"><?php echo number_format($age_stats['avg_age'], 1); ?></div>
                    <div class="stat-box-label">Average Age</div>
                </div>
                <div class="stat-box max-box">
                    <div class="stat-box-icon">📈</div>
                    <div class="stat-box-value"><?php echo $age_stats['max_age']; ?></div>
                    <div class="stat-box-label">Maximum Age</div>
                </div>
            </div>
        </div>

        <!-- Debt Statistics (MIN, MAX, AVG, SUM) -->
        <div class="section-container">
            <h2 class="section-title">Debt Statistics (MIN, MAX, AVG, SUM)</h2>
            <div class="stats-row">
                <div class="stat-box debt-min">
                    <div class="stat-box-icon">💵</div>
                    <div class="stat-box-value">₩<?php echo number_format($debt_stats['min_debt']); ?></div>
                    <div class="stat-box-label">Minimum Debt</div>
                </div>
                <div class="stat-box debt-avg">
                    <div class="stat-box-icon">💴</div>
                    <div class="stat-box-value">₩<?php echo number_format($debt_stats['avg_debt']); ?></div>
                    <div class="stat-box-label">Average Debt</div>
                </div>
                <div class="stat-box debt-max">
                    <div class="stat-box-icon">💶</div>
                    <div class="stat-box-value">₩<?php echo number_format($debt_stats['max_debt']); ?></div>
                    <div class="stat-box-label">Maximum Debt</div>
                </div>
                <div class="stat-box debt-sum">
                    <div class="stat-box-icon">💷</div>
                    <div class="stat-box-value">₩<?php echo number_format($debt_stats['total_debt']); ?></div>
                    <div class="stat-box-label">Total Debt</div>
                </div>
            </div>
        </div>

        <!-- Nationality Distribution (GROUP BY with HAVING) -->
        <div class="section-container">
            <h2 class="section-title">Top Nationalities (GROUP BY with HAVING > 10)</h2>
            <div class="nationality-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nationality</th>
                            <th>Player Count</th>
                            <th>Average Age</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $nationality_result->fetch_assoc()): ?>
                            <tr>
                                <td class="nationality-name"><?php echo htmlspecialchars($row['nationality']); ?></td>
                                <td class="nationality-count"><?php echo $row['player_count']; ?></td>
                                <td class="nationality-avg"><?php echo number_format($row['avg_age'], 1); ?> years</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SQL Queries Used -->
        <div class="section-container sql-section">
            <h2 class="section-title">SQL Queries Demonstration</h2>
            <div class="sql-grid">
                <div class="sql-card">
                    <div class="sql-title">COUNT - Alive Players</div>
                    <code>SELECT COUNT(*) as total_alive FROM players WHERE status = 'alive' OR status IS NULL</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">COUNT - Eliminated Players</div>
                    <code>SELECT COUNT(*) as total_eliminated FROM players WHERE status = 'eliminated'</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">SUM - Prize Money Calculation</div>
                    <code>SELECT COUNT(*) * 100 as prize_money FROM players WHERE status = 'eliminated'</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">GROUP BY - Gender Distribution</div>
                    <code>SELECT gender, COUNT(*) as total_count FROM players GROUP BY gender</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">MIN, MAX, AVG - Age Stats</div>
                    <code>SELECT MIN(age), MAX(age), AVG(age) FROM players</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">GROUP BY HAVING - Nationalities</div>
                    <code>SELECT nationality, COUNT(*) FROM players GROUP BY nationality HAVING COUNT(*) > 10</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
