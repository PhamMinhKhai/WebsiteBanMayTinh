<?php
// Add this at the top of register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Log the form submission
// file_put_contents('debug.txt', 'Register.php was accessed at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
// file_put_contents('debug.txt', 'POST data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

// Check if db_connect.php exists
if (!file_exists('db_connect.php')) {
    // file_put_contents('debug.txt', 'db_connect.php does not exist!' . "\n", FILE_APPEND);
    die("Database connection file not found");
}

require_once 'db_connect.php';

// Log connection status
// if ($conn->connect_error) {
//     file_put_contents('debug.txt', 'Database connection failed: ' . $conn->connect_error . "\n", FILE_APPEND);
// } else {
//     file_put_contents('debug.txt', 'Database connection successful' . "\n", FILE_APPEND);
// }

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $fullname = trim(htmlspecialchars($_POST['fullname']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    // Validate name
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email or login.";
        }
        $stmt->close();
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    // Confirm passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare and execute SQL to insert new user
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $fullname, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Registration successful
            $_SESSION['registration_success'] = true;
            $_SESSION['email'] = $email;
            header("Location: loginRegister.html?registration=success");
            exit();
        } else {
            $errors[] = "Registration failed: " . $conn->error;
        }
        $stmt->close();
    }
    
    // If there were errors, log them and redirect
    if (!empty($errors)) {
        // file_put_contents('debug.txt', 'Registration errors: ' . print_r($errors, true) . "\n", FILE_APPEND);
        $_SESSION['registration_errors'] = $errors;
        header("Location: loginRegister.html?registration=failed");
        exit();
    }
}

// Add this debug line to see if the file is being accessed
// file_put_contents('debug.txt', 'Register.php was accessed at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
?>