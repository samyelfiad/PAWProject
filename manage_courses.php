<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Admin
if ($_SESSION['role'] !== 'admin') header("Location: index.php");

// 1. Handle "Add Course" Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['course_name'];
    $prof_id = $_POST['professor_id'];
    
    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO courses (course_name, professor_id) VALUES (?, ?)");
    $stmt->execute([$name, $prof_id]);
    echo "<script>alert('Course added successfully!');</script>";
}

// 2. Handle "Delete" Link
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_courses.php");
}

// 3. Fetch Data for the dropdowns and table
$professors = $pdo->query("SELECT * FROM users WHERE role='professor'")->fetchAll();
$courses = $pdo->query("SELECT courses.*, users.last_name, users.first_name 
                        FROM courses 
                        JOIN users ON courses.professor_id = users.id")->fetchAll();
?>

<h2>Manage Courses</h2>

<div class="user-layout">
    <div class="card">
        <h3>Create New Course</h3>
        <form method="POST">
            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="course_name" class="form-control" placeholder="e.g. Advanced Web Dev" required>
            </div>
            
            <div class="form-group">
                <label>Assign Professor</label>
                <select name="professor_id" class="form-control" required>
                    <option value="">-- Select Professor --</option>
                    <?php foreach($professors as $p): ?>
                        <option value="<?php echo $p['id']; ?>">
                            Dr. <?php echo $p['last_name'] . " " . $p['first_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" name="add_course" class="btn">Create Course</button>
        </form>
    </div>

    <div class="card">
        <h3>Existing Courses</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Professor</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($courses as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                    <td>Dr. <?php echo htmlspecialchars($c['last_name']); ?></td>
                    <td>
                        <a href="?delete=<?php echo $c['id']; ?>" class="btn-delete" onclick="return confirm('Delete this course?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Quick CSS for this page layout */
    .user-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
    .btn-delete { color: #dc3545; text-decoration: none; font-weight: bold; }
</style>

<?php include 'includes/footer.php'; ?>