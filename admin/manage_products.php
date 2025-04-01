<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include admin authentication check
require_once 'auth_check.php';
require_once '../db_connect.php';

// Handle product deletion if requested
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete related records first (images, tags, features)
        $conn->query("DELETE FROM product_images WHERE product_id = $product_id");
        $conn->query("DELETE FROM product_tags WHERE product_id = $product_id");
        $conn->query("DELETE FROM product_features WHERE product_id = $product_id");
        
        // Delete the product
        $conn->query("DELETE FROM products WHERE id = $product_id");
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_message'] = "Product deleted successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
    }
    
    // Redirect to refresh the page
    header("Location: manage_products.php");
    exit();
}

// Fetch all products with their main images
$sql = "SELECT p.*, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .product-image-placeholder {
            width: 70px;
            height: 70px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 12px;
        }
        
        .product-image-placeholder i {
            font-size: 24px;
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .status-in-stock {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-out-of-stock {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .status-backorder {
            background-color: #fff3cd;
            color: #664d03;
        }
        
        .status-pre-order {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .status-discontinued {
            background-color: #e9ecef;
            color: #343a40;
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
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h1 class="h3 mb-0">Manage Products</h1>
                    <a href="../postProduct.html" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add New Product
                    </a>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if ($row['image_path'] && file_exists('../' . $row['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars('../' . $row['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                                     class="product-image"
                                                     onerror="this.onerror=null; this.src='../assets/images/no-image.png';">
                                            <?php else: ?>
                                                <div class="product-image-placeholder">
                                                    <i class="fas fa-image"></i>
                                                    <span>No Image</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td class="fw-semibold text-primary">$<?php echo number_format($row['regular_price'], 2); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>
                                            <span class="badge status-badge status-<?php echo str_replace(' ', '-', $row['status']); ?>">
                                                <?php echo ucfirst(str_replace('-', ' ', $row['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No products found. <a href="../postProduct.html" class="fw-bold text-decoration-none">Add your first product</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile navigation toggle
        const mobileToggle = document.getElementById("mobile-toggle");
        const navLinks = document.getElementById("nav-links");

        mobileToggle.addEventListener("click", function () {
            navLinks.classList.toggle("active");
        });
    </script>
</body>
</html> 