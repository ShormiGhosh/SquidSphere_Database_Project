<?php
require_once 'config/db_config.php';
$conn = getDBConnection();

// Fetch staff hierarchy with images
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

// Function to get role image
function getRoleImage($role) {
    $images = [
        'Front Man' => 'images/frontMan.png',
        'Square' => 'images/staff_rect.png',
        'Triangle' => 'images/staff_triangle.png',
        'Circle' => 'images/staff_circle.png'
    ];
    return $images[$role] ?? 'images/staff_circle.png';
}

// Count staff by role
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

// Total stats
$total_staff_query = "SELECT COUNT(*) as total, SUM(salary) as total_payroll FROM staff WHERE status = 'active'";
$total_stats = $conn->query($total_staff_query)->fetch_assoc();

// Get organizational structure (Front Man -> Squares -> Triangles -> Circles)
$org_structure = [];
$org_query = "
    SELECT 
        s1.staff_id,
        s1.staff_number,
        s1.name,
        s1.role,
        s1.supervisor_id,
        COUNT(s2.staff_id) as subordinate_count
    FROM staff s1
    LEFT JOIN staff s2 ON s2.supervisor_id = s1.staff_id
    WHERE s1.status = 'active'
    GROUP BY s1.staff_id, s1.staff_number, s1.name, s1.role, s1.supervisor_id
    ORDER BY 
        CASE s1.role
            WHEN 'Front Man' THEN 1
            WHEN 'Square' THEN 2
            WHEN 'Triangle' THEN 3
            WHEN 'Circle' THEN 4
        END, s1.staff_number
";
$org_result = $conn->query($org_query);

