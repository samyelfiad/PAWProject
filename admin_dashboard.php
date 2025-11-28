<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get Statistics
$stats_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students,
        (SELECT COUNT(*) FROM users WHERE role = 'professor') as total_professors,
        (SELECT COUNT(*) FROM courses) as total_courses,
        (SELECT COUNT(*) FROM sessions) as total_sessions,
        (SELECT COUNT(*) FROM attendance WHERE status = 'present') as total_present,
        (SELECT COUNT(*) FROM attendance WHERE status = 'absent') as total_absent,
        (SELECT COUNT(*) FROM justifications WHERE status = 'pending') as pending_justifications
");
$stats_stmt->execute();
$stats = $stats_stmt->fetch();
// Get Statistics (wrapped in try/catch to handle missing tables gracefully)
$stats = [
    'total_students' => 0,
    'total_professors' => 0,
    'total_courses' => 0,
    'total_sessions' => 0,
    'total_present' => 0,
    'total_absent' => 0,
    'pending_justifications' => 0
];
$db_error = null;
try {
    $stats_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students,
        (SELECT COUNT(*) FROM users WHERE role = 'professor') as total_professors,
        (SELECT COUNT(*) FROM courses) as total_courses,
        (SELECT COUNT(*) FROM sessions) as total_sessions,
        (SELECT COUNT(*) FROM attendance WHERE status = 'present') as total_present,
        (SELECT COUNT(*) FROM attendance WHERE status = 'absent') as total_absent,
        (SELECT COUNT(*) FROM justifications WHERE status = 'pending') as pending_justifications
    ");
    $stats_stmt->execute();
    $f = $stats_stmt->fetch();
    if ($f) $stats = array_merge($stats, $f);
} catch (PDOException $e) {
    // store error to display a helpful message without breaking the page
    $db_error = $e->getMessage();
}
?>

<div style="margin-bottom: 2rem;">
    <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
        <i class="fas fa-chart-line"></i> Admin Dashboard
    </h2>
    <p style="color: #666;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>
</div>

<?php if (!empty($db_error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Database error detected: <?php echo htmlspecialchars($db_error); ?>
        <br>
        <small>Please run the <a href="setup.php">setup script</a> to initialize the database, or check your MySQL/MariaDB connection in <code>includes/db.php</code>.</small>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="grid-container grid-2">
    <div class="card" style="border-top: 4px solid var(--secondary-color); text-align: center;">
        <h4 style="color: #666; font-size: 0.9rem;">Total Students</h4>
        <div style="font-size: 2.5rem; font-weight: bold; color: var(--secondary-color); margin: 1rem 0;">
            <?php echo $stats['total_students'] ?? 0; ?>
        </div>
        <a href="manage_students.php" class="btn btn-small">Manage</a>
    </div>

    <div class="card" style="border-top: 4px solid #9b59b6; text-align: center;">
        <h4 style="color: #666; font-size: 0.9rem;">Total Professors</h4>
        <div style="font-size: 2.5rem; font-weight: bold; color: #9b59b6; margin: 1rem 0;">
            <?php echo $stats['total_professors'] ?? 0; ?>
        </div>
        <a href="manage_professors.php" class="btn btn-small">Manage</a>
    </div>

    <div class="card" style="border-top: 4px solid #16a085; text-align: center;">
        <h4 style="color: #666; font-size: 0.9rem;">Total Courses</h4>
        <div style="font-size: 2.5rem; font-weight: bold; color: #16a085; margin: 1rem 0;">
            <?php echo $stats['total_courses'] ?? 0; ?>
        </div>
        <a href="manage_courses.php" class="btn btn-small">Manage</a>
    </div>

    <div class="card" style="border-top: 4px solid #f39c12; text-align: center;">
        <h4 style="color: #666; font-size: 0.9rem;">Total Sessions</h4>
        <div style="font-size: 2.5rem; font-weight: bold; color: #f39c12; margin: 1rem 0;">
            <?php echo $stats['total_sessions'] ?? 0; ?>
        </div>
    </div>
</div>

<!-- Attendance Overview -->
<div class="grid-container grid-3" style="margin-top: 2rem;">
    <div class="card" style="text-align: center;">
        <h4 style="color: #155724; margin-bottom: 0.5rem;"><i class="fas fa-check-circle"></i> Present</h4>
        <div style="font-size: 2rem; font-weight: bold; color: var(--success-color); margin: 1rem 0;">
            <?php echo $stats['total_present'] ?? 0; ?>
        </div>
    </div>

    <div class="card" style="text-align: center;">
        <h4 style="color: #721c24; margin-bottom: 0.5rem;"><i class="fas fa-times-circle"></i> Absent</h4>
        <div style="font-size: 2rem; font-weight: bold; color: var(--danger-color); margin: 1rem 0;">
            <?php echo $stats['total_absent'] ?? 0; ?>
        </div>
    </div>

    <div class="card" style="text-align: center;">
        <h4 style="color: #856404; margin-bottom: 0.5rem;"><i class="fas fa-file-alt"></i> Pending Justifications</h4>
        <div style="font-size: 2rem; font-weight: bold; color: var(--warning-color); margin: 1rem 0;">
            <?php echo $stats['pending_justifications'] ?? 0; ?>
        </div>
        <?php if (($stats['pending_justifications'] ?? 0) > 0): ?>
            <a href="approve_justifications.php" class="btn btn-small btn-warning">Review</a>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 2rem;">
    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
    
    <div class="grid-container grid-2" style="margin-top: 1rem;">
        <a href="manage_students.php" class="btn" style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <i class="fas fa-user-plus"></i> Add Students
        </a>
        <a href="manage_professors.php" class="btn" style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <i class="fas fa-chalkboard-user"></i> Manage Professors
        </a>
        <a href="manage_courses.php" class="btn" style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <i class="fas fa-book-open"></i> Manage Courses
        </a>
        <a href="admin_statistics.php" class="btn" style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <i class="fas fa-chart-pie"></i> View Statistics
        </a>
        <a href="import_students.php" class="btn" style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <i class="fas fa-upload"></i> Import Students
        </a>
        <a href="export_reports.php" class="btn" style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <i class="fas fa-download"></i> Export Reports
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>