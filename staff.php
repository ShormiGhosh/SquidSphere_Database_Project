<?php
require_once 'config/db_config.php';
$conn = getDBConnection();

// Fetch all staff with their supervisors (SELF JOIN)
$hierarchy_query = "
    SELECT 
        s1.staff_id,
        s1.staff_number,
        s1.name AS staff_name,
        s1.role AS staff_role,
        s1.salary,
        s1.hire_date,
        s1.status,
        s2.staff_number AS supervisor_number,
        s2.name AS supervisor_name,
        s2.role AS supervisor_role
    FROM staff s1
    LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id
    ORDER BY 
        CASE s1.role
            WHEN 'Front Man' THEN 1
            WHEN 'Square' THEN 2
            WHEN 'Triangle' THEN 3
            WHEN 'Circle' THEN 4
        END, s1.staff_number
";
$staff_result = $conn->query($hierarchy_query);

// Count staff by role (GROUP BY)
$role_count_query = "
    SELECT 
        role,
        COUNT(*) as total_count,
        AVG(salary) as avg_salary,
        SUM(salary) as total_salary
    FROM staff
    WHERE status = 'active'
    GROUP BY role
    ORDER BY 
        CASE role
            WHEN 'Front Man' THEN 1
            WHEN 'Square' THEN 2
            WHEN 'Triangle' THEN 3
            WHEN 'Circle' THEN 4
        END
";
$role_stats = $conn->query($role_count_query);

// Count subordinates per supervisor (GROUP BY + HAVING)
$subordinates_query = "
    SELECT 
        s2.staff_id,
        s2.staff_number,
        s2.name AS supervisor_name,
        s2.role AS supervisor_role,
        COUNT(s1.staff_id) AS subordinate_count
    FROM staff s1
    INNER JOIN staff s2 ON s1.supervisor_id = s2.staff_id
    WHERE s2.status = 'active'
    GROUP BY s2.staff_id, s2.staff_number, s2.name, s2.role
    HAVING COUNT(s1.staff_id) >= 2
    ORDER BY subordinate_count DESC
";
$supervisors_result = $conn->query($subordinates_query);

