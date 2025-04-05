<?php
require_once 'db_connect.php';

// Set JSON response header
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get POST data
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$image_url = isset($_POST['image_url']) ? $_POST['image_url'] : '';
$specs = isset($_POST['specs']) ? $_POST['specs'] : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Log received data for debugging
error_log("Received POST data: " . print_r($_POST, true));

// Validate input
if ($product_id <= 0 || empty($product_name) || $price <= 0) {
    error_log("Invalid product data: id=$product_id, name=$product_name, price=$price");
    echo json_encode(['success' => false, 'message' => 'Invalid product data']);
    exit;
}

try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Generate or get session ID
    $session_id = session_id();
    error_log("Using session ID: " . $session_id);

    // Check if item already exists in cart
    $check_sql = "SELECT id, quantity FROM cart_items WHERE product_id = ? AND session_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("is", $product_id, $session_id);
    if (!$check_stmt->execute()) {
        throw new Exception("Execute failed: " . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if item exists
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $update_sql = "UPDATE cart_items SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        if (!$update_stmt->execute()) {
            throw new Exception("Execute failed: " . $update_stmt->error);
        }
    } else {
        // Insert new item
        $insert_sql = "INSERT INTO cart_items (product_id, product_name, price, quantity, image_url, specs, session_id) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $insert_stmt->bind_param("isdisss", $product_id, $product_name, $price, $quantity, $image_url, $specs, $session_id);
        if (!$insert_stmt->execute()) {
            throw new Exception("Execute failed: " . $insert_stmt->error);
        }
    }

    // Get updated cart count
    $count_sql = "SELECT SUM(quantity) as total FROM cart_items WHERE session_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $count_stmt->bind_param("s", $session_id);
    if (!$count_stmt->execute()) {
        throw new Exception("Execute failed: " . $count_stmt->error);
    }
    
    $count_result = $count_stmt->get_result();
    $cart_count = $count_result->fetch_assoc()['total'] ?? 0;

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cart_count' => $cart_count
    ]);
} catch (Exception $e) {
    // Log the error
    error_log("Error in add_to_cart.php: " . $e->getMessage());
    
    // Return error response with more details
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding the item to cart: ' . $e->getMessage()
    ]);
}
?> 