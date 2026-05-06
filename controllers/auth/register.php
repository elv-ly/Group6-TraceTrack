<?php
require_once __DIR__ . '/../../autoload.php';
requireGuest();

if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("Location: /views/auth/register.php");
    exit;
}

// Validation
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email']     ?? '');
$password  = $_POST['password']       ?? '';
$confirm   = $_POST['confirm']        ?? '';
$role      = $_POST['role']           ?? 'student';
$id_number = trim($_POST['id_number'] ?? '');
$contact   = trim($_POST['contact']   ?? '');

if (!$full_name || !$email || !$password || !$id_number || !$contact) {
    $_SESSION['error'] = 'All fields are required.';
    header("Location: /views/auth/register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header("Location: /views/auth/register.php");
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters.';
    header("Location: /views/auth/register.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    header("Location: /views/auth/register.php");
    exit;
}

if (!in_array($role, ['student', 'faculty'])) {
    $_SESSION['error'] = 'Invalid role selected.';
    header("Location: /views/auth/register.php");
    exit;
}

$user            = new User($db);
$user->full_name = $full_name;
$user->email     = $email;
$user->password  = $password;
$user->role      = $role;
$user->id_number = $id_number;
$user->contact   = $contact;

$result = $user->register();

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Location: /views/auth/login.php");
} else {
    $_SESSION['error'] = $result['message'];
    header("Location: /views/auth/register.php");
}
exit;