// Total staff statistics
$total_staff_query = "SELECT COUNT(*) as total, SUM(salary) as total_payroll FROM staff WHERE status = 'active'";
$total_stats = $conn->query($total_staff_query)->fetch_assoc();

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SquidSphere - Staff Hierarchy</title>
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
            <li><a href="staff.php" class="active">Staff</a></li>
        </ul>
    </nav>

    <div class="staff-container">
        <h1 class="staff-title">Staff Hierarchy Management</h1>

        <!-- Staff Statistics -->
        <div class="stats-summary">
            <div class="summary-card">
                <div class="summary-icon">👥</div>
                <div class="summary-value"><?php echo $total_stats['total']; ?></div>
                <div class="summary-label">Total Staff</div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">💰</div>
                <div class="summary-value">₩<?php echo number_format($total_stats['total_payroll']); ?></div>
                <div class="summary-label">Total Payroll</div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">📊</div>
                <div class="summary-value"><?php echo $supervisors_result->num_rows; ?></div>
                <div class="summary-label">Supervisors</div>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="section-container">
            <h2 class="section-title">Staff Distribution by Role (GROUP BY)</h2>
            <div class="role-grid">
                <?php while($role = $role_stats->fetch_assoc()): ?>
                    <div class="role-card role-<?php echo strtolower($role['role']); ?>">
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
                        <div class="role-stats">
                            <div class="role-stat">
                                <span class="stat-label">Count:</span>
                                <span class="stat-value"><?php echo $role['total_count']; ?></span>
                            </div>
                            <div class="role-stat">
                                <span class="stat-label">Avg Salary:</span>
                                <span class="stat-value">₩<?php echo number_format($role['avg_salary']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Hierarchy Tree -->
        <div class="section-container">
            <h2 class="section-title">Staff Hierarchy (SELF JOIN)</h2>
            <div class="hierarchy-table-container">
                <table class="hierarchy-table">
                    <thead>
                        <tr>
                            <th>Staff Number</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Salary</th>
                            <th>Supervisor</th>
                            <th>Supervisor Role</th>
                            <th>Hire Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $staff_result->data_seek(0); // Reset pointer
                        while($staff = $staff_result->fetch_assoc()): 
                        ?>
                            <tr class="staff-row role-<?php echo strtolower($staff['staff_role']); ?>">
                                <td class="staff-number"><?php echo $staff['staff_number']; ?></td>
                                <td class="staff-name"><?php echo htmlspecialchars($staff['staff_name']); ?></td>
                                <td class="staff-role">
                                    <span class="role-badge role-<?php echo strtolower($staff['staff_role']); ?>">
                                        <?php echo $staff['staff_role']; ?>
                                    </span>
                                </td>
                                <td class="staff-salary">₩<?php echo number_format($staff['salary']); ?></td>
                                <td class="supervisor-name">
                                    <?php echo $staff['supervisor_name'] ? htmlspecialchars($staff['supervisor_name']) : '<span class="no-supervisor">None (Top Level)</span>'; ?>
                                </td>
                                <td class="supervisor-role">
                                    <?php if($staff['supervisor_role']): ?>
                                        <span class="role-badge role-<?php echo strtolower($staff['supervisor_role']); ?>">
                                            <?php echo $staff['supervisor_role']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="no-supervisor">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="hire-date"><?php echo date('M d, Y', strtotime($staff['hire_date'])); ?></td>
                                <td class="status">
                                    <span class="status-badge status-<?php echo $staff['status']; ?>">
                                        <?php echo ucfirst($staff['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Supervisors with Most Subordinates -->
        <div class="section-container">
            <h2 class="section-title">Top Supervisors (GROUP BY + HAVING)</h2>
            <p class="section-subtitle">Showing supervisors with 2 or more subordinates</p>
            <div class="supervisors-grid">
                <?php 
                $supervisors_result->data_seek(0); // Reset pointer
                while($supervisor = $supervisors_result->fetch_assoc()): 
                ?>
                    <div class="supervisor-card">
                        <div class="supervisor-header">
                            <div class="supervisor-icon">
                                <?php
                                $sup_icons = [
                                    'Front Man' => '👑',
                                    'Square' => '🔲',
                                    'Triangle' => '🔺',
                                    'Circle' => '⭕'
                                ];
                                echo $sup_icons[$supervisor['supervisor_role']];
                                ?>
                            </div>
                            <div class="supervisor-info">
                                <div class="supervisor-name"><?php echo htmlspecialchars($supervisor['supervisor_name']); ?></div>
                                <div class="supervisor-number"><?php echo $supervisor['staff_number']; ?></div>
                            </div>
                        </div>
                        <div class="supervisor-role-badge">
                            <?php echo $supervisor['supervisor_role']; ?>
                        </div>
                        <div class="subordinate-count">
                            <span class="count-number"><?php echo $supervisor['subordinate_count']; ?></span>
                            <span class="count-label">Subordinates</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- SQL Queries Used -->
        <div class="section-container sql-section">
            <h2 class="section-title">SQL Queries Demonstration</h2>
            <div class="sql-grid">
                <div class="sql-card">
                    <div class="sql-title">SELF JOIN - Staff with Supervisors</div>
                    <code>SELECT s1.name, s1.role, s2.name AS supervisor<br>
FROM staff s1<br>
LEFT JOIN staff s2 ON s1.supervisor_id = s2.staff_id</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">GROUP BY - Count by Role</div>
                    <code>SELECT role, COUNT(*) as total, AVG(salary)<br>
FROM staff<br>
GROUP BY role</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">GROUP BY + HAVING - Top Supervisors</div>
                    <code>SELECT s2.name, COUNT(s1.staff_id) as subordinates<br>
FROM staff s1<br>
INNER JOIN staff s2 ON s1.supervisor_id = s2.staff_id<br>
GROUP BY s2.staff_id<br>
HAVING COUNT(s1.staff_id) >= 2</code>
                </div>
                <div class="sql-card">
                    <div class="sql-title">Aggregates - Payroll Statistics</div>
                    <code>SELECT role, SUM(salary) as total_payroll,<br>
MIN(salary), MAX(salary), AVG(salary)<br>
FROM staff<br>
GROUP BY role</code>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="nav-buttons">
            <a href="staff_assignments.php" class="nav-btn">View Staff Assignments →</a>
            <a href="dashboard.php" class="nav-btn secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
