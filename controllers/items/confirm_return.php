<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

if (!csrf_check()) {
    echo "Invalid CSRF token.";
    exit;
}

$me = sessionUser();
$action = $_POST['action'] ?? '';
$encrypted_return_id = rawurldecode($_POST['return_id'] ?? '');
$return_id = decryptId($encrypted_return_id);

if (!$return_id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: /views/dashboard/index.php");
    exit;
}

$itemObj = new Item($db);

if ($action === 'confirm') {
    $result = $itemObj->confirmReturn($return_id, $me['id']);
} elseif ($action === 'reject') {
    $reason = trim($_POST['rejection_reason'] ?? '');
    if (!$reason) {
        $_SESSION['error'] = "Rejection reason is required.";
        header("Location: /views/items/confirm_return.php?return_id=" . urlencode($encrypted_return_id));
        exit;
    }
    $result = $itemObj->rejectReturn($return_id, $me['id'], $reason);
} else {
    $_SESSION['error'] = "Invalid action.";
    header("Location: /views/dashboard/index.php");
    exit;
}

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}
header("Location: /views/dashboard/index.php");
exit;