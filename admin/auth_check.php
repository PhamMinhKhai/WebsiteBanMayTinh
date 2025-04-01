<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['auth_error'] = "You must be an admin to access this page";
    header("Location: ../loginRegister.html");
    exit();
}
?> 