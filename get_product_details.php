<?php
// Start output buffering
ob_start();

require_once 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display, we'll log them instead

// Function to send JSON response
function sendJsonResponse($data) {
    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Log the incoming request
error_log("Received request for product details. GET params: " . print_r($_GET, true));

// Get product ID from request
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    error_log("No product ID provided");
    sendJsonResponse(['success' => false, 'message' => 'Product ID is required']);
}

// Check database connection
if (!isset($conn) || !$conn) {
    error_log("Database connection failed: " . (isset($conn) ? mysqli_connect_error() : "Connection not established"));
    sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
}

// Log the SQL query for debugging
$query = "SELECT * FROM products WHERE id = ?";
error_log("Executing query: " . $query . " with ID: " . $product_id);

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->bind_param("i", $product_id);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    sendJsonResponse(['success' => false, 'message' => 'Query execution failed: ' . $stmt->error]);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("No product found with ID: " . $product_id);
    sendJsonResponse(['success' => false, 'message' => 'Product not found']);
}

$product = $result->fetch_assoc();

// Log the raw product data
error_log("Raw product data: " . print_r($product, true));

// Check if required fields exist
$required_fields = ['name', 'category', 'brand', 'regular_price', 'quantity', 'status', 'description'];
foreach ($required_fields as $field) {
    if (!isset($product[$field])) {
        error_log("Missing required field: " . $field);
        sendJsonResponse(['success' => false, 'message' => 'Product data is incomplete']);
    }
}

// Decode JSON fields with error checking
$images = json_decode($product['images'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error decoding images JSON: " . json_last_error_msg());
    $product['images'] = [];
} else {
    $product['images'] = $images ?: [];
}

$tags = json_decode($product['tags'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error decoding tags JSON: " . json_last_error_msg());
    $product['tags'] = [];
} else {
    $product['tags'] = $tags ?: [];
}

$features = json_decode($product['features'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error decoding features JSON: " . json_last_error_msg());
    $product['features'] = [];
} else {
    $product['features'] = $features ?: [];
}

// Format price
$product['regular_price'] = number_format($product['regular_price'], 2);

// Log the final processed data
error_log("Processed product data: " . print_r($product, true));

sendJsonResponse([
    'success' => true,
    'product' => $product
]);
?> 