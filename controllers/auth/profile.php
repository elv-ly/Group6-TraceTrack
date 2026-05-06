<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

header('Content-Type: application/json');

if (!csrf_check()) {
    echo json_encode(["status" => false, "message" => "Invalid CSRF token."]);
    exit;
}

$me     = sessionUser();
$action = $_POST['action'] ?? '';
$user   = new User($db);
$user->user_id = $me['id'];

switch ($action) {
    case 'update_profile':
        $user->full_name = trim($_POST['full_name'] ?? '');
        $user->contact   = trim($_POST['contact']   ?? '');
        if (!$user->full_name || !$user->contact) {
            echo json_encode(["status" => false, "message" => "All fields are required."]);
            exit;
        }
        $result = $user->update();
        if ($result['status']) {
            $_SESSION['user_name'] = $user->full_name;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo json_encode($result);
        break;

    case 'change_password':
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!$current || !$new || !$confirm) {
            echo json_encode(["status" => false, "message" => "All fields are required."]);
            exit;
        }
        if (strlen($new) < 8) {
            echo json_encode(["status" => false, "message" => "New password must be at least 8 characters."]);
            exit;
        }
        if ($new !== $confirm) {
            echo json_encode(["status" => false, "message" => "Passwords do not match."]);
            exit;
        }
        $result = $user->changePassword($current, $new);
        if ($result['status']) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        echo json_encode($result);
        break;

    default:
        echo json_encode(["status" => false, "message" => "Invalid action."]);
}
exit;
