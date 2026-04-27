<?php
// Load required dependencies
require_once 'autoload.php';

// Redirect logged-in users to dashboard, guests to login page
if (isLoggedIn()) {
    header('Location: /views/dashboard/index.php');
} else {
    header('Location: /views/auth/login.php');
}
exit; // Stop script execution
