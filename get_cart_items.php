<?php
require_once 'db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_id = session_id();

// Get cart items
$sql = "SELECT * FROM cart_items WHERE session_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['id'],
        'product_id' => $row['product_id'],
        'name' => $row['product_name'],
        'price' => (float)$row['price'],
        'quantity' => $row['quantity'],
        'image' => $row['image_url'],
        'specs' => $row['specs']
    ];
}

// Calculate totals
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

// Calculate shipping (example: free shipping over $50)
$shipping = $subtotal > 50 ? 0 : 5.99;

// Calculate tax (example: 6% tax rate)
$tax = $subtotal * 0.06;

// Calculate total
$total = $subtotal + $shipping + $tax;

// Return JSON response
echo json_encode([
    'success' => true,
    'items' => $items,
    'summary' => [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ]
]);
?> 