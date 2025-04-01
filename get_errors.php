<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['registration_errors'])) {
    echo json_encode(['errors' => $_SESSION['registration_errors']]);
    // Clear the errors after sending
    unset($_SESSION['registration_errors']);
} else {
    echo json_encode(['errors' => []]);
}
?>