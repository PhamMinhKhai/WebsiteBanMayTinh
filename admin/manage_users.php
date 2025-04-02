<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include admin authentication check
require_once 'auth_check.php';
require_once '../db_connect.php';

// Handle user role updates if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "User role updated successfully!";
    } else {
        $error_message = "Error updating user role: " . $conn->error;
    }
    $stmt->close();
}

// Pagination setup
$limit = 10; // users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get users with pagination
$stmt = $conn->prepare("SELECT id, fullname, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ?, ?");
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

// Get total users count for pagination
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - TechHub Admin</title>
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
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
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
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
            background-color: #fff;
        }
        
        .pagination {
            margin-top: 1.5rem;
            justify-content: center;
        }
        
        .page-link {
            color: var(--primary-color);
            border: none;
            padding: 0.5rem 1rem;
            margin: 0 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .page-link:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
        }
        
        .badge-admin {
            background-color: var(--danger-color);
            color: white;
        }
        
        .badge-user {
            background-color: var(--accent-color);
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
        
        .user-badge {
            display: inline-block;
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            color: var(--dark-color);
            text-align: center;
            line-height: 40px;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .date-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .role-admin {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--danger-color);
        }
        
        .role-user {
            background-color: rgba(72, 149, 239, 0.1);
            color: var(--accent-color);
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
                        <a href="manage_users.php" class="nav-link active">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_products.php" class="nav-link">
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
                    <h2 class="page-title">Manage Users</h2>
                    <a href="add_user.php" class="btn btn-add">
                        <i class="fas fa-plus mr-2"></i>Add New User
                    </a>
                </div>
                
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-friends mr-2"></i>Users List</h5>
                        <span class="badge bg-light text-dark"><?php echo $total_users; ?> Total Users</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registered Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="user-badge"><?php echo $user['id']; ?></span>
                                        </td>
                                        <td>
                                            <div class="font-weight-medium"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <form method="post" class="d-inline" id="role-form-<?php echo $user['id']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="role" class="form-control" onchange="document.getElementById('role-form-<?php echo $user['id']; ?>').submit();" style="width: auto;">
                                                    <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <input type="hidden" name="update_role" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <span class="date-badge">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-action btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-action btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="p-3">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
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