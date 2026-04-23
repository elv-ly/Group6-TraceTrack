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
