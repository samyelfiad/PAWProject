<?php
// test_sessions.php
// This script simulates the "Manual Testing" required by the exercise.

$url = "http://localhost/Paw/create_session.php"; // UPDATE THIS PATH to your actual folder name

// Helper function to send POST request using PHP (cURL)
function createTestSession($course, $group, $prof) {
    global $url;
    $data = ['course_id' => $course, 'group_id' => $group, 'professor_id' => $prof];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}

echo "<h2>Testing Session Creation</h2>";

// Test 1
echo "<h3>Creating Session 1 (Web Dev, Group 1)...</h3>";
$response1 = createTestSession("Web Development", "Group 1", 101);
echo "Response: " . $response1 . "<hr>";

// Test 2
echo "<h3>Creating Session 2 (Databases, Group 2)...</h3>";
$response2 = createTestSession("Databases", "Group 2", 102);
echo "Response: " . $response2 . "<hr>";

// Test 3
echo "<h3>Creating Session 3 (JavaScript, Group 1)...</h3>";
$response3 = createTestSession("JavaScript", "Group 1", 101);
echo "Response: " . $response3 . "<hr>";

echo "<p>Check your <strong>attendance_sessions</strong> table in phpMyAdmin to confirm 3 rows were added!</p>";
?>