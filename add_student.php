<?php
// add_student.php
header('Content-Type: application/json');
require_once 'db_connect.php'; // Include the database connection

$pdo = getDBConnection();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect data from HTML form
    $lastName = $_POST['last-name'] ?? '';
    $firstName = $_POST['first-name'] ?? '';
    $email = $_POST['email'] ?? '';
    $group = $_POST['group'] ?? 'Group 1';

    // 2. Validation
    if (empty($lastName) || empty($firstName)) {
        echo json_encode(['status' => 'error', 'message' => 'Name fields are required']);
        exit;
    }

    // 3. Combine names (Since your DB has one 'name' column)
    $fullName = $lastName . ' ' . $firstName;

    try {
        // 4. Prepare SQL Insert
        // Note: We do NOT insert 'student_id' here. The database handles it automatically.
        $stmt = $pdo->prepare("INSERT INTO students (name, email, group_name) VALUES (?, ?, ?)");
        
        // 5. Execute
        $stmt->execute([$fullName, $email, $group]);
        
        // 6. Get the ID assigned by the database
        $newDbId = $pdo->lastInsertId();

        // 7. Return success with the new data
        $newStudentData = [
            'student-id' => $newDbId,
            'last-name' => $lastName,
            'first-name' => $firstName,
            'email' => $email,
            'group' => $group
        ];

        echo json_encode(['status' => 'success', 'message' => 'Student saved to DB', 'data' => $newStudentData]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
?>