while ($row = $org_result->fetch_assoc()) {
    if ($row['supervisor_id'] === null) {
        $org_structure['frontman'] = $row;
    } elseif ($row['role'] === 'Square') {
        $org_structure['squares'][] = $row;
    } elseif ($row['role'] === 'Triangle') {
        $org_structure['triangles'][] = $row;
    } else {
        $org_structure['circles'][] = $row;
    }
}

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SquidSphere - Staff Hierarchy</title>
    <link rel="stylesheet" href="staff_visual.css">
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
            <li><a href="staff_visual.php" class="active">Staff</a></li>
        </ul>
    </nav>

    <div class="staff-container">
        <h1 class="staff-title">Staff Hierarchy Management</h1>

        <!-- Statistics Summary -->
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
        </div>

        <!-- Visual Organizational Chart -->
        <div class="section-container org-chart-section">
            <h2 class="section-title">Organizational Hierarchy (SELF JOIN Visualization)</h2>
            
            <!-- Front Man Level -->
            <?php if (isset($org_structure['frontman'])): ?>
            <div class="hierarchy-level frontman-level">
                <div class="staff-card-visual frontman-card">
                    <img src="<?php echo getRoleImage('Front Man'); ?>" alt="Front Man" class="staff-image-large">
                    <div class="staff-card-info">
                        <div class="staff-card-number"><?php echo $org_structure['frontman']['staff_number']; ?></div>
                        <div class="staff-card-name"><?php echo htmlspecialchars($org_structure['frontman']['name']); ?></div>
                        <div class="staff-card-role">Front Man</div>
                        <div class="staff-card-subordinates">
                            <span class="subordinate-icon">👥</span>
                            <?php echo $org_structure['frontman']['subordinate_count']; ?> Direct Reports
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Square Guards Level -->
            <?php if (isset($org_structure['squares'])): ?>
            <div class="hierarchy-connector"></div>
            <div class="hierarchy-level squares-level">
                <div class="level-title">Square Guards</div>
                <div class="staff-grid">
                    <?php foreach ($org_structure['squares'] as $square): ?>
                    <div class="staff-card-visual square-card">
                        <img src="<?php echo getRoleImage('Square'); ?>" alt="Square Guard" class="staff-image">
                        <div class="staff-card-info">
                            <div class="staff-card-number"><?php echo $square['staff_number']; ?></div>
                            <div class="staff-card-name"><?php echo htmlspecialchars($square['name']); ?></div>
                            <div class="staff-card-subordinates">
                                <span class="subordinate-icon">👥</span>
                                <?php echo $square['subordinate_count']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Triangle Guards Level -->
            <?php if (isset($org_structure['triangles'])): ?>
            <div class="hierarchy-connector"></div>
            <div class="hierarchy-level triangles-level">
                <div class="level-title">Triangle Guards</div>
                <div class="staff-grid">
                    <?php foreach (array_slice($org_structure['triangles'], 0, 10) as $triangle): ?>
                    <div class="staff-card-visual triangle-card">
                        <img src="<?php echo getRoleImage('Triangle'); ?>" alt="Triangle Guard" class="staff-image">
                        <div class="staff-card-info">
                            <div class="staff-card-number"><?php echo $triangle['staff_number']; ?></div>
                            <div class="staff-card-name"><?php echo htmlspecialchars($triangle['name']); ?></div>
                            <div class="staff-card-subordinates">
                                <span class="subordinate-icon">👥</span>
                                <?php echo $triangle['subordinate_count']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Circle Guards Level -->
            <?php if (isset($org_structure['circles'])): ?>
            <div class="hierarchy-connector"></div>
            <div class="hierarchy-level circles-level">
                <div class="level-title">Circle Guards (Showing first 20)</div>
                <div class="staff-grid circles-grid">
                    <?php foreach (array_slice($org_structure['circles'], 0, 20) as $circle): ?>
                    <div class="staff-card-visual circle-card">
                        <img src="<?php echo getRoleImage('Circle'); ?>" alt="Circle Guard" class="staff-image-small">
                        <div class="staff-card-info-compact">
                            <div class="staff-card-number-small"><?php echo $circle['staff_number']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Role Distribution Cards -->
        <div class="section-container">
            <h2 class="section-title">Staff Distribution by Role (GROUP BY)</h2>
            <div class="role-cards-grid">
                <?php 
                $role_stats->data_seek(0);
                while($role = $role_stats->fetch_assoc()): 
                ?>
                    <div class="role-distribution-card role-<?php echo strtolower(str_replace(' ', '-', $role['role'])); ?>">
                        <img src="<?php echo getRoleImage($role['role']); ?>" alt="<?php echo $role['role']; ?>" class="role-card-image">
                        <div class="role-card-content">
                            <div class="role-card-title"><?php echo $role['role']; ?></div>
                            <div class="role-card-stats">
                                <div class="role-stat-item">
                                    <span class="stat-icon">👥</span>
                                    <span class="stat-value"><?php echo $role['total_count']; ?></span>
                                    <span class="stat-label">Staff</span>
                                </div>
                                <div class="role-stat-item">
                                    <span class="stat-icon">💰</span>
                                    <span class="stat-value">₩<?php echo number_format($role['avg_salary']); ?></span>
                                    <span class="stat-label">Avg Salary</span>
                                </div>
                                <div class="role-stat-item">
                                    <span class="stat-icon">💵</span>
                                    <span class="stat-value">₩<?php echo number_format($role['total_salary']); ?></span>
                                    <span class="stat-label">Total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Full Hierarchy Table -->
        <div class="section-container">
            <h2 class="section-title">Complete Staff Hierarchy Table (SELF JOIN)</h2>
            <div class="table-container">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Staff #</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Salary</th>
                            <th>Supervisor</th>
                            <th>Supervisor Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $staff_result->data_seek(0);
                        while($staff = $staff_result->fetch_assoc()): 
                        ?>
                            <tr class="staff-table-row">
                                <td>
                                    <img src="<?php echo getRoleImage($staff['staff_role']); ?>" 
                                         alt="<?php echo $staff['staff_role']; ?>" 
                                         class="table-staff-image">
                                </td>
                                <td class="staff-number"><?php echo $staff['staff_number']; ?></td>
                                <td class="staff-name"><?php echo htmlspecialchars($staff['staff_name']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo strtolower(str_replace(' ', '-', $staff['staff_role'])); ?>">
                                        <?php echo $staff['staff_role']; ?>
                                    </span>
                                </td>
                                <td class="staff-salary">₩<?php echo number_format($staff['salary']); ?></td>
                                <td class="supervisor-name">
                                    <?php echo $staff['supervisor_name'] ? htmlspecialchars($staff['supervisor_name']) : '<em>Top Level</em>'; ?>
                                </td>
                                <td>
                                    <?php if($staff['supervisor_role']): ?>
                                        <span class="role-badge role-<?php echo strtolower(str_replace(' ', '-', $staff['supervisor_role'])); ?>">
                                            <?php echo $staff['supervisor_role']; ?>
                                        </span>
                                    <?php else: ?>
                                        <em>-</em>
                                    <?php endif; ?>
                                </td>
                                <td>
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

        <!-- Navigation Buttons -->
        <div class="nav-buttons">
            <a href="staff_assignments.php" class="nav-btn">View Staff Assignments →</a>
            <a href="staff.php" class="nav-btn secondary">Text-Only View</a>
            <a href="dashboard.php" class="nav-btn secondary">← Dashboard</a>
        </div>
    </div>
</body>
</html>
