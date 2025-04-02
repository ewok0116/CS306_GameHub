<?php
// Database connection configuration
$servername = "localhost";
$username = "root";  // Most default installations use 'root'
$password = "";      // Many local installations have no password
$dbname = "gamehub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Log the error or handle it more gracefully
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Set charset to ensure proper character handling
$conn->set_charset("utf8mb4");
// No "Connected successfully" message
?>