<?php

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Require user to be logged in, redirect to login if not
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /views/auth/login.php');
        exit;
    }
}

// Require admin privileges, redirect to dashboard if not admin
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /views/dashboard/index.php');
        exit;
    }
}

// Require guest access (not logged in), redirect to dashboard if logged in
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: /views/dashboard/index.php');
        exit;
    }
}

// Get current user data from session with safe defaults
function sessionUser() {
    return [
        'id'    => $_SESSION['user_id']    ?? null,
        'name'  => $_SESSION['user_name']  ?? '',
        'role'  => $_SESSION['user_role']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}
