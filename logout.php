<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Delete remember me cookies if they exist
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Delete the token from the database if it exists
if (isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once 'db_connect.php';
    $user_id = $_SESSION['user_id'];
    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND token = ?");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: index.php?logout=success");
exit();
?> 