<?php
require 'includes/db.php';
session_start();

// Read JSON Input
$input = file_get_contents('php://input');
$request = json_decode($input, true);

if (isset($request['session_id']) && isset($request['data'])) {
    
    $session_id = $request['session_id'];
    $attendance_list = $request['data'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE session_id = ? AND student_id = ?");

        foreach ($attendance_list as $record) {
            $stmt->execute([$record['status'], $session_id, $record['student_id']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid Data']);
}
?>