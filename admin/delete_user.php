<?php
// Include admin authentication check
require_once 'auth_check.php';
require_once '../db_connect.php';

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = $_GET['id'];

// Cannot delete your own account
if ($_SESSION['user_id'] == $user_id) {
    $_SESSION['error_message'] = "You cannot delete your own account";
    header("Location: manage_users.php");
    exit();
}

// Delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "User deleted successfully";
} else {
    $_SESSION['error_message'] = "Error deleting user: " . $conn->error;
}

$stmt->close();
$conn->close();

// Redirect back to manage users page
header("Location: manage_users.php");
exit();
?> 