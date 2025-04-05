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

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid product ID";
    header("Location: manage_products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Process form submission for update
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
        
        // Update product information
        $stmt = $conn->prepare("UPDATE products SET 
            name = ?, 
            category = ?, 
            brand = ?, 
            regular_price = ?, 
            quantity = ?, 
            status = ?, 
            description = ?, 
            specifications = ? 
            WHERE id = ?");
            
        $stmt->bind_param("ssssisssi", $name, $category, $brand, $regular_price, 
                          $quantity, $status, $description, $specifications, $product_id);
        $stmt->execute();
        
        // Handle image uploads if new images are provided
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // If "replace_images" is checked, delete existing images
            if (isset($_POST['replace_images'])) {
                // Get existing image paths to delete files
                $img_result = $conn->query("SELECT image_path FROM product_images WHERE product_id = $product_id");
                while ($img_row = $img_result->fetch_assoc()) {
                    if (file_exists('../' . $img_row['image_path'])) {
                        unlink('../' . $img_row['image_path']);
                    }
                }
                
                // Delete image records from the database
                $conn->query("DELETE FROM product_images WHERE product_id = $product_id");
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (empty($_FILES['images']['name'][$key])) continue;
                
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
                $is_main = isset($_POST['replace_images']) && ($key === 0) ? 1 : 0;
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $product_id, $image_path, $is_main);
                $stmt->execute();
            }
        }
        
        // Update main image if requested
        if (isset($_POST['main_image_id']) && is_numeric($_POST['main_image_id'])) {
            $main_image_id = intval($_POST['main_image_id']);
            // Reset all images to not main
            $conn->query("UPDATE product_images SET is_main = 0 WHERE product_id = $product_id");
            // Set selected image as main
            $conn->query("UPDATE product_images SET is_main = 1 WHERE id = $main_image_id AND product_id = $product_id");
        }
        
        // Handle tags - Remove old tags first
        $conn->query("DELETE FROM product_tags WHERE product_id = $product_id");
        if (isset($_POST['tags']) && !empty($_POST['tags'])) {
            $tags = json_decode($_POST['tags'], true);
            if (is_array($tags)) {
                $tag_stmt = $conn->prepare("INSERT INTO product_tags (product_id, tag) VALUES (?, ?)");
                foreach ($tags as $tag) {
                    $tag = sanitize_input($tag);
                    $tag_stmt->bind_param("is", $product_id, $tag);
                    $tag_stmt->execute();
                }
                $tag_stmt->close();
            }
        }
        
        // Handle features - Remove old features first
        $conn->query("DELETE FROM product_features WHERE product_id = $product_id");
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
        
        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: manage_products.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
    }
}

// Fetch product data for the edit form
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Product not found";
    header("Location: manage_products.php");
    exit();
}

$product = $result->fetch_assoc();

// Fetch product images
$images_sql = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_main DESC, id ASC";
$images_result = $conn->query($images_sql);
$images = [];
while ($img = $images_result->fetch_assoc()) {
    $images[] = $img;
}

// Fetch product tags
$tags_sql = "SELECT tag FROM product_tags WHERE product_id = $product_id";
$tags_result = $conn->query($tags_sql);
$tags = [];
while ($tag = $tags_result->fetch_assoc()) {
    $tags[] = $tag['tag'];
}

