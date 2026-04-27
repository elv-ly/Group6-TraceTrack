<?php
// Start session for user state management (login, CSRF, flash messages)
session_start();

// Generate CSRF token if not already set (for form protection)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ========== LOAD DEPENDENCIES ==========

// Database configuration and connection class
require_once __DIR__ . "/config/Database.php";

// Helper functions
require_once __DIR__ . "/helpers/csrf_helper.php";      // CSRF token generation/validation
require_once __DIR__ . "/helpers/encrypt_helper.php";   // ID encryption/decryption
require_once __DIR__ . "/helpers/auth_helper.php";      // Auth middleware functions

// ========== AUTOLOADER ==========
// Automatically load class files from /classes directory when instantiated
spl_autoload_register(function ($class) {
    require_once __DIR__ . "/classes/$class.php";
});

// ========== DATABASE CONNECTION ==========
// Create global database connection object for all models to use
$database = new Database();
$db = $database->getConnection();
