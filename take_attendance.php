<?php
<<<<<<< HEAD
// take_attendance.php

// Allow cross-origin requests if necessary (optional for local development)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. Get the raw POST data (JSON)
$jsonInput = file_get_contents("php://input");
$requestData = json_decode($jsonInput, true);

// Check if data was received
if (!isset($requestData['attendance'])) {
    echo json_encode(["status" => "error", "message" => "No attendance data received."]);
    exit;
}

// 2. Define the filename with the current date (YYYY-MM-DD)
$currentDate = date('Y-m-d');
$filename = "attendance_" . $currentDate . ".json";

// 3. Check if the file already exists
if (file_exists($filename)) {
    // Requirement: Show "Attendance for today has already been taken."
    echo json_encode([
        "status" => "error", 
        "message" => "Attendance for today has already been taken."
    ]);
} else {
    // 4. Format and Save the data
    // The instructions say save array of: ["student_id" => "...", "status" => "..."]
    
    $attendanceList = $requestData['attendance'];
    
    // Convert array to formatted JSON (Pretty print for readability)
    $jsonData = json_encode($attendanceList, JSON_PRETTY_PRINT);
    
    if (file_put_contents($filename, $jsonData)) {
        echo json_encode([
            "status" => "success", 
            "message" => "Attendance saved successfully for $currentDate!"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Failed to write to file."
        ]);
    }
}
?>
=======
require 'includes/db.php';
include 'includes/header.php';

if ($_SESSION['role'] !== 'professor') header("Location: index.php");

$session_id = $_GET['session_id'] ?? null;
if (!$session_id) header("Location: prof_dashboard.php");

// 1. Get Session Details
$stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
$stmt->execute([$session_id]);
$session = $stmt->fetch();

// 2. Get Students and their Status for this Session
// We join 'users' with 'attendance' to get the current status (defaults to 'absent' if we just created it)
$sql = "SELECT u.id as student_id, u.first_name, u.last_name, u.student_group, a.status 
        FROM attendance a 
        JOIN users u ON a.student_id = u.id 
        WHERE a.session_id = ? 
        ORDER BY u.last_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$students = $stmt->fetchAll();
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2><?php echo htmlspecialchars($session['course_name']); ?></h2>
            <p style="color:#666;">
                Date: <?php echo date('d M Y, H:i', strtotime($session['session_date'])); ?> | 
                Type: <?php echo $session['type']; ?> | 
                Group: <?php echo $session['target_group']; ?>
            </p>
        </div>
        <button id="save-btn" class="btn" style="width: auto; background-color: #28a745;">💾 Save Changes</button>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div id="message-box"></div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Group</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $s): ?>
            <tr class="student-row" data-student-id="<?php echo $s['student_id']; ?>">
                <td><?php echo htmlspecialchars($s['last_name'] . " " . $s['first_name']); ?></td>
                <td><?php echo htmlspecialchars($s['student_group']); ?></td>
                <td>
                    <div class="status-options">
                        <label>
                            <input type="radio" name="status_<?php echo $s['student_id']; ?>" value="present" 
                            <?php echo ($s['status'] == 'present') ? 'checked' : ''; ?>> 
                            <span class="badge-option present">Present</span>
                        </label>
                        <label>
                            <input type="radio" name="status_<?php echo $s['student_id']; ?>" value="absent" 
                            <?php echo ($s['status'] == 'absent') ? 'checked' : ''; ?>> 
                            <span class="badge-option absent">Absent</span>
                        </label>
                        <label>
                            <input type="radio" name="status_<?php echo $s['student_id']; ?>" value="justified" 
                            <?php echo ($s['status'] == 'justified') ? 'checked' : ''; ?>> 
                            <span class="badge-option justified">Justified</span>
                        </label>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    // Initialize badge states on page load
    updateBadgeStates();
    
    // When any radio button changes, update badge appearance
    $('input[type="radio"]').on('change', function() {
        updateBadgeStates();
    });
    
    function updateBadgeStates() {
        // For each status option group
        $('.status-options').each(function() {
            // Remove active class from all badges in this group
            $(this).find('.badge-option').removeClass('active');
            
            // Add active class to the checked option's badge
            var checkedInput = $(this).find('input:checked');
            if (checkedInput.length) {
                checkedInput.next('.badge-option').addClass('active');
            }
        });
    }
    
    // Save Button Click
    $('#save-btn').click(function() {
        var attendanceData = [];
        var sessionId = <?php echo $session_id; ?>;

        // Loop through all rows to gather data
        $('.student-row').each(function() {
            var studentId = $(this).data('student-id');
            var status = $(this).find('input[name="status_' + studentId + '"]:checked').val();
            
            attendanceData.push({
                student_id: studentId,
                status: status
            });
        });

        // Send AJAX Request
        $.ajax({
            url: 'ajax_save_attendance.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ session_id: sessionId, data: attendanceData }),
            success: function(response) {
                const res = JSON.parse(response);
                if(res.success) {
                    $('#message-box').html('<div class="alert" style="background:#d4edda; color:#155724;">Saved Successfully!</div>');
                    setTimeout(function(){ $('#message-box').empty(); }, 2000);
                } else {
                    alert('Error saving data');
                }
            },
            error: function() {
                alert('Connection Failed');
            }
        });
    });
});
</script>

<style>
    /* Status Badge Styles */
    .status-options label { cursor: pointer; margin-right: 5px; }
    .status-options input { display: none; } /* Hide radio buttons */
    
    .badge-option {
        padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc; color: #555; display: inline-block;
        transition: all 0.2s ease;
    }
    
    /* Active States - triggered by JavaScript */
    .badge-option.active.present { background-color: #d4edda; border-color: #28a745; color: #155724; font-weight: bold; }
    .badge-option.active.absent { background-color: #f8d7da; border-color: #dc3545; color: #721c24; font-weight: bold; }
    .badge-option.active.justified { background-color: #fff3cd; border-color: #ffc107; color: #856404; font-weight: bold; }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table td { padding: 12px; border-bottom: 1px solid #eee; }
</style>

<?php include 'includes/footer.php'; ?>
>>>>>>> 5dbdd8bdb6e08c7fce83fa18a4ab65f29177ffee
