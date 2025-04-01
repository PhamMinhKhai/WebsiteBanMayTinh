<?php
// Include database connection
require_once 'db_connect.php';

// First check if products table already exists
$table_exists = $conn->query("SHOW TABLES LIKE 'products'")->num_rows > 0;

if (!$table_exists) {
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL,
        brand VARCHAR(50) NOT NULL,
        regular_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        status ENUM('in-stock', 'out-of-stock', 'backorder', 'pre-order', 'discontinued') NOT NULL DEFAULT 'in-stock',
        description TEXT NOT NULL,
        specifications TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        echo "Products table created successfully<br>";
    } else {
        echo "Error creating products table: " . $conn->error . "<br>";
    }

    // Create product images table
    $sql = "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_main TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        echo "Product images table created successfully<br>";
    } else {
        echo "Error creating product images table: " . $conn->error . "<br>";
    }

    // Create product tags table
    $sql = "CREATE TABLE IF NOT EXISTS product_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        tag VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        echo "Product tags table created successfully<br>";
    } else {
        echo "Error creating product tags table: " . $conn->error . "<br>";
    }

    // Create product features table
    $sql = "CREATE TABLE IF NOT EXISTS product_features (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        feature VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        echo "Product features table created successfully<br>";
    } else {
        echo "Error creating product features table: " . $conn->error . "<br>";
    }
} else {
    // Check if any columns need to be altered
    $columns = $conn->query("SHOW COLUMNS FROM products");
    $column_names = [];
    
    while ($row = $columns->fetch_assoc()) {
        $column_names[] = $row['Field'];
    }
    
    // If sku exists, drop it
    if (in_array('sku', $column_names)) {
        if ($conn->query("ALTER TABLE products DROP COLUMN sku") === TRUE) {
            echo "SKU column removed successfully<br>";
        } else {
            echo "Error removing SKU column: " . $conn->error . "<br>";
        }
    }
    
    echo "Products tables already exist, structure checked and updated if needed.<br>";
}

echo "Setup complete!";
?> 