<?php
require_once __DIR__ . '/../../autoload.php';
requireGuest();

if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("Location: /views/auth/login.php");
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

if (!$email || !$password) {
    $_SESSION['error'] = 'Please enter your email and password.';
    header("Location: /views/auth/login.php");
    exit;
}

$user          = new User($db);
$user->email   = $email;
$user->password = $password;

$result = $user->login();

if ($result['status']) {
    $u = $result['user'];
    $_SESSION['user_id']    = $u['user_id'];
    $_SESSION['user_name']  = $u['full_name'];
    $_SESSION['user_role']  = $u['role'];
    $_SESSION['user_email'] = $u['email'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['success']    = "Welcome back, " . $u['full_name'] . "!";
    header("Location: /views/dashboard/index.php");
} else {
    $_SESSION['error'] = $result['message'];
    header("Location: /views/auth/login.php");
}
exit;
