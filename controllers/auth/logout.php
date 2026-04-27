<?php
// Load required dependencies and configuration
require_once __DIR__ . '/../../autoload.php';
// Clear all session variables (removes user data, cart items, etc.)
session_unset();
// Destroy the session completely (deletes session file/cookie)
session_destroy();
// Redirect user to login page for new authentication
header('Location: /views/auth/login.php');
// Terminate script execution
exit;
