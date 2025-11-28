<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get attendance statistics by group
$group_stats_stmt = $pdo->prepare("
    SELECT 
        u.student_group as 'group',
        COUNT(DISTINCT a.student_id) as total_students,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as total_present,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as total_absent,
        SUM(CASE WHEN a.status = 'justified' THEN 1 ELSE 0 END) as total_justified
    FROM users u
    LEFT JOIN attendance a ON u.id = a.student_id
    WHERE u.role = 'student'
    GROUP BY u.student_group
    ORDER BY u.student_group
");
$group_stats_stmt->execute();
$group_stats = $group_stats_stmt->fetchAll();

// Get justification statistics
$justification_stats = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count
    FROM justifications
    GROUP BY status
");
$justification_stats->execute();
$justifications = $justification_stats->fetchAll();

// Get attendance by course
$course_stats = $pdo->prepare("
    SELECT 
        c.course_name,
        COUNT(a.id) as total_records,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN a.status = 'justified' THEN 1 ELSE 0 END) as justified
    FROM courses c
    LEFT JOIN sessions s ON c.id = s.course_id
    LEFT JOIN attendance a ON s.id = a.session_id
    GROUP BY c.id, c.course_name
    ORDER BY c.course_name
");
$course_stats->execute();
$courses = $course_stats->fetchAll();
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-chart-pie"></i> System Statistics
</h2>

<!-- Group Attendance Chart -->
<div class="grid-container grid-2">
    <div class="card">
        <h3><i class="fas fa-chart-bar"></i> Attendance by Group</h3>
        <canvas id="groupChart"></canvas>
    </div>

    <div class="card">
        <h3><i class="fas fa-tasks"></i> Justification Status</h3>
        <canvas id="justificationChart"></canvas>
    </div>
</div>

<!-- Course Attendance Chart -->
<div class="card" style="margin-top: 2rem;">
    <h3><i class="fas fa-chart-line"></i> Attendance by Course</h3>
    <canvas id="courseChart"></canvas>
</div>

<!-- Detailed Tables -->
<div class="grid-container" style="margin-top: 2rem;">
    <div class="card">
        <h3>Statistics by Group</h3>
        <table class="data-table" style="font-size: 0.9rem;">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Justified</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($group_stats as $stat): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($stat['group'] ?? 'Unknown'); ?></strong></td>
                        <td><span class="badge badge-success"><?php echo $stat['total_present'] ?? 0; ?></span></td>
                        <td><span class="badge badge-danger"><?php echo $stat['total_absent'] ?? 0; ?></span></td>
                        <td><span class="badge badge-warning"><?php echo $stat['total_justified'] ?? 0; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Justification Status</h3>
        <table class="data-table" style="font-size: 0.9rem;">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($justifications as $just): ?>
                    <tr>
                        <td>
                            <strong><?php echo ucfirst($just['status']); ?></strong>
                        </td>
                        <td>
                            <?php 
                            $badge_class = match($just['status']) {
                                'approved' => 'badge-success',
                                'rejected' => 'badge-danger',
                                'pending' => 'badge-warning',
                                default => 'badge-light'
                            };
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo $just['count']; ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Group Attendance Chart
var groupChartCanvas = document.getElementById('groupChart').getContext('2d');
var groupChartData = {
    labels: [<?php echo implode(',', array_map(fn($s) => "'".$s['group']."'", $group_stats)); ?>],
    datasets: [
        {
            label: 'Present',
            data: [<?php echo implode(',', array_map(fn($s) => $s['total_present'] ?? 0, $group_stats)); ?>],
            backgroundColor: '#27ae60'
        },
        {
            label: 'Absent',
            data: [<?php echo implode(',', array_map(fn($s) => $s['total_absent'] ?? 0, $group_stats)); ?>],
            backgroundColor: '#e74c3c'
        },
        {
            label: 'Justified',
            data: [<?php echo implode(',', array_map(fn($s) => $s['total_justified'] ?? 0, $group_stats)); ?>],
            backgroundColor: '#f39c12'
        }
    ]
};

new Chart(groupChartCanvas, {
    type: 'bar',
    data: groupChartData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Justification Chart
var justificationCanvas = document.getElementById('justificationChart').getContext('2d');
var justificationData = {
    labels: [<?php echo implode(',', array_map(fn($j) => "'".ucfirst($j['status'])."'", $justifications)); ?>],
    datasets: [{
        data: [<?php echo implode(',', array_map(fn($j) => $j['count'], $justifications)); ?>],
        backgroundColor: ['#27ae60', '#e74c3c', '#f39c12']
    }]
};

new Chart(justificationCanvas, {
    type: 'doughnut',
    data: justificationData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Course Attendance Chart
var courseCanvas = document.getElementById('courseChart').getContext('2d');
var courseData = {
    labels: [<?php echo implode(',', array_map(fn($c) => "'".$c['course_name']."'", $courses)); ?>],
    datasets: [
        {
            label: 'Present',
            data: [<?php echo implode(',', array_map(fn($c) => $c['present'] ?? 0, $courses)); ?>],
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.1)'
        },
        {
            label: 'Absent',
            data: [<?php echo implode(',', array_map(fn($c) => $c['absent'] ?? 0, $courses)); ?>],
            borderColor: '#e74c3c',
            backgroundColor: 'rgba(231, 76, 60, 0.1)'
        }
    ]
};

new Chart(courseCanvas, {
    type: 'line',
    data: courseData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
