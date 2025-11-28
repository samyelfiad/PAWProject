<?php
require 'includes/db.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'professor') header("Location: index.php");

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) header("Location: prof_dashboard.php");

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'] . ' ' . $_POST['time']; // Combine date and time
    $type = $_POST['type'];
    $group = $_POST['target_group'];

    // 1. Create the Session
    $sql = "INSERT INTO sessions (course_id, session_date, type, target_group) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id, $date, $type, $group]);
    $new_session_id = $pdo->lastInsertId();

    // 2. Pre-fill Attendance Records for all relevant students
    // If Group is "All", select all students, otherwise filter by group
    if ($group == 'All') {
        $student_sql = "SELECT id FROM users WHERE role = 'student'";
        $params = [];
    } else {
        $student_sql = "SELECT id FROM users WHERE role = 'student' AND student_group = ?";
        $params = [$group];
    }
    
    $students = $pdo->prepare($student_sql);
    $students->execute($params);
    
    // Insert empty attendance rows for them
    $insert_att = $pdo->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, 'absent')");
    while ($s = $students->fetch()) {
        $insert_att->execute([$new_session_id, $s['id']]);
    }
    
    // Redirect to the attendance sheet
    header("Location: take_attendance.php?session_id=" . $new_session_id);
    exit;
}
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h3>Start New Session</h3>
    <form method="POST">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>Time</label>
            <input type="time" name="time" class="form-control" required value="<?php echo date('H:i'); ?>">
        </div>
        <div class="form-group">
            <label>Session Type</label>
            <select name="type" class="form-control">
                <option value="Cours">Lecture (Cours)</option>
                <option value="TD">TD</option>
                <option value="TP">TP</option>
            </select>
        </div>
        <div class="form-group">
            <label>Target Group</label>
            <select name="target_group" class="form-control">
                <option value="All">All Groups (Lecture)</option>
                <option value="G1">Group 1</option>
                <option value="G2">Group 2</option>
                <option value="G3">Group 3</option>
            </select>
        </div>
        <button type="submit" class="btn">Create & Start Attendance</button>
        <a href="prof_dashboard.php" style="display:block; text-align:center; margin-top:10px; color:#666;">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>