<?php
session_start();
require_once 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $email = trim(htmlspecialchars($_POST['email']));
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate inputs
    $errors = [];
    
    // Check for empty fields
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, fullname, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_regenerate_id(); // Prevent session fixation
                
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_fullname'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // If remember me is checked, set cookies
                if ($remember) {
                    $token = bin2hex(random_bytes(16)); // Generate a secure token
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days')); // Token expiry date

                    // Check if remember_tokens table exists
                    $tableExists = $conn->query("SHOW TABLES LIKE 'remember_tokens'")->num_rows > 0;

                    if (!$tableExists) {
                        // Create remember_tokens table
                        $sql = "CREATE TABLE remember_tokens (
                            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            user_id INT(11) UNSIGNED NOT NULL,
                            token VARCHAR(64) NOT NULL,
                            expiry DATETIME NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                            UNIQUE KEY (token)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        $conn->query($sql);
                    }

                    // Store token in database
                    $stmt_token = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
                    $stmt_token->bind_param("iss", $user['id'], $token, $expiry);
                    $stmt_token->execute();
                    $stmt_token->close();
                    
                    // Set cookies (HttpOnly recommended for security if possible, path set to '/')
                    setcookie('remember_user', $user['id'], time() + (86400 * 30), "/", "", false, true); // 30 days, HttpOnly
                    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true); // 30 days, HttpOnly
                }
                
                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php"); // Redirect admin to admin dashboard
                } else {
                    header("Location: account.php"); // Redirect user to account page
                }
                exit();
            } else {
                // Invalid password
                $errors[] = "Invalid email or password";
            }
        } else {
            // User not found
            $errors[] = "Invalid email or password";
        }
        $stmt->close();
    }
    
    // If there were errors, store them in session and redirect back to login form
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors; // Store login specific errors if needed
        header("Location: loginRegister.html?login=failed");
        exit();
    }
}
?>