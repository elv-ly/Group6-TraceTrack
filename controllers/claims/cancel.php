<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

header('Content-Type: application/json');

if (!csrf_check()) {
    echo json_encode(["status" => false, "message" => "Invalid CSRF token."]);
    exit;
}

$me       = sessionUser();
$claim_id = intval($_POST['claim_id'] ?? 0);

if (!$claim_id) {
    echo json_encode(["status" => false, "message" => "Invalid claim."]);
    exit;
}

$claim  = new Claim($db);
$result = $claim->requestCancel($claim_id, $me['id']);

echo json_encode($result);
exit;
