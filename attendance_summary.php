<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Professors
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: index.php");
    exit;
}

$prof_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

// Get professor's courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE professor_id = ? ORDER BY course_name ASC");
$stmt->execute([$prof_id]);
$courses = $stmt->fetchAll();

// If course_id provided, verify it belongs to this professor
if ($course_id) {
    $verify = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
    $verify->execute([$course_id, $prof_id]);
    if (!$verify->fetch()) {
        $course_id = null;
    }
}

// Get selected course info if available
$selected_course = null;
if ($course_id) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND professor_id = ?");
    $stmt->execute([$course_id, $prof_id]);
    $selected_course = $stmt->fetch();
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="color: var(--primary-color);">
        <i class="fas fa-chart-bar"></i> Attendance Summary
    </h2>
    <a href="prof_dashboard.php" class="btn" style="width: auto;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Course Selector -->
<?php if (count($courses) > 0): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <h3>Select Course</h3>
        <div class="form-group">
            <select class="form-control" id="courseSelect" onchange="window.location.href='attendance_summary.php?course_id=' + this.value">
                <option value="">-- Choose a course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($course_id == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
<?php endif; ?>

<?php if ($selected_course): ?>
    <!-- Attendance Statistics -->
    <div class="grid-container grid-2">
        <?php
        // Get group-wise statistics
        $groups = ['G1', 'G2', 'G3', 'G4'];
        
        foreach ($groups as $group) {
            // Count statistics for this group
            $stat_sql = "SELECT 
                        COUNT(DISTINCT a.student_id) as total_records,
                        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                        SUM(CASE WHEN a.status = 'justified' THEN 1 ELSE 0 END) as justified_count
                        FROM attendance a
                        JOIN sessions s ON a.session_id = s.id
                        JOIN users u ON a.student_id = u.id
                        WHERE s.course_id = ? AND u.student_group = ?";
            $stat_stmt = $pdo->prepare($stat_sql);
            $stat_stmt->execute([$course_id, $group]);
            $stats = $stat_stmt->fetch();
            
            $total = $stats['total_records'] ?? 0;
            $present = $stats['present_count'] ?? 0;
            $absent = $stats['absent_count'] ?? 0;
            $justified = $stats['justified_count'] ?? 0;
            
            if ($total > 0):
            ?>
                <div class="card">
                    <h3 style="color: var(--secondary-color); margin-bottom: 1rem;">Group <?php echo $group; ?></h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                        <div style="background: #d4edda; padding: 1rem; border-radius: 4px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #155724;"><?php echo $present; ?></div>
                            <div style="color: #155724; font-size: 0.85rem;">Present</div>
                        </div>
                        <div style="background: #f8d7da; padding: 1rem; border-radius: 4px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #721c24;"><?php echo $absent; ?></div>
                            <div style="color: #721c24; font-size: 0.85rem;">Absent</div>
                        </div>
                        <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #856404;"><?php echo $justified; ?></div>
                            <div style="color: #856404; font-size: 0.85rem;">Justified</div>
                        </div>
                        <div style="background: #d1ecf1; padding: 1rem; border-radius: 4px; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #0c5460;"><?php echo $total; ?></div>
                            <div style="color: #0c5460; font-size: 0.85rem;">Total</div>
                        </div>
                    </div>
                    
                    <div style="background: #ecf0f1; border-radius: 4px; padding: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.85rem;">Attendance Rate</span>
                            <span style="font-weight: bold;"><?php echo ($total > 0) ? round(($present / $total) * 100, 1) : 0; ?>%</span>
                        </div>
                        <div style="background: white; border-radius: 3px; height: 10px; overflow: hidden;">
                            <div style="background: var(--success-color); height: 100%; width: <?php echo ($total > 0) ? ($present / $total) * 100 : 0; ?>%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php } ?>
    </div>
    
    <!-- Detailed Students Report by Group -->
    <div class="card" style="margin-top: 2rem;">
        <h3>Student Attendance Details</h3>
        
        <?php
        $groups_with_data = [];
        foreach (['G1', 'G2', 'G3', 'G4'] as $group) {
            $check_sql = "SELECT COUNT(DISTINCT u.id) as cnt FROM users u
                         WHERE u.student_group = ? AND u.role = 'student'
                         AND u.id IN (
                            SELECT DISTINCT a.student_id FROM attendance a
                            JOIN sessions s ON a.session_id = s.id
                            WHERE s.course_id = ?
                         )";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$group, $course_id]);
            if ($check_stmt->fetch()['cnt'] > 0) {
                $groups_with_data[] = $group;
            }
        }
        
        foreach ($groups_with_data as $group):
            $students_sql = "SELECT u.id, u.first_name, u.last_name,
                            COUNT(a.id) as total_sessions,
                            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_sessions,
                            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_sessions,
                            SUM(CASE WHEN a.status = 'justified' THEN 1 ELSE 0 END) as justified_sessions
                            FROM users u
                            LEFT JOIN attendance a ON u.id = a.student_id
                            LEFT JOIN sessions s ON a.session_id = s.id AND s.course_id = ?
                            WHERE u.student_group = ? AND u.role = 'student'
                            GROUP BY u.id
                            ORDER BY u.last_name, u.first_name";
            $students_stmt = $pdo->prepare($students_sql);
            $students_stmt->execute([$course_id, $group]);
            $group_students = $students_stmt->fetchAll();
        ?>
            <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; color: var(--secondary-color);">Group <?php echo $group; ?></h4>
            
            <table class="data-table" style="font-size: 0.9rem; margin-bottom: 1.5rem;">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Total Sessions</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Justified</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($group_students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['last_name'] . ' ' . $student['first_name']); ?></td>
                            <td><?php echo $student['total_sessions'] ?? 0; ?></td>
                            <td><span class="badge badge-success"><?php echo $student['present_sessions'] ?? 0; ?></span></td>
                            <td><span class="badge badge-danger"><?php echo $student['absent_sessions'] ?? 0; ?></span></td>
                            <td><span class="badge badge-warning"><?php echo $student['justified_sessions'] ?? 0; ?></span></td>
                            <td>
                                <?php 
                                $total = $student['total_sessions'] ?? 0;
                                $percent = $total > 0 ? round(($student['present_sessions'] ?? 0) / $total * 100, 1) : 0;
                                echo "<strong>$percent%</strong>";
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Select a course to view attendance summary.
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
