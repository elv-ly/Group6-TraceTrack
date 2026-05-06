<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /views/auth/login.php');
        exit;
    }
}

function isSuperAdmin() {
    // Superadmin role no longer exists - admins now handle governance
    return false;
}

function requireSuperAdmin() {
    // Superadmin role no longer exists - use requireAdmin() instead
    requireAdmin();
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /views/dashboard/index.php');
        exit;
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header('Location: /views/dashboard/index.php');
        exit;
    }
}

function sessionUser() {
    return [
        'id'    => $_SESSION['user_id']    ?? null,
        'name'  => $_SESSION['user_name']  ?? '',
        'role'  => $_SESSION['user_role']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
    ];
}

function siteUrl($path = '') {
    if (empty($path)) {
        $path = '/';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    }

    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $path = '/' . ltrim($path, '/');

    return $scheme . '://' . $host . $path;
}

// Logging function for audit trail
function logAction($user_id, $action, $details = null) {
    global $db;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $db->prepare("INSERT INTO AUDIT_LOG (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':action'  => $action,
            ':details' => $details,
            ':ip'      => $ip
        ]);
    } catch (Throwable $e) {
        error_log("logAction error: " . $e->getMessage());
    }
}