// Fetch product features
$features_sql = "SELECT feature FROM product_features WHERE product_id = $product_id";
$features_result = $conn->query($features_sql);
$features = [];
while ($feature = $features_result->fetch_assoc()) {
    $features[] = $feature['feature'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .admin-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #ddd;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
        }
        .main-image-badge {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: rgba(0, 0, 255, 0.7);
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
        }
        .tag-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 46px;
        }
        .tag {
            display: flex;
            align-items: center;
            background-color: #f0f4ff;
            border: 1px solid #d0d9ff;
            border-radius: 16px;
            padding: 4px 10px;
            font-size: 13px;
        }
        .tag-text {
            margin-right: 6px;
        }
        .remove-tag {
            border: none;
            background: none;
            cursor: pointer;
            color: #666;
            font-size: 12px;
        }
        .tag-input {
            flex: 1;
            min-width: 60px;
            border: none;
            outline: none;
            font-size: 14px;
            padding: 4px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.html" class="logo">Tech<span>Hub</span></a>
            <div class="mobile-toggle" id="mobile-toggle">☰</div>

            <!-- Navigation Links -->
            <ul class="nav-links" id="nav-links">
                <li><a href="../index.html">Home</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="manage_users.php">Users</a></li>
                <li><a href="manage_products.php">Products</a></li>
                <li><a href="manage_orders.php">Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="admin-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Edit Product</h1>
                <a href="manage_products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Products
                </a>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="admin-form" action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="form-group">
                    <label class="form-label" for="product-name">Product Name*</label>
                    <input type="text" id="product-name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="product-category">Category*</label>
                    <select id="product-category" name="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php
                        $categories = [
                            'processors', 'motherboards', 'graphics-cards', 'memory', 
                            'storage', 'power-supplies', 'cooling', 'cases', 
                            'monitors', 'keyboards', 'mice', 'headsets', 'speakers'
                        ];
                        foreach ($categories as $cat) {
                            $selected = ($product['category'] === $cat) ? 'selected' : '';
                            echo "<option value=\"$cat\" $selected>" . ucfirst(str_replace('-', ' ', $cat)) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="product-brand">Brand*</label>
                    <select id="product-brand" name="brand" class="form-select" required>
                        <option value="">Select a brand</option>
                        <?php
                        $brands = [
                            'acer', 'amd', 'apple', 'asus', 'corsair', 'dell', 'gigabyte', 
                            'hp', 'intel', 'lenovo', 'logitech', 'msi', 'nvidia', 'razer', 
                            'samsung', 'other'
                        ];
                        foreach ($brands as $b) {
                            $selected = ($product['brand'] === $b) ? 'selected' : '';
                            echo "<option value=\"$b\" $selected>" . ucfirst($b) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Pricing Information -->
                <div class="form-group">
                    <label class="form-label" for="product-price">Regular Price ($)*</label>
                    <input type="number" id="product-price" name="regular_price" class="form-control" 
                           step="0.01" min="0" value="<?php echo $product['regular_price']; ?>" required>
                </div>

                <!-- Inventory Information -->
                <div class="form-group">
                    <label class="form-label" for="product-quantity">Stock Quantity*</label>
                    <input type="number" id="product-quantity" name="quantity" class="form-control" 
                           min="0" value="<?php echo $product['quantity']; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="product-status">Status*</label>
                    <select id="product-status" name="status" class="form-select" required>
                        <?php
                        $statuses = ['in-stock', 'out-of-stock', 'backorder', 'pre-order', 'discontinued'];
                        foreach ($statuses as $status) {
                            $selected = ($product['status'] === $status) ? 'selected' : '';
                            echo "<option value=\"$status\" $selected>" . ucfirst(str_replace('-', ' ', $status)) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Product Details -->
                <div class="form-group full-width">
                    <label class="form-label" for="product-description">Product Description*</label>
                    <textarea id="product-description" name="description" class="form-control" 
                              rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="product-specifications">Technical Specifications</label>
                    <textarea id="product-specifications" name="specifications" class="form-control" 
                              rows="5"><?php echo htmlspecialchars($product['specifications']); ?></textarea>
                </div>

                <!-- Image Management -->
                <div class="form-group full-width">
                    <label class="form-label">Current Images</label>
                    <div class="current-images mb-3">
                        <?php if (count($images) > 0): ?>
                            <?php foreach($images as $image): ?>
                                <div class="preview-item">
                                    <?php
                                        $image_path = '../' . $image['image_path'];
                                        if (file_exists($image_path)):
                                    ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>"
                                             alt="Product image">
                                    <?php else: ?>
                                        <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8f9fa;">
                                            <i class="fas fa-image" style="font-size:22px;margin-bottom:4px;color:#6c757d;"></i>
                                            <span style="font-size:11px;color:#6c757d;">No Image</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($image['is_main']): ?>
                                        <span class="main-image-badge">Main</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No images available</p>
                        <?php endif; ?>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="replace-images" name="replace_images">
                        <label class="form-check-label" for="replace-images">
                            Replace all current images with newly uploaded ones
                        </label>
                    </div>
                    <label class="form-label">Upload New Images</label>
                    <input type="file" id="product-images" name="images[]" accept="image/*" multiple class="form-control">
                    <small class="text-muted">Leave empty to keep current images. Upload up to 6 product images.</small>
                    <div id="image-preview" class="mt-3"></div>
                </div>

                <!-- Tags -->
                <div class="form-group full-width">
                    <label class="form-label" for="product-tags">Product Tags</label>
                    <div class="tag-input-container" id="tags-container">
                        <!-- Tags will be added here dynamically -->
                        <input type="text" id="tag-input" class="tag-input" placeholder="Type and press Enter to add tags">
                    </div>
                    <input type="hidden" name="tags" id="tags-input">
                </div>

                <!-- Product Features -->
                <div class="form-group full-width">
                    <label class="form-label">Special Features</label>
                    <div class="checkbox-group">
                        <?php
                        $feature_options = ['new', 'bestseller', 'sale', 'featured', 'limited'];
                        foreach ($feature_options as $feature) {
                            $checked = in_array($feature, $features) ? 'checked' : '';
                            echo "<div class='checkbox-item'>
                                    <input type='checkbox' id='feature-$feature' name='features[]' 
                                           value='$feature' $checked>
                                    <label for='feature-$feature'>" . ucfirst($feature === 'new' ? 'new arrival' : $feature) . "</label>
                                  </div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-group full-width">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="manage_products.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tags handling
            const tagsContainer = document.getElementById('tags-container');
            const tagInput = document.getElementById('tag-input');
            const tagsInputHidden = document.getElementById('tags-input');
            
            // Initialize with existing tags
            const existingTags = <?php echo json_encode($tags); ?>;
            existingTags.forEach(tag => addTag(tag));
            updateHiddenInput();

            tagInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && this.value.trim() !== '') {
                    e.preventDefault();
                    const tagValue = this.value.trim();
                    
                    // Check if tag already exists
                    const tags = Array.from(tagsContainer.querySelectorAll('.tag-text')).map(span => span.textContent);
                    if (!tags.includes(tagValue)) {
                        addTag(tagValue);
                        updateHiddenInput();
                        this.value = '';
                    }
                }
            });

            function addTag(tagText) {
                const tag = document.createElement('div');
                tag.className = 'tag';
                
                const span = document.createElement('span');
                span.className = 'tag-text';
                span.textContent = tagText;
                
                const removeButton = document.createElement('button');
                removeButton.className = 'remove-tag';
                removeButton.innerHTML = '×';
                removeButton.addEventListener('click', function() {
                    tag.remove();
                    updateHiddenInput();
                });
                
                tag.appendChild(span);
                tag.appendChild(removeButton);
                tagsContainer.insertBefore(tag, tagInput);
            }

            function updateHiddenInput() {
                const tags = Array.from(tagsContainer.querySelectorAll('.tag-text')).map(span => span.textContent);
                tagsInputHidden.value = JSON.stringify(tags);
            }
            
            // Image preview functionality
            const imageInput = document.getElementById('product-images');
            const imagePreview = document.getElementById('image-preview');
            
            imageInput.addEventListener('change', function() {
                imagePreview.innerHTML = '';
                
                if (this.files) {
                    const maxFiles = 6;
                    const files = Array.from(this.files).slice(0, maxFiles);
                    
                    files.forEach(file => {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const previewItem = document.createElement('div');
                            previewItem.className = 'preview-item';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            
                            previewItem.appendChild(img);
                            imagePreview.appendChild(previewItem);
                        };
                        
                        reader.readAsDataURL(file);
                    });
                }
            });
            
            // Mobile navigation toggle
            const mobileToggle = document.getElementById('mobile-toggle');
            const navLinks = document.getElementById('nav-links');
            
            mobileToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });
        });
    </script>
</body>
</html> 