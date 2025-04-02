<?php
// Include admin authentication check
require_once 'auth_check.php';
require_once '../db_connect.php';

// Get total user count
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$stmt->execute();
$result = $stmt->get_result();
$total_users = $result->fetch_assoc()['total_users'];
$stmt->close();

// Get total admin count
$stmt = $conn->prepare("SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$total_admins = $result->fetch_assoc()['total_admins'];
$stmt->close();

// Get recent users (last 5 registered)
$stmt = $conn->prepare("SELECT id, fullname, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechHub</title>
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
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-top: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
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
        
        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .stats-card {
            flex: 1;
            min-width: 220px;
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
                        <a href="dashboard.php" class="nav-link active">
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
                <h2 class="page-title">Admin Dashboard</h2>
                
                <div class="stats-row">
                    <div class="stats-card">
                        <div class="card-counter">
                            <div class="count-numbers"><?php echo $total_users; ?></div>
                            <div class="count-name">Total Users</div>
                            <div class="icon-container">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card">
                        <div class="card-counter">
                            <div class="count-numbers"><?php echo $total_admins; ?></div>
                            <div class="count-name">Total Admins</div>
                            <div class="icon-container">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional stat cards -->
                    <div class="stats-card">
                        <div class="card-counter">
                            <div class="count-numbers">0</div>
                            <div class="count-name">Total Products</div>
                            <div class="icon-container">
                                <i class="fas fa-laptop"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card">
                        <div class="card-counter">
                            <div class="count-numbers">0</div>
                            <div class="count-name">Total Orders</div>
                            <div class="icon-container">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-user-plus mr-2"></i>Recent Users</h5>
                                <a href="manage_users.php" class="btn btn-sm btn-light">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Registered Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <span class="user-badge"><?php echo $user['id']; ?></span>
                                                </td>
                                                <td>
                                                    <div class="font-weight-medium"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
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
                            </div>
                        </div>
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