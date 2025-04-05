<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the script to create remember_tokens table if it doesn't exist
require_once 'create_remember_tokens_table.php';

// Check if user is not logged in but has remember me cookies
if (!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    require_once 'db_connect.php';

    // Check if remember_tokens table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'remember_tokens'")->num_rows > 0;
    if (!$tableExists) {
        // If table doesn't exist, clear cookies and return
        setcookie('remember_user', '', time() - 3600, '/');
        setcookie('remember_token', '', time() - 3600, '/');
        return;
    }
    
    $user_id = $_COOKIE['remember_user'];
    $token = $_COOKIE['remember_token'];
    
    // Verify the token in the database
    $stmt = $conn->prepare("SELECT u.id, u.fullname, u.email, u.role FROM users u 
                           JOIN remember_tokens t ON u.id = t.user_id 
                           WHERE u.id = ? AND t.token = ? AND t.expiry > NOW()");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_fullname'] = $user['fullname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Extend the cookie lifetime
        setcookie('remember_user', $user_id, time() + (86400 * 30), "/", "", false, true); // 30 days
        setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true); // 30 days
        
        // Update token expiry in database
        $new_expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmt_update = $conn->prepare("UPDATE remember_tokens SET expiry = ? WHERE user_id = ? AND token = ?");
        $stmt_update->bind_param("sis", $new_expiry, $user_id, $token);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Invalid or expired token, clear cookies
        setcookie('remember_user', '', time() - 3600, '/');
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    $stmt->close();
}
?>