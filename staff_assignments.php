<?php
require_once 'config/db_config.php';
$conn = getDBConnection();

// Fetch all staff assignments with JOINs
$assignments_query = "
    SELECT 
        sa.assignment_id,
        sa.round_number,
        sa.role_description,
        sa.hours_worked,
        sa.assignment_date,
        s.staff_number,
        s.name AS staff_name,
        s.role AS staff_role,
        g.game_name,
        g.game_type
    FROM staff_assignments sa
    INNER JOIN staff s ON sa.staff_id = s.staff_id
    INNER JOIN games g ON sa.game_id = g.game_id
    ORDER BY sa.round_number, g.game_id, s.role, s.staff_number
";
$assignments_result = $conn->query($assignments_query);

// Assignments per game (GROUP BY)
$game_stats_query = "
    SELECT 
        g.game_name,
        g.game_type,
        sa.round_number,
        COUNT(sa.assignment_id) AS total_staff,
        SUM(sa.hours_worked) AS total_hours,
        AVG(sa.hours_worked) AS avg_hours
    FROM staff_assignments sa
    INNER JOIN games g ON sa.game_id = g.game_id
    GROUP BY g.game_id, g.game_name, g.game_type, sa.round_number
    ORDER BY sa.round_number
";
$game_stats = $conn->query($game_stats_query);

// Top working staff (ORDER BY + LIMIT)
$top_staff_query = "
    SELECT 
        s.staff_number,
        s.name,
        s.role,
        COUNT(sa.assignment_id) AS total_assignments,
        SUM(sa.hours_worked) AS total_hours,
        AVG(sa.hours_worked) AS avg_hours
    FROM staff s
    INNER JOIN staff_assignments sa ON s.staff_id = sa.staff_id
    GROUP BY s.staff_id, s.staff_number, s.name, s.role
    HAVING COUNT(sa.assignment_id) >= 1
    ORDER BY total_hours DESC
    LIMIT 10
";
$top_staff = $conn->query($top_staff_query);

// Assignments by role (GROUP BY)
$role_assignments_query = "
    SELECT 
        s.role,
        COUNT(sa.assignment_id) AS assignment_count,
        SUM(sa.hours_worked) AS total_hours,
        AVG(sa.hours_worked) AS avg_hours_per_assignment
    FROM staff s
    INNER JOIN staff_assignments sa ON s.staff_id = sa.staff_id
    GROUP BY s.role
    ORDER BY 
        CASE s.role
            WHEN 'Front Man' THEN 1
            WHEN 'Square' THEN 2
            WHEN 'Triangle' THEN 3
            WHEN 'Circle' THEN 4
        END
";
$role_assignments = $conn->query($role_assignments_query);

// Total statistics
$total_query = "
    SELECT 
        COUNT(DISTINCT sa.staff_id) AS unique_staff,
        COUNT(sa.assignment_id) AS total_assignments,
        SUM(sa.hours_worked) AS total_hours,
        AVG(sa.hours_worked) AS avg_hours
    FROM staff_assignments sa
