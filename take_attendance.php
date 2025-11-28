<?php
// take_attendance.php

// Allow cross-origin requests if necessary (optional for local development)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. Get the raw POST data (JSON)
$jsonInput = file_get_contents("php://input");
$requestData = json_decode($jsonInput, true);

// Check if data was received
if (!isset($requestData['attendance'])) {
    echo json_encode(["status" => "error", "message" => "No attendance data received."]);
    exit;
}

// 2. Define the filename with the current date (YYYY-MM-DD)
$currentDate = date('Y-m-d');
$filename = "attendance_" . $currentDate . ".json";

// 3. Check if the file already exists
if (file_exists($filename)) {
    // Requirement: Show "Attendance for today has already been taken."
    echo json_encode([
        "status" => "error", 
        "message" => "Attendance for today has already been taken."
    ]);
} else {
    // 4. Format and Save the data
    // The instructions say save array of: ["student_id" => "...", "status" => "..."]
    
    $attendanceList = $requestData['attendance'];
    
    // Convert array to formatted JSON (Pretty print for readability)
    $jsonData = json_encode($attendanceList, JSON_PRETTY_PRINT);
    
    if (file_put_contents($filename, $jsonData)) {
        echo json_encode([
            "status" => "success", 
            "message" => "Attendance saved successfully for $currentDate!"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Failed to write to file."
        ]);
    }
}
?>