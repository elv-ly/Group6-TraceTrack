<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database
require_once __DIR__ . "/config/Database.php";

// Helpers
require_once __DIR__ . "/helpers/csrf_helper.php";
require_once __DIR__ . "/helpers/encrypt_helper.php";
require_once __DIR__ . "/helpers/auth_helper.php";

// Autoload classes
spl_autoload_register(function ($class) {
    require_once __DIR__ . "/classes/$class.php";
});

// Shared DB connection
$database = new Database();
$db = $database->getConnection();

// Check maintenance mode (block non-admins if enabled)
try {
    $stmt = $db->prepare("SELECT config_value FROM SYSTEM_CONFIG WHERE config_key = 'maintenance_mode' LIMIT 1");
    $stmt->execute();
    $maintenance = (int)($stmt->fetchColumn() ?? 0);
    if ($maintenance && !isset($_SESSION['bypass_maintenance'])) {
        $current_page = $_SERVER['PHP_SELF'] ?? '';
        // Allow auth pages (login/logout/register) to work during maintenance
        // Allow admins to access any page
        if (strpos($current_page, '/auth/') === false && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')) {
            die("<h1 style='text-align:center; margin-top:100px;'>System Under Maintenance</h1><p style='text-align:center; color:#666;'>Please check back later.</p>");
        }
    }
} catch (Throwable $e) {
    // Database tables might not exist yet - don't block startup
}
