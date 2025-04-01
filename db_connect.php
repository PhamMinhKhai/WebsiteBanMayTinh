<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Check if this is your actual MySQL username
$password = ""; // Check if this is your actual MySQL password
$dbname = "techhub"; // Make sure this database exists

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>