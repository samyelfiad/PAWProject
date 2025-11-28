<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Professor
if ($_SESSION['role'] !== 'professor') header("Location: index.php");

$prof_id = $_SESSION['user_id'];

// Get Courses assigned to this logged-in professor
$stmt = $pdo->prepare("SELECT * FROM courses WHERE professor_id = ?");
$stmt->execute([$prof_id]);
$my_courses = $stmt->fetchAll();
?>

<h2>Professor Dashboard</h2>
<p>Manage your courses and attendance sessions.</p>

<div class="courses-grid">
    <?php if(count($my_courses) == 0): ?>
        <div class="alert">No courses assigned yet. Please ask the Admin to assign you a course.</div>
    <?php endif; ?>

    <?php foreach($my_courses as $course): ?>
        <div class="card course-card">
            <h3 style="color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom:10px;">
                <?php echo htmlspecialchars($course['course_name']); ?>
            </h3>
            
            <div class="session-list">
                <h4>Recent Sessions:</h4>
                <?php
                // Get last 3 sessions for this course
                $s_stmt = $pdo->prepare("SELECT * FROM sessions WHERE course_id = ? ORDER BY session_date DESC LIMIT 3");
                $s_stmt->execute([$course['id']]);
                $sessions = $s_stmt->fetchAll();
                ?>
                
                <?php if(count($sessions) > 0): ?>
                    <ul>
                    <?php foreach($sessions as $s): ?>
                        <li>
                            <span><?php echo date('d M Y', strtotime($s['session_date'])); ?> (<?php echo $s['type']; ?>)</span>
                            <a href="take_attendance.php?session_id=<?php echo $s['id']; ?>" class="link-view">View</a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color:#888; font-style:italic;">No sessions yet.</p>
                <?php endif; ?>
            </div>

            <a href="add_session.php?course_id=<?php echo $course['id']; ?>" class="btn mt-3">+ New Session</a>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
    .course-card { background: white; border-top: 4px solid var(--primary-color); }
    .session-list ul { list-style: none; padding: 0; margin: 10px 0; }
    .session-list li { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
    .link-view { color: var(--primary-color); font-weight: bold; text-decoration: none; }
    .mt-3 { margin-top: 15px; display: block; text-align: center; }
</style>

<?php include 'includes/footer.php'; ?>