<?php
// create_session.php
header('Content-Type: application/json');
require_once 'db_connect.php';

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get data from request
    // Assuming you send these via a form or AJAX
    $courseId = $_POST['course_id'] ?? 'Web Programming';
    $groupId = $_POST['group_id'] ?? 'Group 1';
    $profId = $_POST['professor_id'] ?? 1; // Default ID if none sent

    try {
        // 2. Insert new session
        $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, opened_by, status) VALUES (?, ?, ?, 'open')");
        $stmt->execute([$courseId, $groupId, $profId]);

        // 3. Get the new Session ID
        $sessionId = $pdo->lastInsertId();

        echo json_encode([
            "status" => "success", 
            "message" => "Session created successfully.",
            "session_id" => $sessionId
        ]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
?>