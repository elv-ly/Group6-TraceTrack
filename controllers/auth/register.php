<?php
// Load required dependencies and configuration
require_once __DIR__ . '/../../autoload.php';
// Ensure user is not already logged in (redirects to dashboard if they are)
requireGuest();

// Verify CSRF token to prevent cross-site request forgery attacks
if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("Location: /views/auth/register.php");
    exit;
}

// ========== FORM DATA COLLECTION AND SANITIZATION ==========
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email']     ?? '');
$password  = $_POST['password']       ?? '';
$confirm   = $_POST['confirm']        ?? '';
$role      = $_POST['role']           ?? 'student';
$id_number = trim($_POST['id_number'] ?? '');
$contact   = trim($_POST['contact']   ?? '');

// ========== VALIDATION RULES ==========

// Check that all required fields are provided
if (!$full_name || !$email || !$password || !$id_number || !$contact) {
    $_SESSION['error'] = 'All fields are required.';
    header("Location: /views/auth/register.php");
    exit;
}

// Validate email format using PHP's built-in filter
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header("Location: /views/auth/register.php");
    exit;
}

// Enforce minimum password length for security
if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters.';
    header("Location: /views/auth/register.php");
    exit;
}

// Verify that password and confirmation match
if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    header("Location: /views/auth/register.php");
    exit;
}

// Ensure role is valid (prevents role manipulation attacks)
if (!in_array($role, ['student', 'faculty'])) {
    $_SESSION['error'] = 'Invalid role selected.';
    header("Location: /views/auth/register.php");
    exit;
}

// ========== USER REGISTRATION ==========

// Create new User object and populate with validated data
$user            = new User($db);
$user->full_name = $full_name;
$user->email     = $email;
$user->password  = $password;
$user->role      = $role;
$user->id_number = $id_number;
$user->contact   = $contact;

// Attempt to register the user in database
$result = $user->register();

// Check registration result
if ($result['status']) {
    // Registration successful
    $_SESSION['success'] = $result['message'];
    // Generate new CSRF token for login page security
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    // Redirect to login page for authentication
    header("Location: /views/auth/login.php");
} else {
    // Registration failed - store error and redirect back to registration form
    $_SESSION['error'] = $result['message'];
    header("Location: /views/auth/register.php");
}
exit; // Terminate script execution
