<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: loginRegister.html");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, fullname, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Collect and sanitize input data
    $fullname = trim(htmlspecialchars($_POST['fullname']));
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    // Validate name
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    // If trying to change password
    if (!empty($new_password)) {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
        
        if (empty($current_password) || !password_verify($current_password, $user_data['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        
        // Confirm passwords match
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        if (!empty($new_password)) {
            // Update name and password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $fullname, $hashed_password, $user_id);
        } else {
            // Update only name
            $stmt = $conn->prepare("UPDATE users SET fullname = ? WHERE id = ?");
            $stmt->bind_param("si", $fullname, $user_id);
        }
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Update session user name
            $_SESSION['user_fullname'] = $fullname;
            // Refresh user data
            $user['fullname'] = $fullname;
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - TechHub</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            object-fit: cover;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .list-group-item {
            border: none;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }
        
        .list-group-item:hover {
            background-color: var(--light-color);
            transform: translateX(5px);
        }
        
        .list-group-item.active {
            background-color: var(--accent-color);
            border: none;
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .badge-admin {
            background-color: var(--danger-color);
            color: white;
        }
        
        .badge-user {
            background-color: var(--accent-color);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'header.html'; ?>
    
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="https://via.placeholder.com/150" class="profile-picture" alt="Profile Picture">
                </div>
                <div class="col-md-9">
                    <h1 class="mb-2"><?php echo htmlspecialchars($user['fullname']); ?></h1>
                    <span class="badge <?php echo ($user['role'] === 'admin') ? 'badge-admin' : 'badge-user'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <p class="mt-3 mb-0">
                        <i class="far fa-envelope mr-2"></i><?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="mt-2">
                        <i class="far fa-calendar-alt mr-2"></i>Joined: <?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Navigation</h4>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user mr-2"></i>My Profile
                        </a>
                        <a href="orders.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag mr-2"></i>My Orders
                        </a>
                        <a href="wishlist.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart mr-2"></i>My Wishlist
                        </a>
                        <?php if ($user['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-user-shield mr-2"></i>Admin Dashboard
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="form-text text-muted">Email cannot be changed</small>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Change Password</h5>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                            </div>
                            
                            <div class="form-group">
                                <small class="form-text text-muted">Leave password fields blank if you don't want to change your password</small>
                            </div>
                            
                            <input type="hidden" name="update_profile" value="1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if ($user['role'] === 'admin'): ?>
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Admin Actions</h4>
                    </div>
                    <div class="card-body">
                        <p>As an administrator, you have access to the admin dashboard and other privileged actions.</p>
                        <a href="admin/dashboard.php" class="btn btn-danger">
                            <i class="fas fa-user-shield mr-2"></i>Go to Admin Dashboard
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'user'): ?>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Account Activity</h4>
                    </div>
                    <div class="card-body">
                        <p>View your recent account activity and orders.</p>
                        <div class="list-group">
                            <a href="orders.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart mr-2"></i>View Your Orders
                            </a>
                            <a href="cart.html" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-basket mr-2"></i>View Your Cart
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.html'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html> 