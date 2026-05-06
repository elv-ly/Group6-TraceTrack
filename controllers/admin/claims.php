<?php
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

header('Content-Type: application/json');

if (!csrf_check()) {
    echo json_encode(["status" => false, "message" => "Invalid CSRF token."]);
    exit;
}

$me       = sessionUser();
$action   = $_POST['action']   ?? '';
$claim_id = intval($_POST['claim_id'] ?? 0);
$reason   = trim($_POST['reason'] ?? '');

if (!$claim_id) {
    echo json_encode(["status" => false, "message" => "Invalid claim."]);
    exit;
}

$claimObj = new Claim($db);

switch ($action) {
    case 'getDetails':
        $claim = $claimObj->getOne($claim_id);
        if (!$claim) {
            echo json_encode(["status" => false, "message" => "Claim not found."]);
        } else {
            echo json_encode(["status" => true, "claim" => $claim]);
        }
        break;
    case 'approve':
        echo json_encode($claimObj->adminApprove($claim_id, $me['id']));
        break;
    case 'reject':
        if (!$reason) { echo json_encode(["status" => false, "message" => "Reason is required."]); exit; }
        echo json_encode($claimObj->adminReject($claim_id, $me['id'], $reason));
        break;
    case 'returned':
        echo json_encode($claimObj->markReturned($claim_id, $me['id']));
        break;
    case 'approve_cancel':
        echo json_encode($claimObj->adminApproveCancel($claim_id, $me['id']));
        break;
    case 'reject_cancel':
        if (!$reason) { echo json_encode(["status" => false, "message" => "Reason is required."]); exit; }
        echo json_encode($claimObj->adminRejectCancel($claim_id, $me['id'], $reason));
        break;
    default:
        echo json_encode(["status" => false, "message" => "Invalid action."]);
}
exit;
