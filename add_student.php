<?php
header('Content-Type: application/json');

$file = 'students.json';

/* -------------------- GET : return all students -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (file_exists($file)) {
        $json = file_get_contents($file);
        echo $json;
    } else {
        echo json_encode([]);
    }
    exit;
}


/* -------------------- POST : add a new student -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect data
    $id = $_POST['student-id'] ?? '';
    $lastName = $_POST['last-name'] ?? '';
    $firstName = $_POST['first-name'] ?? '';
    $email = $_POST['email'] ?? '';
    $group = $_POST['group'] ?? 'Group 1';

    // Validation
    if (empty($id) || empty($lastName) || empty($firstName)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
        exit;
    }

    // Load existing students
    if (file_exists($file) && filesize($file) > 0) {
        $current_data = file_get_contents($file);
        $array_data = json_decode($current_data, true);
    } else {
        $array_data = [];
    }

    if (!is_array($array_data)) {
        $array_data = [];
    }

    // Add new student
    $new_student = [
        'student-id' => $id,
        'last-name' => $lastName,
        'first-name' => $firstName,
        'email' => $email,
        'group' => $group
    ];

    $array_data[] = $new_student;

    // Save (use LOCK_EX to avoid concurrent write issues)
    $saved = file_put_contents($file, json_encode($array_data, JSON_PRETTY_PRINT), LOCK_EX);

    if ($saved === false) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save student to file']);
        exit;
    }

    echo json_encode(['status' => 'success', 'message' => 'Student added successfully', 'data' => $new_student]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
?>
