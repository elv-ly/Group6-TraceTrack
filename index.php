<?php
require_once 'autoload.php';
if (isLoggedIn()) {
    header('Location: /views/dashboard/index.php');
} else {
    header('Location: /views/auth/login.php');
}
exit;
