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

// Count total products
$total_products = $result ? $result->num_rows : 0;

// Count products by category
$category_counts = [];
if ($result && $result->num_rows > 0) {
    $temp_result = $conn->query("SELECT category, COUNT(*) as count FROM products GROUP BY category");
    if ($temp_result) {
        while ($row = $temp_result->fetch_assoc()) {
            $category_counts[$row['category']] = $row['count'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - TechHub Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #2ecc71;
            --danger-color: #e63946;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --text-muted: #6c757d;
        }
        
        body {
            background-color: #f1f5f9;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            left: 0;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar .brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 12px 20px;
            margin: 8px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        
        .page-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 4px;
            width: 50px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        /* Card hover animation disabled as per user preference */
        /* .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        } */
        
        .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.2rem 1.5rem;
            font-weight: 600;
            border: none;
        }
        
        .card-counter {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 1.5rem;
            background-color: #fff;
            height: 140px;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card-counter:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-counter:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(67, 97, 238, 0.3), rgba(76, 201, 240, 0.1));
            clip-path: circle(180px at 80% 20%);
            z-index: 0;
        }
        
        .card-counter .icon-container {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(67, 97, 238, 0.2);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-counter .icon-container i {
            font-size: 1.8rem;
            color: var(--primary-color);
        }
        
        .card-counter .count-numbers {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.2rem;
            z-index: 1;
            position: relative;
        }
        
        .card-counter .count-name {
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            font-weight: 600;
            color: var(--text-muted);
            z-index: 1;
            position: relative;
        }
        
        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .stats-card {
            flex: 1;
            min-width: 220px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            color: var(--dark-color);
            font-weight: 600;
            border: none;
            padding: 1rem 1.5rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin: 0 3px;
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        
        .btn-action.btn-primary {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border: none;
        }
        
        .btn-action.btn-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-action.btn-danger {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--danger-color);
            border: none;
        }
        
        .btn-action.btn-danger:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-add {
            background-color: #2ecc71;
            border: none;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
            border-radius: 8px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            border: 3px solid #fff;
        }
        
        .product-image-placeholder {
            width: 60px;
            height: 60px;
            background-color: #f8f9fa;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 11px;
        }
        
        .product-image-placeholder i {
            font-size: 22px;
            margin-bottom: 4px;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .status-in-stock {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }
        
        .status-out-of-stock {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--danger-color);
        }
        
        .status-backorder {
            background-color: rgba(255, 193, 7, 0.1);
            color: #f0ad4e;
        }
        
        .status-pre-order {
            background-color: rgba(72, 149, 239, 0.1);
            color: var(--accent-color);
        }
        
        .status-discontinued {
            background-color: #e9ecef;
            color: var(--text-muted);
        }
        
        .price-tag {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #e2e8f0;
        }
        
        .empty-state h4 {
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar .brand {
                padding: 1rem 0.5rem;
                text-align: center;
            }
            
            .sidebar .nav-link {
                padding: 15px 0;
                text-align: center;
                margin: 5px;
            }
            
            .sidebar .nav-link i {
                margin: 0;
                font-size: 1.3rem;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .media-body h4, .media-body p {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="brand py-4 px-4 mb-2">
                    <div class="media d-flex align-items-center">
                        <i class="fas fa-laptop-code mr-2" style="font-size: 1.8rem;"></i>
                        <div class="media-body">
                            <h4 class="m-0">TechHub</h4>
                            <p class="font-weight-light text-white-50 mb-0">Admin Panel</p>
                        </div>
                    </div>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_users.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_products.php" class="nav-link active">
                            <i class="fas fa-laptop"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_orders.php" class="nav-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a href="../logout.php" class="nav-link text-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title">Manage Products</h2>
                    <a href="../postProduct.html" class="btn btn-add">
                        <i class="fas fa-plus mr-2"></i>Add New Product
                    </a>
                </div>
                
                <!-- Stats Row -->
                <div class="stats-row mb-4">
                    <div class="stats-card">
                        <div class="card-counter">
                            <div class="count-numbers"><?php echo $total_products; ?></div>
                            <div class="count-name">Total Products</div>
                            <div class="icon-container">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                    </div>
                    
                    <?php foreach ($category_counts as $category => $count): ?>
                    <div class="stats-card">
                        <div class="card-counter">
                            <div class="count-numbers"><?php echo $count; ?></div>
                            <div class="count-name"><?php echo htmlspecialchars(ucfirst($category)); ?></div>
                            <div class="icon-container">
                                <i class="fas fa-tag"></i>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-box-open mr-2"></i>Products List</h5>
                        <span class="badge bg-light text-dark"><?php echo $total_products; ?> Total Products</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($result && $result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
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
                                        <td><div class="font-weight-medium"><?php echo htmlspecialchars($row['name']); ?></div></td>
                                        <td><span class="category-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                        <td class="price-tag">$<?php echo number_format($row['regular_price'], 2); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($row['status'])); ?>">
                                                <?php echo ucfirst(str_replace('-', ' ', $row['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_product" class="btn btn-action btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h4>No Products Found</h4>
                            <p>You haven't added any products yet.</p>
                            <a href="../postProduct.html" class="btn btn-add mt-2">
                                <i class="fas fa-plus mr-2"></i>Add Your First Product
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 