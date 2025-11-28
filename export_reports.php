<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle export requests
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    header('Content-Type: text/csv');
    $timestamp = date('Y-m-d_H-i-s');
    
    switch ($export_type) {
        case 'students':
            header('Content-Disposition: attachment; filename="students_' . $timestamp . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Group']);
            
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, student_group FROM users WHERE role = 'student' ORDER BY last_name");
            $stmt->execute();
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            
            fclose($output);
            exit;
            
        case 'attendance':
            header('Content-Disposition: attachment; filename="attendance_report_' . $timestamp . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Session Date', 'Course', 'Student Name', 'Group', 'Status']);
            
            $stmt = $pdo->prepare("
                SELECT s.session_date, c.course_name, u.first_name, u.last_name, u.student_group, a.status
                FROM attendance a
                JOIN sessions s ON a.session_id = s.id
                JOIN courses c ON s.course_id = c.id
                JOIN users u ON a.student_id = u.id
                ORDER BY s.session_date DESC, u.last_name
            ");
            $stmt->execute();
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($row['session_date'])),
                    $row['course_name'],
                    $row['last_name'] . ' ' . $row['first_name'],
                    $row['student_group'],
                    ucfirst($row['status'])
                ]);
            }
            
            fclose($output);
            exit;
            
        case 'justifications':
            header('Content-Disposition: attachment; filename="justifications_' . $timestamp . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Student', 'Session Date', 'Course', 'Reason', 'Status', 'Submitted Date']);
            
            $stmt = $pdo->prepare("
                SELECT u.first_name, u.last_name, s.session_date, c.course_name, j.reason, j.status, j.submitted_date
                FROM justifications j
                JOIN users u ON j.student_id = u.id
                JOIN sessions s ON j.session_id = s.id
                JOIN courses c ON s.course_id = c.id
                ORDER BY j.submitted_date DESC
            ");
            $stmt->execute();
            
            while ($row = $stmt->fetch()) {
                fputcsv($output, [
                    $row['last_name'] . ' ' . $row['first_name'],
                    date('d/m/Y H:i', strtotime($row['session_date'])),
                    $row['course_name'],
                    $row['reason'],
                    ucfirst($row['status']),
                    date('d/m/Y H:i', strtotime($row['submitted_date']))
                ]);
            }
            
            fclose($output);
            exit;
    }
}

// Get statistics for preview
$stats = [];
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM users WHERE role = 'student'");
$stmt->execute();
$stats['students'] = $stmt->fetch()['cnt'];

$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM attendance");
$stmt->execute();
$stats['attendance_records'] = $stmt->fetch()['cnt'];

$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM justifications");
$stmt->execute();
$stats['justifications'] = $stmt->fetch()['cnt'];
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-download"></i> Export Reports
</h2>

<div class="grid-container grid-3">
    <!-- Export Students -->
    <div class="card">
        <h3 style="color: var(--secondary-color);">
            <i class="fas fa-users"></i> Students
        </h3>
        <p style="color: #666; margin-bottom: 1rem;">Export all student records in CSV format.</p>
        
        <div style="background: var(--light-gray); padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);">
                <?php echo $stats['students']; ?>
            </div>
            <small style="color: #666;">Student Records</small>
        </div>
        
        <a href="?export=students" class="btn btn-block" style="background: var(--secondary-color);">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <!-- Export Attendance -->
    <div class="card">
        <h3 style="color: var(--success-color);">
            <i class="fas fa-clipboard-list"></i> Attendance
        </h3>
        <p style="color: #666; margin-bottom: 1rem;">Export all attendance records in CSV format.</p>
        
        <div style="background: var(--light-gray); padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: bold; color: var(--success-color);">
                <?php echo $stats['attendance_records']; ?>
            </div>
            <small style="color: #666;">Attendance Records</small>
        </div>
        
        <a href="?export=attendance" class="btn btn-block" style="background: var(--success-color);">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <!-- Export Justifications -->
    <div class="card">
        <h3 style="color: var(--warning-color);">
            <i class="fas fa-file-alt"></i> Justifications
        </h3>
        <p style="color: #666; margin-bottom: 1rem;">Export all justification requests in CSV format.</p>
        
        <div style="background: var(--light-gray); padding: 1rem; border-radius: 4px; margin-bottom: 1rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: bold; color: var(--warning-color);">
                <?php echo $stats['justifications']; ?>
            </div>
            <small style="color: #666;">Justification Requests</small>
        </div>
        
        <a href="?export=justifications" class="btn btn-block" style="background: var(--warning-color);">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>
</div>

<!-- Info -->
<div class="card" style="margin-top: 2rem;">
    <h3><i class="fas fa-info-circle"></i> Export Information</h3>
    
    <p>
        Use these exports to:
    </p>
    <ul style="margin-left: 1.5rem; margin-top: 1rem;">
        <li>Create backups of your data</li>
        <li>Generate reports for administration</li>
        <li>Import data into other systems</li>
        <li>Analyze attendance patterns</li>
        <li>Track justification workflow</li>
    </ul>
    
    <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
        <i class="fas fa-lock"></i> <strong>Privacy Notice:</strong> Exported files contain sensitive information. Keep them secure and share only with authorized personnel.
    </p>
</div>

<?php include 'includes/footer.php'; ?>
