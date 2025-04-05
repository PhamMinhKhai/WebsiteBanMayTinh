<?php
require_once 'db_connect.php';

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop the table if it exists
$conn->query("DROP TABLE IF EXISTS products");

// Create the table with the correct structure
$sql = "CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    regular_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    specifications TEXT,
    images JSON,
    tags JSON,
    features JSON,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "Products table has been reset successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
?> 