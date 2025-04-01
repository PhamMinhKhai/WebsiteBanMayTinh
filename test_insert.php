<?php
require_once 'db_connect.php';

// Test direct insertion
$fullname = "Test User";
$email = "test" . time() . "@example.com"; // Unique email using timestamp
$password = password_hash("password123", PASSWORD_DEFAULT);

// Try direct query first
$sql = "INSERT INTO users (fullname, email, password, created_at) VALUES ('$fullname', '$email', '$password', NOW())";

echo "Attempting to insert test user...<br>";
if ($conn->query($sql)) {
    echo "Success! Test user inserted with ID: " . $conn->insert_id;
} else {
    echo "Error: " . $conn->error . "<br>";
    
    // Check if database and table exist
    $check_db = $conn->query("SELECT DATABASE()");
    $db_name = $check_db->fetch_row()[0];
    echo "Current database: " . $db_name . "<br>";
    
    $check_table = $conn->query("SHOW TABLES LIKE 'users'");
    if ($check_table->num_rows > 0) {
        echo "Users table exists<br>";
        
        // Display table structure
        $describe = $conn->query("DESCRIBE users");
        echo "Table structure:<br><pre>";
        while ($row = $describe->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Users table does not exist<br>";
    }
}
?> 