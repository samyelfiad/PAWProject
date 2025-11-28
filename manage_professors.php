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

// Add new professor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_professor') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!$first_name || !$last_name || !$email || !$password) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
                    VALUES (?, ?, ?, ?, 'professor')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
            $success = "Professor added successfully!";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "Email already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Delete professor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_professor') {
    $prof_id = $_POST['professor_id'] ?? null;
    if ($prof_id) {
        try {
            $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'professor'")->execute([$prof_id]);
            $success = "Professor deleted successfully!";
        } catch (PDOException $e) {
            $error = "Failed to delete professor: " . $e->getMessage();
        }
    }
}

// Get all professors
$stmt = $pdo->prepare("SELECT u.* , COUNT(c.id) as course_count 
                       FROM users u 
                       LEFT JOIN courses c ON u.id = c.professor_id 
                       WHERE u.role = 'professor' 
                       GROUP BY u.id 
                       ORDER BY u.last_name, u.first_name ASC");
$stmt->execute();
$professors = $stmt->fetchAll();
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-chalkboard-user"></i> Professor Management
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

<!-- Add Professor Form -->
<div class="card">
    <h3><i class="fas fa-plus"></i> Add New Professor</h3>
    
    <form method="POST" style="max-width: 600px;">
        <input type="hidden" name="action" value="add_professor">
        
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
            <input type="email" id="email" name="email" class="form-control" placeholder="professor@univ-alger.dz" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password <span style="color: var(--danger-color);">*</span></label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
        </div>
        
        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Professor
        </button>
    </form>
</div>

<!-- Professors List -->
<div class="card" style="margin-top: 2rem;">
    <h3><i class="fas fa-list"></i> Professors List (<?php echo count($professors); ?>)</h3>
    
    <?php if (count($professors) == 0): ?>
        <p style="color: #888; font-style: italic;">No professors yet.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Courses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professors as $prof): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($prof['last_name'] . ' ' . $prof['first_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($prof['email']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $prof['course_count'] ?? 0; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-small btn-danger" onclick="deleteProfessor(<?php echo $prof['id']; ?>, '<?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?>')">
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
            <input type="hidden" name="action" value="delete_professor">
            <input type="hidden" name="professor_id" id="deleteProfessorId" value="">
            
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
function deleteProfessor(profId, profName) {
    document.getElementById('deleteProfessorId').value = profId;
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete Prof. ' + profName + '? This action cannot be undone.';
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
