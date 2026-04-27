<?php
// Load required dependencies and configuration
require_once __DIR__ . '/../../autoload.php';
// Ensure user is not already logged in (redirects to dashboard if they are)
requireGuest();

// Verify CSRF token to prevent cross-site request forgery attacks
if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("Location: /views/auth/login.php");
    exit;
}

// Get and sanitize form input data
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// Validate that both fields are filled
if (!$email || !$password) {
    $_SESSION['error'] = 'Please enter your email and password.';
    header("Location: /views/auth/login.php");
    exit;
}

// Create new User object and set credentials
$user          = new User($db);
$user->email   = $email;
$user->password = $password;

// Attempt to authenticate the user
$result = $user->login();

// Check if login was successful
if ($result['status']) {
    // Extract user data from result
    $u = $result['user'];
    // Store user information in session for persistence across requests
    $_SESSION['user_id']    = $u['user_id'];
    $_SESSION['user_name']  = $u['full_name'];
    $_SESSION['user_role']  = $u['role'];
    $_SESSION['user_email'] = $u['email'];
    // Regenerate CSRF token for security (prevains token reuse attacks)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    // Set success message for display on next page
    $_SESSION['success']    = "Welcome back, " . $u['full_name'] . "!";
    header("Location: /views/dashboard/index.php");
} else {
    // Login failed - store error message and redirect back to login form
    $_SESSION['error'] = $result['message'];
    header("Location: /views/auth/login.php");
}
exit;
