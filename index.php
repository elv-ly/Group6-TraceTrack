<?php
// Load application dependencies (session, database, helpers, classes)
require_once 'autoload.php';

// Redirect user based on login status
if (isLoggedIn()) {
    // User is logged in → send to dashboard
    header('Location: /views/dashboard/index.php');
} else {
    // User is not logged in → send to login page
    header('Location: /views/auth/login.php');
}

// Stop script execution after redirect
exit;
