<?php
require_once 'db_connect.php';

// Check if remember_tokens table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'remember_tokens'")->num_rows > 0;

if (!$tableExists) {
    // Create remember_tokens table
    $sql = "CREATE TABLE remember_tokens (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        token VARCHAR(64) NOT NULL,
        expiry DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql) === TRUE) {
        echo "Table remember_tokens created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
}

// Don't close the connection as it will be used by the calling script
?>