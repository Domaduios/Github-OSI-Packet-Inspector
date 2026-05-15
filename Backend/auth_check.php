<?php
/**
 * Include this at the TOP of every protected page (before any HTML output).
 * It starts the session and redirects to login.php if user is not authenticated.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Make user info globally available
$currentUser = [
    'id'       => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'User',
    'email'    => $_SESSION['email'] ?? '',
    'fullname' => $_SESSION['fullname'] ?? '',
    'role'     => $_SESSION['role'] ?? 'User',
];
?>
