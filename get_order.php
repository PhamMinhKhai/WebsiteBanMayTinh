<?php
// Include database connection
require_once 'db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if order number is provided
if (!isset($_GET['order_number']) || empty($_GET['order_number'])) {
    echo json_encode(['success' => false, 'message' => 'Order number is required']);
    exit;
}

$order_number = $_GET['order_number'];

// Prepare the query to get order details
$stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name, c.email, c.phone, c.address, c.city, c.state, c.country
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.order_number = ?
");

$stmt->bind_param("s", $order_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Get order data
$order_data = $result->fetch_assoc();
$stmt->close();

// Get order items
$stmt = $conn->prepare("
    SELECT * FROM order_items
    WHERE order_id = ?
");

$stmt->bind_param("i", $order_data['id']);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];

while ($item = $items_result->fetch_assoc()) {
    $items[] = [
        'id' => $item['product_id'],
        'name' => $item['product_name'],
        'price' => (float)$item['price'],
        'quantity' => (int)$item['quantity'],
        'specs' => $item['specs']
    ];
}

$stmt->close();

// Format the order data for the response
$order = [
    'orderNumber' => $order_data['order_number'],
    'orderDate' => $order_data['created_at'],
    'status' => $order_data['status'],
    'customer' => [
        'firstName' => $order_data['first_name'],
        'lastName' => $order_data['last_name'],
        'email' => $order_data['email'],
        'phone' => $order_data['phone'],
        'address' => $order_data['address'],
        'city' => $order_data['city'],
        'state' => $order_data['state'],
        'country' => $order_data['country']
    ],
    'items' => $items,
    'subtotal' => (float)$order_data['subtotal'],
    'shipping' => (float)$order_data['shipping'],
    'tax' => (float)$order_data['tax'],
    'total' => (float)$order_data['total']
];

// Return the order data
echo json_encode(['success' => true, 'order' => $order]);

// Close connection
$conn->close();
?>