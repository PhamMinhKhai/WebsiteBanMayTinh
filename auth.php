<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * @return bool Whether the user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if the logged-in user is an admin
 * @return bool Whether the user is an admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require user to be logged in, redirect if not
 * @param string $redirect_url URL to redirect to if not logged in
 */
function require_login($redirect_url = 'loginRegister.html') {
    if (!is_logged_in()) {
        $_SESSION['auth_error'] = "You must be logged in to access this page";
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require user to be an admin, redirect if not
 * @param string $redirect_url URL to redirect to if not an admin
 */
function require_admin($redirect_url = 'index.html') {
    if (!is_admin()) {
        $_SESSION['auth_error'] = "You must be an admin to access this page";
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Get the currently logged in user's ID
 * @return int|null User ID if logged in, null otherwise
 */
function get_user_id() {
    return is_logged_in() ? $_SESSION['user_id'] : null;
}

/**
 * Get the currently logged in user's role
 * @return string|null User role if logged in, null otherwise
 */
function get_user_role() {
    return is_logged_in() ? $_SESSION['user_role'] : null;
}
?> 