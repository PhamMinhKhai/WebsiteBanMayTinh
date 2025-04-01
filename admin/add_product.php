<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Function to handle image upload
function handle_image_upload($file, $product_id) {
    global $conn;
    
    $target_dir = "../uploads/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = $product_id . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Insert into database
        $image_path = "uploads/products/" . $new_filename;
        $is_main = isset($_POST['is_main']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $product_id, $image_path, $is_main);
        $stmt->execute();
        $stmt->close();
        
        return true;
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get and sanitize input data
        $name = sanitize_input($_POST['name']);
        $category = sanitize_input($_POST['category']);
        $brand = sanitize_input($_POST['brand']);
        $regular_price = floatval($_POST['regular_price']);
        $quantity = intval($_POST['quantity']);
        $status = sanitize_input($_POST['status']);
        $description = sanitize_input($_POST['description']);
        $specifications = sanitize_input($_POST['specifications']);
        
        // First check if SKU column exists in the table
        $result = $conn->query("SHOW COLUMNS FROM products LIKE 'sku'");
        $sku_exists = $result->num_rows > 0;
        
        // Prepare and execute product insertion
        if ($sku_exists) {
            // SKU column exists, include it in the query
            // Generate a unique SKU based on product name and timestamp to prevent duplicate errors
            $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10)) . time();
            
            $stmt = $conn->prepare("INSERT INTO products (name, sku, category, brand, regular_price, quantity, status, description, specifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssdisss", $name, $sku, $category, $brand, $regular_price, $quantity, $status, $description, $specifications);
        } else {
            // SKU column doesn't exist, use the version without it
            $stmt = $conn->prepare("INSERT INTO products (name, category, brand, regular_price, quantity, status, description, specifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssisss", $name, $category, $brand, $regular_price, $quantity, $status, $description, $specifications);
        }
        
        $stmt->execute();
        $product_id = $conn->insert_id;
        
        // Handle image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['images']['name'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $file_tmp = $_FILES['images']['tmp_name'][$key];
                $file_type = $_FILES['images']['type'][$key];

                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPG, PNG & GIF files are allowed.");
                }

                // Validate file size (max 5MB)
                if ($file_size > 5 * 1024 * 1024) {
                    throw new Exception("File size too large. Maximum size is 5MB.");
                }

                // Generate unique filename
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $unique_filename;

                // Move uploaded file
                if (!move_uploaded_file($file_tmp, $file_path)) {
                    throw new Exception("Failed to upload image.");
                }

                // Insert image record
                $image_path = 'uploads/products/' . $unique_filename;
                $is_main = ($key === 0) ? 1 : 0;
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $product_id, $image_path, $is_main);
                $stmt->execute();
            }
        }
        
        // Handle tags
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            $tag_stmt = $conn->prepare("INSERT INTO product_tags (product_id, tag) VALUES (?, ?)");
            foreach ($_POST['tags'] as $tag) {
                $tag = sanitize_input($tag);
                $tag_stmt->bind_param("is", $product_id, $tag);
                $tag_stmt->execute();
            }
            $tag_stmt->close();
        }
        
        // Handle features
        if (isset($_POST['features']) && is_array($_POST['features'])) {
            $feature_stmt = $conn->prepare("INSERT INTO product_features (product_id, feature) VALUES (?, ?)");
            foreach ($_POST['features'] as $feature) {
                $feature = sanitize_input($feature);
                $feature_stmt->bind_param("is", $product_id, $feature);
                $feature_stmt->execute();
            }
            $feature_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_message'] = "Product added successfully!";
        header("Location: manage_products.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error adding product: " . $e->getMessage();
        header("Location: ../postProduct.html");
        exit();
    }
} else {
    // If someone tries to access this file directly without POST data
    header("Location: ../postProduct.html");
    exit();
}
?> 