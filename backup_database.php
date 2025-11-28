<?php
/**
 * Database Backup Script
 * Creates a SQL dump of the algiers_attendance database
 */

require 'includes/db.php';

$backup_file = 'backups/algiers_attendance_' . date('Y-m-d_H-i-s') . '.sql';

// Create backups directory if it doesn't exist
if (!is_dir('backups')) {
    mkdir('backups', 0755);
}

try {
    // Get all tables
    $tables = [];
    $result = $pdo->query("SHOW TABLES FROM algiers_attendance");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $backup_content = "-- Database Backup\n";
    $backup_content .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- Database: algiers_attendance\n";
    $backup_content .= "-- ---\n\n";

    foreach ($tables as $table) {
        $backup_content .= "-- Table: $table\n";
        $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Get CREATE TABLE statement
        $result = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch();
        $backup_content .= $row['Create Table'] . ";\n\n";
        
        // Get table data
        $result = $pdo->query("SELECT * FROM `$table`");
        while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
            $columns = implode('`, `', array_keys($data));
            $values = implode("', '", array_map(function($v) { return addslashes($v); }, $data));
            $backup_content .= "INSERT INTO `$table` (`$columns`) VALUES ('$values');\n";
        }
        $backup_content .= "\n";
    }

    // Write backup file
    if (file_put_contents($backup_file, $backup_content)) {
        echo "<h1 style='color: green;'>Backup Created Successfully!</h1>";
        echo "<p>File: <strong>$backup_file</strong></p>";
        echo "<p>Size: " . filesize($backup_file) . " bytes</p>";
        echo "<p><a href='$backup_file' download>Download Backup</a></p>";
    } else {
        echo "<h1 style='color: red;'>Failed to create backup!</h1>";
    }

} catch (Exception $e) {
    echo "<h1 style='color: red;'>Error: " . $e->getMessage() . "</h1>";
}
?>
