<?php
// Include database connection
require_once 'db_connect.php';

// Create tables if they don't exist
function createTables($conn) {
    // Customers table
    $sql_customers = "CREATE TABLE IF NOT EXISTS customers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(50) NOT NULL,
        state VARCHAR(50) NOT NULL,
        country VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Orders table
    $sql_orders = "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        customer_id INT(11) NOT NULL,
        order_number VARCHAR(20) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        shipping DECIMAL(10,2) NOT NULL,
        tax DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id)
    )";

    // Order items table
    $sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) NOT NULL,
        product_id VARCHAR(100) NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT(11) NOT NULL,
        specs TEXT,
        FOREIGN KEY (order_id) REFERENCES orders(id)
    )";

    // Execute queries
    if ($conn->query($sql_customers) !== TRUE) {
        echo "Error creating customers table: " . $conn->error . "<br>";
    }

    if ($conn->query($sql_orders) !== TRUE) {
        echo "Error creating orders table: " . $conn->error . "<br>";
    }

    if ($conn->query($sql_order_items) !== TRUE) {
        echo "Error creating order_items table: " . $conn->error . "<br>";
    }
}

// Call the function to create tables
createTables($conn);

// Process the order data
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate the data
if (!$data || !isset($data['customer']) || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert customer data
    $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone, address, city, state, country) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssss", 
        $data['customer']['firstName'], 
        $data['customer']['lastName'], 
        $data['customer']['email'], 
        $data['customer']['phone'], 
        $data['customer']['address'], 
        $data['customer']['city'], 
        $data['customer']['state'], 
        $data['customer']['country']
    );
    
    $stmt->execute();
    $customer_id = $conn->insert_id;
    $stmt->close();
    
    // Generate order number if not provided
    $order_number = isset($data['orderNumber']) ? $data['orderNumber'] : 'TH-' . mt_rand(100000, 999999);
    
    // Insert order data
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_number, subtotal, shipping, tax, total) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isdddd", 
        $customer_id, 
        $order_number, 
        $data['subtotal'], 
        $data['shipping'], 
        $data['tax'], 
        $data['total']
    );
    
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();
    
    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, specs) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        $stmt->bind_param("issdis", 
            $order_id, 
            $item['id'], 
            $item['name'], 
            $item['price'], 
            $item['quantity'], 
            $item['specs']
        );
        
        $stmt->execute();
    }
    
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Order saved successfully', 
        'orderNumber' => $order_number,
        'orderId' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close connection
$conn->close();
?>