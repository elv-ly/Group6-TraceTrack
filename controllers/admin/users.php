<?php
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

header('Content-Type: application/json');

if (!csrf_check()) {
    echo json_encode(["status" => false, "message" => "Invalid CSRF token."]);
    exit;
}

$me      = sessionUser();
$action  = $_POST['action']  ?? '';
$user_id = intval($_POST['user_id'] ?? 0);

if (!$user_id) {
    echo json_encode(["status" => false, "message" => "Invalid user."]);
    exit;
}

if ($user_id == $me['id']) {
    echo json_encode(["status" => false, "message" => "You cannot deactivate your own account."]);
    exit;
}

$userObj = new User($db);

switch ($action) {
    case 'deactivate':
        echo json_encode($userObj->toggleActive($user_id, 0));
        break;
    case 'activate':
        echo json_encode($userObj->toggleActive($user_id, 1));
        break;
    default:
        echo json_encode(["status" => false, "message" => "Invalid action."]);
}
exit;
