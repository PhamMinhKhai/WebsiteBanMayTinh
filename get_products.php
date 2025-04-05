<?php
// Start output buffering
ob_start();

require_once 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display, we'll log them instead

// Function to send JSON response and exit
function sendJsonResponse($data) {
    // Clear any previous output
    ob_clean();
    
    // Set JSON header
    header('Content-Type: application/json');
    
    // Send response
    echo json_encode($data);
    exit();
}

// Log incoming request
error_log("Received request: " . print_r($_GET, true));

// Check database connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
}

// Check if products table exists
$table_check = $conn->query("SHOW TABLES LIKE 'products'");
if ($table_check->num_rows == 0) {
    error_log("Products table does not exist");
    sendJsonResponse(['success' => false, 'message' => 'Products table does not exist']);
}

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'desktop';
$brand = isset($_GET['brand']) ? $_GET['brand'] : null;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 12;

// Log parameters
error_log("Parameters: category=$category, brand=$brand, min_price=$min_price, max_price=$max_price, sort=$sort, page=$page, per_page=$per_page");

// Calculate offset
$offset = ($page - 1) * $per_page;

// Build query
$sql = "SELECT * FROM products WHERE category = ?";
$params = [$category];
$types = "s";

if ($brand) {
    $sql .= " AND brand = ?";
    $params[] = $brand;
    $types .= "s";
}

if ($min_price !== null) {
    $sql .= " AND regular_price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price !== null) {
    $sql .= " AND regular_price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY regular_price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY regular_price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY created_at DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY rating DESC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

// Log the query for debugging
error_log("SQL Query: " . $sql);
error_log("Parameters: " . print_r($params, true));

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

if (!$stmt->bind_param($types, ...$params)) {
    error_log("Bind param failed: " . $stmt->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$result = $stmt->get_result();

// Get total count for pagination
$count_sql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$count_sql = preg_replace("/LIMIT.*$/", "", $count_sql);
$count_stmt = $conn->prepare($count_sql);
if (!$count_stmt) {
    error_log("Count prepare failed: " . $conn->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

if (!$count_stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2))) {
    error_log("Count bind param failed: " . $count_stmt->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $count_stmt->error]);
}

if (!$count_stmt->execute()) {
    error_log("Count execute failed: " . $count_stmt->error);
    sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $count_stmt->error]);
}

$total_count = $count_stmt->get_result()->fetch_assoc()['total'];

// Log total count
error_log("Total products found: " . $total_count);

// Prepare response
$response = [
    'success' => true,
    'products' => [],
    'pagination' => [
        'total' => $total_count,
        'per_page' => $per_page,
        'current_page' => $page,
        'total_pages' => ceil($total_count / $per_page)
    ]
];