";
$total_stats = $conn->query($total_query)->fetch_assoc();

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SquidSphere - Staff Assignments</title>
    <link rel="stylesheet" href="staff.css">
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="staff.php">Staff</a></li>
            <li><a href="staff_assignments.php" class="active">Assignments</a></li>
        </ul>
    </nav>

    <div class="staff-container">
        <h1 class="staff-title">Staff Assignment Tracking</h1>

        <!-- Assignment Statistics -->
        <div class="stats-summary">
            <div class="summary-card">
                <div class="summary-icon">👥</div>
                <div class="summary-value"><?php echo $total_stats['unique_staff']; ?></div>
                <div class="summary-label">Staff Assigned</div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">📋</div>
                <div class="summary-value"><?php echo $total_stats['total_assignments']; ?></div>
                <div class="summary-label">Total Assignments</div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">⏰</div>
                <div class="summary-value"><?php echo number_format($total_stats['total_hours'], 1); ?>h</div>
                <div class="summary-label">Total Hours</div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">📊</div>
                <div class="summary-value"><?php echo number_format($total_stats['avg_hours'], 1); ?>h</div>
                <div class="summary-label">Avg Hours/Assignment</div>
            </div>
        </div>

        <!-- Assignments by Game -->
        <div class="section-container">
            <h2 class="section-title">Assignments per Game (GROUP BY + INNER JOIN)</h2>
            <div class="game-assignments-grid">
                <?php while($game = $game_stats->fetch_assoc()): ?>
                    <div class="game-assignment-card">
                        <div class="game-header">
                            <div class="game-name"><?php echo $game['game_name']; ?></div>
                            <div class="round-badge">Round <?php echo $game['round_number']; ?></div>
                        </div>
                        <div class="game-type"><?php echo $game['game_type']; ?></div>
                        <div class="game-stats">
                            <div class="game-stat">
                                <span class="stat-icon">👥</span>
                                <span class="stat-value"><?php echo $game['total_staff']; ?></span>
                                <span class="stat-label">Staff</span>
                            </div>
                            <div class="game-stat">
                                <span class="stat-icon">⏰</span>
                                <span class="stat-value"><?php echo number_format($game['total_hours'], 1); ?>h</span>
                                <span class="stat-label">Total</span>
                            </div>
                            <div class="game-stat">
                                <span class="stat-icon">📊</span>
                                <span class="stat-value"><?php echo number_format($game['avg_hours'], 1); ?>h</span>
                                <span class="stat-label">Average</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Top Working Staff -->
        <div class="section-container">
            <h2 class="section-title">Top 10 Working Staff (ORDER BY + LIMIT)</h2>
            <div class="top-staff-grid">
                <?php 
                $rank = 1;
                while($staff = $top_staff->fetch_assoc()): 
                ?>
                    <div class="top-staff-card rank-<?php echo $rank; ?>">
                        <div class="rank-badge">#<?php echo $rank; ?></div>
                        <div class="staff-info">
                            <div class="staff-name"><?php echo htmlspecialchars($staff['name']); ?></div>
                            <div class="staff-number"><?php echo $staff['staff_number']; ?></div>
                            <span class="role-badge role-<?php echo strtolower($staff['role']); ?>">
                                <?php echo $staff['role']; ?>
                            </span>
                        </div>
                        <div class="staff-metrics">
                            <div class="metric">
                                <span class="metric-value"><?php echo $staff['total_assignments']; ?></span>
                                <span class="metric-label">Assignments</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value"><?php echo number_format($staff['total_hours'], 1); ?>h</span>
                                <span class="metric-label">Total Hours</span>
                            </div>
                        </div>
                    </div>
                <?php 
                    $rank++;
                endwhile; 
                ?>
            </div>
        </div>

        <!-- Assignments by Role -->
        <div class="section-container">
            <h2 class="section-title">Assignments by Role (GROUP BY)</h2>
            <div class="role-assignments-grid">
                <?php while($role = $role_assignments->fetch_assoc()): ?>
                    <div class="role-assignment-card">
                        <div class="role-icon">
                            <?php
                            $icons = [
                                'Front Man' => '👑',
                                'Square' => '🔲',
                                'Triangle' => '🔺',
                                'Circle' => '⭕'
                            ];
                            echo $icons[$role['role']];
                            ?>
                        </div>
                        <div class="role-name"><?php echo $role['role']; ?></div>
                        <div class="role-assignment-stats">
                            <div class="role-assignment-stat">
                                <span class="stat-label">Assignments:</span>
                                <span class="stat-value"><?php echo $role['assignment_count']; ?></span>
                            </div>
                            <div class="role-assignment-stat">
                                <span class="stat-label">Total Hours:</span>
                                <span class="stat-value"><?php echo number_format($role['total_hours'], 1); ?>h</span>
                            </div>
                            <div class="role-assignment-stat">
                                <span class="stat-label">Avg Hours:</span>
                                <span class="stat-value"><?php echo number_format($role['avg_hours_per_assignment'], 1); ?>h</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- All Assignments Table -->
        <div class="section-container">
            <h2 class="section-title">All Staff Assignments (INNER JOIN)</h2>
            <div class="hierarchy-table-container">
                <table class="hierarchy-table">
                    <thead>
                        <tr>
                            <th>Round</th>
                            <th>Game</th>
                            <th>Staff Number</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Assignment</th>
                            <th>Hours</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $assignments_result->data_seek(0);
                        while($assignment = $assignments_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="round-number">
                                    <span class="round-badge">Round <?php echo $assignment['round_number']; ?></span>
                                </td>
                                <td class="game-name"><?php echo $assignment['game_name']; ?></td>
                                <td class="staff-number"><?php echo $assignment['staff_number']; ?></td>
                                <td class="staff-name"><?php echo htmlspecialchars($assignment['staff_name']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo strtolower($assignment['staff_role']); ?>">
                                        <?php echo $assignment['staff_role']; ?>
                                    </span>
                                </td>
                                <td class="role-description"><?php echo htmlspecialchars($assignment['role_description']); ?></td>
                                <td class="hours-worked"><?php echo number_format($assignment['hours_worked'], 1); ?>h</td>
                                <td class="assignment-date"><?php echo date('M d, Y', strtotime($assignment['assignment_date'])); ?></td>
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
                    <div class="sql-title">INNER JOIN - Staff + Assignments + Games</div>
                    <code>SELECT s.name, g.game_name, sa.role_description<br>
FROM staff_assignments sa<br>
INNER JOIN staff s ON sa.staff_id = s.staff_id<br>
INNER JOIN games g ON sa.game_id = g.game_id</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">GROUP BY - Assignments per Game</div>
                    <code>SELECT g.game_name, COUNT(*) as staff_count<br>
FROM staff_assignments sa<br>
INNER JOIN games g ON sa.game_id = g.game_id<br>
GROUP BY g.game_id</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">ORDER BY + LIMIT - Top Workers</div>
                    <code>SELECT s.name, SUM(sa.hours_worked) as total<br>
FROM staff s<br>
INNER JOIN staff_assignments sa ON s.staff_id = sa.staff_id<br>
GROUP BY s.staff_id<br>
ORDER BY total DESC<br>
LIMIT 10</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">Aggregates - Work Statistics</div>
                    <code>SELECT role, COUNT(*) as assignments,<br>
SUM(hours_worked), AVG(hours_worked)<br>
FROM staff_assignments sa<br>
JOIN staff s ON sa.staff_id = s.staff_id<br>
GROUP BY role</code>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="nav-buttons">
            <a href="staff.php" class="nav-btn secondary">← Back to Staff Hierarchy</a>
            <a href="dashboard.php" class="nav-btn secondary">Dashboard</a>
        </div>
    </div>

    <style>
        /* Additional styles for assignments page */
        .game-assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .game-assignment-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            border: 2px solid rgba(215, 0, 120, 0.3);
            transition: all 0.3s ease;
        }

        .game-assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(215, 0, 120, 0.4);
        }

        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .game-name {
            font-size: 1.3rem;
            color: #d70078;
            font-weight: bold;
        }

        .round-badge {
            background: rgba(215, 0, 120, 0.2);
            color: #d70078;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .game-type {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .game-stats {
            display: flex;
            justify-content: space-around;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .game-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .game-stat .stat-icon {
            font-size: 1.5rem;
        }

        .game-stat .stat-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #d70078;
        }

        .game-stat .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .top-staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .top-staff-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            border: 2px solid rgba(215, 0, 120, 0.3);
            position: relative;
            transition: all 0.3s ease;
        }

        .top-staff-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(215, 0, 120, 0.4);
        }

        .top-staff-card.rank-1 {
            border-color: #ffd700;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }

        .top-staff-card.rank-2 {
            border-color: #c0c0c0;
            box-shadow: 0 0 20px rgba(192, 192, 192, 0.3);
        }

        .top-staff-card.rank-3 {
            border-color: #cd7f32;
            box-shadow: 0 0 20px rgba(205, 127, 50, 0.3);
        }

        .rank-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: linear-gradient(135deg, #d70078, #ff0090);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(215, 0, 120, 0.5);
        }

        .top-staff-card.rank-1 .rank-badge {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #000;
        }

        .top-staff-card.rank-2 .rank-badge {
            background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
            color: #000;
        }

        .top-staff-card.rank-3 .rank-badge {
            background: linear-gradient(135deg, #cd7f32, #e8a87c);
            color: #fff;
        }

        .staff-metrics {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .metric {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #d70078;
        }

        .metric-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }

        .role-assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .role-assignment-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .role-assignment-card:hover {
            transform: scale(1.05);
        }

        .role-assignment-stats {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .role-assignment-stat {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .role-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .hours-worked {
            color: #4caf50;
            font-weight: bold;
        }

        .assignment-date {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
    </style>
</body>
</html>
