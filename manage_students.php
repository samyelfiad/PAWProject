<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$success = '';
$error = '';

// Add new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_student') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_group = trim($_POST['student_group'] ?? 'G1');
    $password = trim($_POST['password'] ?? '');
    
    if (!$first_name || !$last_name || !$email || !$password) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, email, password, role, student_group) 
                    VALUES (?, ?, ?, ?, 'student', ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $email, $hashed_password, $student_group]);
            $success = "Student added successfully!";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "Email already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Delete student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_student') {
    $student_id = $_POST['student_id'] ?? null;
    if ($student_id) {
        try {
            // Delete related records first
            $pdo->prepare("DELETE FROM attendance WHERE student_id = ?")->execute([$student_id]);
            $pdo->prepare("DELETE FROM justifications WHERE student_id = ?")->execute([$student_id]);
            $pdo->prepare("DELETE FROM participation WHERE student_id = ?")->execute([$student_id]);
            $pdo->prepare("DELETE FROM course_enrollments WHERE student_id = ?")->execute([$student_id]);
            
            // Delete student
            $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$student_id]);
            $success = "Student deleted successfully!";
        } catch (PDOException $e) {
            $error = "Failed to delete student: " . $e->getMessage();
        }
    }
}

// Get all students
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student' ORDER BY last_name, first_name ASC");
$stmt->execute();
$students = $stmt->fetchAll();
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-users"></i> Student Management
</h2>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Add Student Form -->
<div class="card">
    <h3><i class="fas fa-plus"></i> Add New Student</h3>
    
    <form method="POST" style="max-width: 600px;">
        <input type="hidden" name="action" value="add_student">
        
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name <span style="color: var(--danger-color);">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name <span style="color: var(--danger-color);">*</span></label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email <span style="color: var(--danger-color);">*</span></label>
            <input type="email" id="email" name="email" class="form-control" placeholder="student@univ-alger.dz" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="student_group">Group</label>
                <select id="student_group" name="student_group" class="form-control">
                    <option value="G1">Group 1 (G1)</option>
                    <option value="G2">Group 2 (G2)</option>
                    <option value="G3">Group 3 (G3)</option>
                    <option value="G4">Group 4 (G4)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password <span style="color: var(--danger-color);">*</span></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Student
        </button>
    </form>
</div>

<!-- Students List -->
<div class="card" style="margin-top: 2rem;">
    <h3><i class="fas fa-list"></i> Students List (<?php echo count($students); ?>)</h3>
    
    <?php if (count($students) == 0): ?>
        <p style="color: #888; font-style: italic;">No students yet.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Group</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($student['last_name'] . ' ' . $student['first_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($student['student_group'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-small btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button type="button" class="close-modal" onclick="closeDeleteModal()">&times;</button>
        </div>
        
        <p id="deleteMessage" style="margin-bottom: 1.5rem;"></p>
        
        <form method="POST">
            <input type="hidden" name="action" value="delete_student">
            <input type="hidden" name="student_id" id="deleteStudentId" value="">
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    <i class="fas fa-check"></i> Delete
                </button>
                <button type="button" class="btn btn-secondary" style="flex: 1; background: #95a5a6;" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function deleteStudent(studentId, studentName) {
    document.getElementById('deleteStudentId').value = studentId;
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete ' + studentName + '? This action cannot be undone.';
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
