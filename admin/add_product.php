<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include admin authentication check
require_once 'auth_check.php';
require_once '../db_connect.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle file uploads
function handleFileUpload($file) {
    $target_dir = "../uploads/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");

    if (!in_array($file_extension, $allowed_extensions)) {
        return array("success" => false, "message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    if ($file["size"] > 5000000) { // 5MB max
        return array("success" => false, "message" => "Sorry, your file is too large. Maximum size is 5MB.");
    }

    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array("success" => true, "path" => "uploads/products/" . $new_filename);
    } else {
        return array("success" => false, "message" => "Sorry, there was an error uploading your file.");
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Log the POST data for debugging
        error_log("POST Data: " . print_r($_POST, true));
        error_log("FILES Data: " . print_r($_FILES, true));

        // Get form data and sanitize inputs
        $name = sanitize_input($_POST['name']);
        $category = sanitize_input($_POST['category']);
        $brand = sanitize_input($_POST['brand']);
        $regular_price = floatval($_POST['regular_price']);
        $quantity = intval($_POST['quantity']);
        $status = sanitize_input($_POST['status']);
        $description = sanitize_input($_POST['description']);
        $specifications = sanitize_input($_POST['specifications']);
        
        // Handle tags
        $tags = [];
        if (isset($_POST['tags']) && is_string($_POST['tags'])) {
            $tags = json_decode($_POST['tags'], true) ?? [];
        }
        
        // Handle features
        $features = [];
        if (isset($_POST['features']) && is_array($_POST['features'])) {
            $features = $_POST['features'];
        }

        // Handle image uploads
        $image_paths = array();
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = array(
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    );
                    $upload_result = handleFileUpload($file);
                    if ($upload_result['success']) {
                        $image_paths[] = $upload_result['path'];
                    }
                }
            }
        }

        // Log the processed data
        error_log("Processed Data: " . print_r([
            'name' => $name,
            'category' => $category,
            'brand' => $brand,
            'regular_price' => $regular_price,
            'quantity' => $quantity,
            'status' => $status,
            'image_paths' => $image_paths,
            'tags' => $tags,
            'features' => $features
        ], true));

        // Prepare SQL statement
        $sql = "INSERT INTO products (name, category, brand, regular_price, quantity, status, description, specifications, images, tags, features) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Convert arrays to JSON strings
        $images_json = json_encode($image_paths);
        $tags_json = json_encode($tags);
        $features_json = json_encode($features);
        
        // Log JSON data
        error_log("JSON Data: " . print_r([
            'images_json' => $images_json,
            'tags_json' => $tags_json,
            'features_json' => $features_json
        ], true));
        
        // Bind parameters with correct types
        $stmt->bind_param("sssdiisssss", 
            $name,           // string
            $category,       // string
            $brand,         // string
            $regular_price, // double
            $quantity,      // integer
            $status,        // string
            $description,   // string
            $specifications,// string
            $images_json,   // string (JSON)
            $tags_json,     // string (JSON)
            $features_json  // string (JSON)
        );

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product added successfully!";
            header("Location: manage_products.php");
            exit();
        } else {
            throw new Exception("Error adding product: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in add_product.php: " . $e->getMessage());
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: ../postProduct.html");
        exit();
    }
} else {
    // If someone tries to access this file directly without POST data
    header("Location: ../postProduct.html");
    exit();
}
?> 