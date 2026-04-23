<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

header('Content-Type: application/json');

if (!csrf_check()) {
    echo json_encode(["status" => false, "message" => "Invalid CSRF token."]);
    exit;
}

$me      = sessionUser();
$item_id = intval($_POST['item_id'] ?? 0);
$reason  = trim($_POST['reason'] ?? '');

if (!$item_id || !$reason) {
    echo json_encode(["status" => false, "message" => "Item and reason are required."]);
    exit;
}

$item   = new Item($db);
$record = $item->readOne($item_id);

// Make sure the item belongs to this user
if (!$record || $record['user_id'] != $me['id']) {
    echo json_encode(["status" => false, "message" => "Unauthorized."]);
    exit;
}

$result = $item->requestDeletion($item_id, $me['id'], $reason);
echo json_encode($result);
exit;
