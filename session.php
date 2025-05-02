<?php
require_once 'config.php';
require_once 'includes/session_config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please log in to access this page.";
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . getBaseUrl() . '/login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Admin privileges required.";
        header('Location: ' . getBaseUrl() . '/index.php');
        exit();
    }
}
?>
