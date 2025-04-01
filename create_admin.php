<?php
require_once 'db_connect.php';

// Check if the script is being run from the command line or by a web user
$is_cli = (php_sapi_name() === 'cli');

// Admin user details
$admin_fullname = "Admin User";
$admin_email = "admin@techhub.com";
$admin_password = "admin123"; // This should be changed immediately after creation

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $message = "Admin user already exists!";
} else {
    // Hash the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Insert admin user
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
    $stmt->bind_param("sss", $admin_fullname, $admin_email, $hashed_password);
    
    if ($stmt->execute()) {
        $message = "Admin user created successfully! Email: $admin_email, Password: $admin_password";
    } else {
        $message = "Error creating admin user: " . $conn->error;
    }
}

$stmt->close();
$conn->close();

// Output message depending on context
if ($is_cli) {
    echo $message . PHP_EOL;
} else {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Admin User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <h1>Create Admin User</h1>";
    
    if (strpos($message, "already exists") !== false) {
        echo "<div class='message warning'>" . htmlspecialchars($message) . "</div>";
    } else if (strpos($message, "successfully") !== false) {
        echo "<div class='message success'>" . htmlspecialchars($message) . "</div>";
        echo "<p><strong>Important:</strong> Please change the admin password immediately after first login!</p>";
    } else {
        echo "<div class='message error'>" . htmlspecialchars($message) . "</div>";
    }
    
    echo "<p><a href='loginRegister.html'>Go to Login Page</a></p>
</body>
</html>";
}
?>