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

// Handle justification approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $justification_id = $_POST['justification_id'] ?? null;
    $action = $_POST['action'];
    
    if ($justification_id) {
        try {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $sql = "UPDATE justifications SET status = ?, approved_date = NOW(), reviewer_id = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $_SESSION['user_id'], $justification_id]);
            
            $success = "Justification " . $status . " successfully!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get all justifications with their details
$sql = "SELECT j.id, j.status as current_status, j.submitted_date, j.reason, j.file_path,
               u.first_name, u.last_name, u.student_group,
               c.course_name, s.session_date, s.type,
               (SELECT COUNT(*) FROM justifications WHERE status = 'pending') as pending_count
        FROM justifications j
        JOIN users u ON j.student_id = u.id
        JOIN sessions s ON j.session_id = s.id
        JOIN courses c ON s.course_id = c.id
        ORDER BY j.current_status, j.submitted_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$justifications = $stmt->fetchAll();

// Separate by status
$pending = [];
$approved = [];
$rejected = [];

foreach ($justifications as $j) {
    if ($j['current_status'] === 'pending') {
        $pending[] = $j;
    } elseif ($j['current_status'] === 'approved') {
        $approved[] = $j;
    } else {
        $rejected[] = $j;
    }
}
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-check-square"></i> Manage Justifications
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

<!-- Status Tabs -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 1rem; border-bottom: 2px solid #ecf0f1; margin: -1.5rem -1.5rem 1.5rem -1.5rem; padding: 1.5rem 1.5rem 0 1.5rem;">
        <button class="tab-btn active" onclick="switchTab('pending')" style="padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; border-bottom: 3px solid var(--warning-color);">
            <i class="fas fa-hourglass"></i> Pending (<?php echo count($pending); ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('approved')" style="padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; color: #888;">
            <i class="fas fa-check"></i> Approved (<?php echo count($approved); ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('rejected')" style="padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; color: #888;">
            <i class="fas fa-times"></i> Rejected (<?php echo count($rejected); ?>)
        </button>
    </div>
</div>

<!-- Pending Justifications -->
<div id="pending-tab" class="tab-content">
    <?php if (count($pending) == 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No pending justifications.
        </div>
    <?php else: ?>
        <?php foreach ($pending as $j): ?>
            <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--warning-color);">
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($j['last_name'] . ' ' . $j['first_name']); ?>
                        </h4>
                        <p style="color: #666; font-size: 0.9rem; margin: 0.25rem 0;">
                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($j['course_name']); ?> | 
                            <i class="fas fa-calendar"></i> <?php echo date('d M Y, H:i', strtotime($j['session_date'])); ?> (<?php echo $j['type']; ?>)
                        </p>
                        <p style="color: #666; font-size: 0.9rem; margin: 0.25rem 0;">
                            <i class="fas fa-users"></i> Group: <?php echo htmlspecialchars($j['student_group']); ?> | 
                            <i class="fas fa-clock"></i> Submitted: <?php echo date('d M Y, H:i', strtotime($j['submitted_date'])); ?>
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </div>
                
                <div style="background: #f9f9f9; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <p style="color: #333; margin: 0; white-space: pre-wrap;">
                        <?php echo htmlspecialchars($j['reason']); ?>
                    </p>
                    <?php if ($j['file_path']): ?>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                            <i class="fas fa-paperclip"></i> 
                            <a href="<?php echo htmlspecialchars($j['file_path']); ?>" target="_blank" style="color: var(--secondary-color);">
                                View Attachment
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="justification_id" value="<?php echo $j['id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="justification_id" value="<?php echo $j['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Approved Justifications -->
<div id="approved-tab" class="tab-content" style="display: none;">
    <?php if (count($approved) == 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No approved justifications yet.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Session Date</th>
                    <th>Submitted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approved as $j): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($j['last_name'] . ' ' . $j['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($j['course_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($j['session_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($j['submitted_date'])); ?></td>
                        <td><span class="badge badge-success">Approved</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Rejected Justifications -->
<div id="rejected-tab" class="tab-content" style="display: none;">
    <?php if (count($rejected) == 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No rejected justifications.
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Session Date</th>
                    <th>Submitted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rejected as $j): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($j['last_name'] . ' ' . $j['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($j['course_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($j['session_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($j['submitted_date'])); ?></td>
                        <td><span class="badge badge-danger">Rejected</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.getElementById('pending-tab').style.display = 'none';
    document.getElementById('approved-tab').style.display = 'none';
    document.getElementById('rejected-tab').style.display = 'none';
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.color = '#888';
        btn.style.borderBottom = 'none';
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to clicked button
    event.target.style.color = '#333';
    event.target.style.borderBottom = '3px solid var(--warning-color)';
}
</script>

<?php include 'includes/footer.php'; ?>
