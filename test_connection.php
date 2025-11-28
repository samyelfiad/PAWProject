<?php
// test_connection.php

// Include the connection logic
require_once 'db_connect.php';

// Attempt to get the connection
$conn = getDBConnection();

// If the script didn't "die" inside getDBConnection, it means we were successful
if ($conn) {
    echo "<h2 style='color: green;'>Connection successful!</h2>";
    echo "You are connected to database: " . DB_NAME;
} else {
    // This part theoretically won't be reached because of the die() in the catch block,
    // but it's good practice.
    echo "<h2 style='color: red;'>Connection failed</h2>";
}
?>