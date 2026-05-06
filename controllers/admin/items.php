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
$item_id = intval($_POST['item_id'] ?? 0);
$reason  = trim($_POST['reason'] ?? '');

if (!$item_id) {
    echo json_encode(["status" => false, "message" => "Invalid item."]);
    exit;
}

$itemObj = new Item($db);

switch ($action) {
    case 'approve':
        echo json_encode($itemObj->adminApprove($item_id, $me['id']));
        break;
    case 'reject':
        if (!$reason) { echo json_encode(["status" => false, "message" => "Reason is required."]); exit; }
        echo json_encode($itemObj->adminReject($item_id, $me['id'], $reason));
        break;
    default:
        echo json_encode(["status" => false, "message" => "Invalid action."]);
}
exit;
