<?php
$host = 'localhost';
$rootUser = 'root';
$rootPass = ''; 

try {
    $pdo = new PDO("mysql:host=$host", $rootUser, $rootPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS algiers_attendance");
    $pdo->exec("USE algiers_attendance");

    // Create Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50), last_name VARCHAR(50),
        email VARCHAR(100) UNIQUE, password VARCHAR(255),
        role ENUM('admin', 'professor', 'student'),
        student_group VARCHAR(20)
    )");

    // Create Courses Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(100), professor_id INT,
        FOREIGN KEY (professor_id) REFERENCES users(id)
    )");

    // Create Sessions Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT, session_date DATETIME,
        type VARCHAR(20), target_group VARCHAR(20),
        FOREIGN KEY (course_id) REFERENCES courses(id)
    )");

    // Create Attendance Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT, student_id INT,
        status ENUM('present', 'absent', 'justified'),
        FOREIGN KEY (session_id) REFERENCES sessions(id),
        FOREIGN KEY (student_id) REFERENCES users(id)
    )");

    // Create Justifications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS justifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT, student_id INT,
        reason TEXT, file_path VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        submitted_date DATETIME, approved_date DATETIME,
        reviewer_id INT, 
        FOREIGN KEY (session_id) REFERENCES sessions(id),
        FOREIGN KEY (student_id) REFERENCES users(id),
        FOREIGN KEY (reviewer_id) REFERENCES users(id)
    )");

    // Create Participation Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS participation (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT, session_id INT,
        participation_score INT DEFAULT 0,
        behavior_score INT DEFAULT 0,
        notes TEXT,
        FOREIGN KEY (student_id) REFERENCES users(id),
        FOREIGN KEY (session_id) REFERENCES sessions(id)
    )");

    // Create Course Groups Table (for multi-group enrollment)
    $pdo->exec("CREATE TABLE IF NOT EXISTS course_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT, course_id INT,
        enrolled_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_enrollment (student_id, course_id),
        FOREIGN KEY (student_id) REFERENCES users(id),
        FOREIGN KEY (course_id) REFERENCES courses(id)
    )");

    // Default Admin (Email: admin@univ-alger.dz / Pass: admin123)
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (first_name, last_name, email, password, role) 
                VALUES ('Admin', 'Main', 'admin@univ-alger.dz', '$pass', 'admin')");

    echo "<h1 style='color:green'>Database Setup Complete!</h1>";
    echo "<p>Default Admin: <b>admin@univ-alger.dz</b> / <b>admin123</b></p>";
    echo "<a href='index.php'>Go to Login</a>";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>