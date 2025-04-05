<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "root"; // Check if this is your actual MySQL username
$password = ""; // Check if this is your actual MySQL password
$dbname = "techhub"; // Make sure this database exists

// First connect without database selected
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname;";
if ($conn->query($sql) !== TRUE) {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Create cart_items table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    image_url VARCHAR(255),
    specs TEXT,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Execute the query and log the result
if ($conn->query($sql) === TRUE) {
    error_log("Table 'cart_items' created successfully or already exists.");
} else {
    error_log("Error creating table 'cart_items': " . $conn->error);
    // Optionally, you could output the error, but it might break JSON responses elsewhere
    // echo "Error creating table: " . $conn->error . "<br>"; 
}

// Check if session_id column exists
$result = $conn->query("SHOW COLUMNS FROM cart_items LIKE 'session_id'");
$exists = ($result->num_rows > 0);

if (!$exists) {
    // Add the session_id column if it doesn't exist
    $alter_sql = "ALTER TABLE cart_items ADD COLUMN session_id VARCHAR(255) NOT NULL";
    if ($conn->query($alter_sql) === TRUE) {
        error_log("Column 'session_id' added successfully to 'cart_items' table.");
    } else {
        error_log("Error adding column 'session_id': " . $conn->error);
    }
}

// --- Check and add other potentially missing columns ---

// Columns to check and their definitions
$columns_to_check = [
    'product_name' => 'VARCHAR(255) NOT NULL',
    'price' => 'DECIMAL(10,2) NOT NULL',
    'image_url' => 'VARCHAR(255)',
    'specs' => 'TEXT'
];

foreach ($columns_to_check as $column_name => $column_definition) {
    $result = $conn->query("SHOW COLUMNS FROM cart_items LIKE '$column_name'");
    $exists = ($result->num_rows > 0);

    if (!$exists) {
        $alter_sql = "ALTER TABLE cart_items ADD COLUMN $column_name $column_definition";
        if ($conn->query($alter_sql) === TRUE) {
            error_log("Column '$column_name' added successfully to 'cart_items' table.");
        } else {
            error_log("Error adding column '$column_name': " . $conn->error);
        }
    }
}

// --- Check and remove old foreign key and column if they exist ---

// Check and drop foreign key constraint `cart_items_ibfk_1`
$check_fk_sql = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'cart_items' AND CONSTRAINT_NAME = 'cart_items_ibfk_1'";
$stmt_fk = $conn->prepare($check_fk_sql);
if ($stmt_fk) {
    $stmt_fk->bind_param("s", $dbname);
    $stmt_fk->execute();
    $result_fk = $stmt_fk->get_result();
    if ($result_fk->num_rows > 0) {
        error_log("Foreign key 'cart_items_ibfk_1' found. Attempting to drop...");
        if ($conn->query("ALTER TABLE cart_items DROP FOREIGN KEY cart_items_ibfk_1") === TRUE) {
            error_log("Foreign key 'cart_items_ibfk_1' dropped successfully.");
        } else {
            error_log("Error dropping foreign key 'cart_items_ibfk_1': " . $conn->error);
        }
    }
    $stmt_fk->close();
} else {
    error_log("Error preparing statement to check foreign key: " . $conn->error);
}

// Check and drop `cart_id` column
$check_col_sql = "SHOW COLUMNS FROM cart_items LIKE 'cart_id'";
$result_col = $conn->query($check_col_sql);
if ($result_col && $result_col->num_rows > 0) {
    error_log("Column 'cart_id' found. Attempting to drop...");
    if ($conn->query("ALTER TABLE cart_items DROP COLUMN cart_id") === TRUE) {
        error_log("Column 'cart_id' dropped successfully.");
    } else {
        error_log("Error dropping column 'cart_id': " . $conn->error);
    }
}

?>