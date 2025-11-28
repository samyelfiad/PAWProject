<?php
// get_students.php
header('Content-Type: application/json');
require_once 'db_connect.php';

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // 1. Fetch all students from DB
        $stmt = $pdo->query("SELECT * FROM students ORDER BY student_id DESC");
        $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Format data for HTML
        
        // We split the string manually.
        $formattedList = [];

        foreach ($dbRows as $row) {
            // Split "Name Surname" into parts
            $nameParts = explode(' ', $row['name'], 2); 
            $lastName = $nameParts[0];
            $firstName = $nameParts[1] ?? ''; // Handle case if only one name exists

            $formattedList[] = [
                'student-id' => $row['student_id'], // Database ID
                'last-name' => $lastName,
                'first-name' => $firstName,
                'email' => $row['email'],
                'group' => $row['group_name']
            ];
        }

        // 3. Output JSON
        echo json_encode($formattedList);

    } catch (PDOException $e) {
        echo json_encode([]); // Return empty array on error so table doesn't crash
    }
    exit;
}
?>