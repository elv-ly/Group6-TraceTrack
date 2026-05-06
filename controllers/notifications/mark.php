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
$notif  = new Notification($db);

switch ($action) {
    case 'mark_one':
        $id = intval($_POST['notification_id'] ?? 0);
        echo json_encode($notif->markRead($id, $me['id']));
        break;
    case 'mark_all':
        echo json_encode($notif->markAllRead($me['id']));
        break;
    default:
        echo json_encode(["status" => false, "message" => "Invalid action."]);
}
exit;