// Process results
while ($row = $result->fetch_assoc()) {
    try {
        // Log the raw row data
        error_log("Processing row with ID: " . $row['id']);
        error_log("Raw row data: " . print_r($row, true));

        // Decode JSON fields with error checking
        $images = json_decode($row['images'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for images in product ID " . $row['id'] . ": " . json_last_error_msg());
            $images = [];
        }

        $tags = json_decode($row['tags'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for tags in product ID " . $row['id'] . ": " . json_last_error_msg());
            $tags = [];
        }

        $features = json_decode($row['features'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error for features in product ID " . $row['id'] . ": " . json_last_error_msg());
            $features = [];
        }

        // Update row with decoded data
        $row['images'] = $images;
        $row['tags'] = $tags;
        $row['features'] = $features;
        
        // Format price
        $row['regular_price'] = number_format($row['regular_price'], 2);
        
        // Log the processed product data
        error_log("Processed product data for ID " . $row['id'] . ": " . json_encode($row));
        
        $response['products'][] = $row;
    } catch (Exception $e) {
        error_log("Error processing row: " . $e->getMessage());
        continue;
    }
}

// Log the response for debugging
error_log("Response: " . json_encode($response));

// Send JSON response
sendJsonResponse($response);

// Function to get processor-specific products
function getProcessorProducts($conn, $filters = []) {
    $sql = "SELECT * FROM products WHERE category = 'processor'";
    $params = [];
    $types = "";

    // Add brand filter if specified
    if (!empty($filters['brand'])) {
        if (is_array($filters['brand'])) {
            $placeholders = str_repeat('?,', count($filters['brand']) - 1) . '?';
            $sql .= " AND brand IN ($placeholders)";
            $params = array_merge($params, $filters['brand']);
            $types .= str_repeat('s', count($filters['brand']));
        } else {
            $sql .= " AND brand = ?";
            $params[] = $filters['brand'];
            $types .= "s";
        }
    }

    // Add price range filter if specified
    if (!empty($filters['min_price'])) {
        $sql .= " AND regular_price >= ?";
        $params[] = $filters['min_price'];
        $types .= "d";
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND regular_price <= ?";
        $params[] = $filters['max_price'];
        $types .= "d";
    }

    // Add sorting
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_low':
                $sql .= " ORDER BY regular_price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY regular_price DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY created_at DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY rating DESC";
                break;
            default:
                $sql .= " ORDER BY created_at DESC";
        }
    } else {
        $sql .= " ORDER BY created_at DESC";
    }

    // Add pagination
    if (!empty($filters['page']) && !empty($filters['per_page'])) {
        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $filters['per_page'];
        $params[] = $offset;
        $types .= "ii";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['images'] = json_decode($row['images'], true) ?? [];
        $row['tags'] = json_decode($row['tags'], true) ?? [];
        $row['features'] = json_decode($row['features'], true) ?? [];
        $products[] = $row;
    }

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM products WHERE category = 'processor'";
    if (!empty($filters['brand'])) {
        if (is_array($filters['brand'])) {
            $placeholders = str_repeat('?,', count($filters['brand']) - 1) . '?';
            $count_sql .= " AND brand IN ($placeholders)";
        } else {
            $count_sql .= " AND brand = ?";
        }
    }
    if (!empty($filters['min_price'])) {
        $count_sql .= " AND regular_price >= ?";
    }
    if (!empty($filters['max_price'])) {
        $count_sql .= " AND regular_price <= ?";
    }

    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        throw new Exception("Count prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }

    if (!$count_stmt->execute()) {
        throw new Exception("Count execute failed: " . $count_stmt->error);
    }

    $total_count = $count_stmt->get_result()->fetch_assoc()['total'];

    return [
        'products' => $products,
        'pagination' => [
            'total' => $total_count,
            'per_page' => $filters['per_page'] ?? 12,
            'current_page' => $filters['page'] ?? 1,
            'total_pages' => ceil($total_count / ($filters['per_page'] ?? 12))
        ]
    ];
}

// Function to get monitor-specific products
function getMonitorProducts($conn, $filters = []) {
    $sql = "SELECT * FROM products WHERE category = 'monitor'";
    $params = [];
    $types = "";

    // Add brand filter if specified
    if (!empty($filters['brand'])) {
        if (is_array($filters['brand'])) {
            $placeholders = str_repeat('?,', count($filters['brand']) - 1) . '?';
            $sql .= " AND brand IN ($placeholders)";
            $params = array_merge($params, $filters['brand']);
            $types .= str_repeat('s', count($filters['brand']));
        } else {
            $sql .= " AND brand = ?";
            $params[] = $filters['brand'];
            $types .= "s";
        }
    }

    // Add price range filter if specified
    if (!empty($filters['min_price'])) {
        $sql .= " AND regular_price >= ?";
        $params[] = $filters['min_price'];
        $types .= "d";
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND regular_price <= ?";
        $params[] = $filters['max_price'];
        $types .= "d";
    }

    // Add sorting
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_low':
                $sql .= " ORDER BY regular_price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY regular_price DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY created_at DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY rating DESC";
                break;
            default:
                $sql .= " ORDER BY created_at DESC";
        }
    } else {
        $sql .= " ORDER BY created_at DESC";
    }

    // Add pagination
    if (!empty($filters['page']) && !empty($filters['per_page'])) {
        $offset = ($filters['page'] - 1) * $filters['per_page'];
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $filters['per_page'];
        $params[] = $offset;
        $types .= "ii";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['images'] = json_decode($row['images'], true) ?? [];
        $row['tags'] = json_decode($row['tags'], true) ?? [];
        $row['features'] = json_decode($row['features'], true) ?? [];
        $products[] = $row;
    }

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM products WHERE category = 'monitor'";
    if (!empty($filters['brand'])) {
        if (is_array($filters['brand'])) {
            $placeholders = str_repeat('?,', count($filters['brand']) - 1) . '?';
            $count_sql .= " AND brand IN ($placeholders)";
        } else {
            $count_sql .= " AND brand = ?";
        }
    }
    if (!empty($filters['min_price'])) {
        $count_sql .= " AND regular_price >= ?";
    }
    if (!empty($filters['max_price'])) {
        $count_sql .= " AND regular_price <= ?";
    }

    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        throw new Exception("Count prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }

    if (!$count_stmt->execute()) {
        throw new Exception("Count execute failed: " . $count_stmt->error);
    }

    $total_count = $count_stmt->get_result()->fetch_assoc()['total'];

    return [
        'products' => $products,
        'pagination' => [
            'total' => $total_count,
            'per_page' => $filters['per_page'] ?? 12,
            'current_page' => $filters['page'] ?? 1,
            'total_pages' => ceil($total_count / ($filters['per_page'] ?? 12))
        ]
    ];
}
?> 