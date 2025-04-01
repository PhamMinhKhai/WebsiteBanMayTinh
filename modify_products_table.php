<?php
// Include database connection
require_once 'db_connect.php';

// Start transaction
$conn->begin_transaction();

try {
    // Check if the column exists before attempting to drop it
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'sku'");
    if ($result->num_rows > 0) {
        // Column exists, drop it
        $conn->query("ALTER TABLE products DROP COLUMN sku");
        echo "SKU column successfully removed from products table.<br>";
    } else {
        echo "SKU column does not exist in products table.<br>";
    }
    
    // Commit transaction
    $conn->commit();
    echo "Database update completed successfully!";
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo "Error updating database: " . $e->getMessage();
}
?> 