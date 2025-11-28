<?php
// db_connect.php

// 1. Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Default WAMP user
define('DB_PASS', '');         // Default WAMP password (empty)
define('DB_NAME', 'student_db'); // The database you created

// 2. Connection Function
function getDBConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // Return JSON error immediately if connection fails
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "DB Connection failed: " . $e->getMessage()]);
        exit;
    }
}
?>