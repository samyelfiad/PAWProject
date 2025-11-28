<?php
require 'includes/db.php';

// Security: Only Students (BEFORE header include)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    header("Location: student_dashboard.php");
    exit;
}

include 'includes/header.php';

// Verify student can view this course
$stmt = $pdo->prepare("SELECT student_group FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$verify_sql = "SELECT COUNT(*) as cnt FROM courses c 
               JOIN sessions s ON c.id = s.course_id 
               WHERE c.id = ? AND (s.target_group = ? OR s.target_group = 'All')";
$verify_stmt = $pdo->prepare($verify_sql);
$verify_stmt->execute([$course_id, $student['student_group']]);
$verify = $verify_stmt->fetch();

if ($verify['cnt'] == 0) {
    header("Location: student_dashboard.php");
    exit;
}

// Get course info
$stmt = $pdo->prepare("SELECT c.*, u.first_name, u.last_name 
                       FROM courses c 
                       JOIN users u ON c.professor_id = u.id 
                       WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Handle justification submission
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_justification') {
    $session_id = $_POST['session_id'] ?? null;
    $reason = trim($_POST['reason'] ?? '');
    $file_path = null;
    
    if (!$session_id || !$reason) {
        $error = "Please provide session and reason.";
    } else {
        // Handle file upload if provided
        if (isset($_FILES['document']) && $_FILES['document']['size'] > 0) {
            $upload_dir = 'uploads/justifications/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = uniqid() . '_' . basename($_FILES['document']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (!move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
                $error = "Failed to upload file.";
            }
        }
        
        if (!isset($error)) {
            // Check if already has justification for this session
            $check = $pdo->prepare("SELECT id FROM justifications WHERE session_id = ? AND student_id = ?");
            $check->execute([$session_id, $student_id]);
            
            if ($check->fetch()) {
                $update_sql = "UPDATE justifications SET reason = ?, file_path = ?, status = 'pending', submitted_date = NOW() 
                               WHERE session_id = ? AND student_id = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$reason, $file_path, $session_id, $student_id]);
            } else {
                $insert_sql = "INSERT INTO justifications (session_id, student_id, reason, file_path, status, submitted_date) 
                               VALUES (?, ?, ?, ?, 'pending', NOW())";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([$session_id, $student_id, $reason, $file_path]);
            }
            
            $success_msg = "Justification submitted successfully!";
        }
    }
}

// Get all attendance records for this student in this course
$sql = "SELECT s.id as session_id, s.session_date, s.type, a.status, 
               j.status as justification_status, j.id as justification_id
        FROM sessions s
        LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
        LEFT JOIN justifications j ON s.id = j.session_id AND j.student_id = ?
        WHERE s.course_id = ?
        ORDER BY s.session_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id, $student_id, $course_id]);
$records = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="color: var(--primary-color); margin-bottom: 0.5rem;">
            <i class="fas fa-clipboard-list"></i> <?php echo htmlspecialchars($course['course_name']); ?>
        </h2>
        <p style="color: #666;">
            Prof. <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
        </p>
    </div>
    <a href="student_dashboard.php" class="btn" style="width: auto;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card">
    <h3>Attendance Records</h3>
    
    <?php if (count($records) == 0): ?>
        <p style="color: #888; font-style: italic;">No attendance records yet.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Justification</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo date('d M Y, H:i', strtotime($record['session_date'])); ?></td>
                        <td><span class="badge badge-light"><?php echo htmlspecialchars($record['type']); ?></span></td>
                        <td>
                            <?php 
                            $status = $record['status'] ?? 'pending';
                            $status_class = 'status-' . str_replace(' ', '-', strtolower($status));
                            echo "<span class='status-badge $status_class'>" . ucfirst($status) . "</span>";
                            ?>
                        </td>
                        <td>
                            <?php if ($record['justification_status']): ?>
                                <span class="badge badge-warning"><?php echo ucfirst($record['justification_status']); ?></span>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['status'] === 'absent' && !$record['justification_id']): ?>
                                <button class="btn btn-small" onclick="openJustificationModal(<?php echo $record['session_id']; ?>)">
                                    <i class="fas fa-paper-plane"></i> Submit
                                </button>
                            <?php elseif ($record['justification_id']): ?>
                                <button class="btn btn-small btn-warning" onclick="viewJustification(<?php echo $record['justification_id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Justification Modal -->
<div id="justificationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Submit Justification</h2>
            <button type="button" class="close-modal" onclick="closeJustificationModal()">&times;</button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateJustificationForm()">
            <input type="hidden" name="action" value="submit_justification">
            <input type="hidden" name="session_id" id="justificationSessionId" value="">
            
            <div class="form-group">
                <label for="reason">Reason for Absence <span style="color: var(--danger-color);">*</span></label>
                <textarea id="reason" name="reason" class="form-control" placeholder="Explain your absence..." required></textarea>
            </div>
            
            <div class="form-group">
                <label for="document">Supporting Document (Optional)</label>
                <input type="file" id="document" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small style="color: #666;">Max 5MB. Accepted: PDF, Images, Word docs</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success" style="flex: 1;">
                    <i class="fas fa-check"></i> Submit
                </button>
                <button type="button" class="btn btn-danger" style="flex: 1;" onclick="closeJustificationModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openJustificationModal(sessionId) {
    document.getElementById('justificationSessionId').value = sessionId;
    document.getElementById('reason').value = '';
    document.getElementById('document').value = '';
    document.getElementById('justificationModal').style.display = 'block';
}

function closeJustificationModal() {
    document.getElementById('justificationModal').style.display = 'none';
}

function validateJustificationForm() {
    var reason = document.getElementById('reason').value.trim();
    if (!reason || reason.length < 10) {
        alert('Please provide a reason with at least 10 characters.');
        return false;
    }
    
    var fileInput = document.getElementById('document');
    if (fileInput.files.length > 0) {
        var fileSize = fileInput.files[0].size;
        if (fileSize > 5 * 1024 * 1024) { // 5MB
            alert('File size must be less than 5MB');
            return false;
        }
    }
    
    return true;
}

function viewJustification(justificationId) {
    alert('View justification details for ID: ' + justificationId);
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('justificationModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
