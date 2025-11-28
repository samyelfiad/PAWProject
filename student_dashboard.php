<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Students
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Get student info to find their group
try {
    $stmt = $pdo->prepare("SELECT student_group FROM users WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    $student_group = $student['student_group'] ?? 'G1';

    // Get enrolled courses (courses where the student's group is targeted)
    $sql = "SELECT DISTINCT c.id, c.course_name, u.first_name as prof_first, u.last_name as prof_last
            FROM courses c
            JOIN users u ON c.professor_id = u.id
            JOIN sessions s ON c.id = s.course_id
            WHERE s.target_group IN (?, 'All')
            ORDER BY c.course_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_group]);
    $courses = $stmt->fetchAll();

    // Remove duplicates
    $courses_unique = [];
    $course_ids = [];
    foreach ($courses as $course) {
        if (!in_array($course['id'], $course_ids)) {
            $courses_unique[] = $course;
            $course_ids[] = $course['id'];
        }
    }
    $courses = $courses_unique;
} catch (PDOException $e) {
    $courses = [];
    $db_error = "Database error: " . $e->getMessage();
}
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-book"></i> My Courses
</h2>

<?php if (isset($db_error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($db_error); ?>
    </div>
<?php endif; ?>

<?php if (count($courses) == 0): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You are not enrolled in any courses yet. Please contact your administrator.
    </div>
<?php else: ?>
    <div class="grid-container">
        <?php foreach ($courses as $course): ?>
            <?php
            // Get attendance statistics for this course
            $att_sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                        SUM(CASE WHEN a.status = 'justified' THEN 1 ELSE 0 END) as justified_count
                        FROM attendance a
                        JOIN sessions s ON a.session_id = s.id
                        WHERE s.course_id = ? AND a.student_id = ?";
            $att_stmt = $pdo->prepare($att_sql);
            $att_stmt->execute([$course['id'], $student_id]);
            $stats = $att_stmt->fetch();
            
            $total = $stats['total'] ?? 0;
            $present = $stats['present_count'] ?? 0;
            $absent = $stats['absent_count'] ?? 0;
            $justified = $stats['justified_count'] ?? 0;
            $attendance_rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            ?>
            <div class="card" style="border-top: 4px solid var(--secondary-color);">
                <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.95rem;">
                    <i class="fas fa-user"></i> 
                    Prof. <?php echo htmlspecialchars($course['prof_first'] . ' ' . $course['prof_last']); ?>
                </p>
                
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: bold;">Attendance Rate</span>
                        <span class="badge badge-info"><?php echo $attendance_rate; ?>%</span>
                    </div>
                    <div style="background: #ecf0f1; border-radius: 4px; height: 8px; overflow: hidden;">
                        <div style="background: var(--success-color); height: 100%; width: <?php echo $attendance_rate; ?>%; transition: width 0.3s;"></div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem;">
                    <div style="background: #d4edda; padding: 0.75rem; border-radius: 4px; text-align: center;">
                        <span style="color: #155724; font-weight: bold;"><?php echo $present; ?></span>
                        <br><small>Present</small>
                    </div>
                    <div style="background: #f8d7da; padding: 0.75rem; border-radius: 4px; text-align: center;">
                        <span style="color: #721c24; font-weight: bold;"><?php echo $absent; ?></span>
                        <br><small>Absent</small>
                    </div>
                    <div style="background: #fff3cd; padding: 0.75rem; border-radius: 4px; text-align: center;">
                        <span style="color: #856404; font-weight: bold;"><?php echo $justified; ?></span>
                        <br><small>Justified</small>
                    </div>
                    <div style="background: #d1ecf1; padding: 0.75rem; border-radius: 4px; text-align: center;">
                        <span style="color: #0c5460; font-weight: bold;"><?php echo $total; ?></span>
                        <br><small>Total</small>
                    </div>
                </div>
                
                <a href="my_attendance.php?course_id=<?php echo $course['id']; ?>" class="btn btn-block">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
