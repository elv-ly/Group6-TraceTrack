<?php

require_once __DIR__ . '/../../autoload.php';
requireAdmin();

if (!csrf_check()) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: /views/admin/returns.php");
    exit;
}

$action = $_POST['action'] ?? '';
$return_id = $_POST['return_id'] ?? '';

if (!$return_id || !is_numeric($return_id)) {
    $_SESSION['error'] = "Invalid return request ID.";
    header("Location: /views/admin/returns.php");
    exit;
}

$me = sessionUser();
$itemObj = new Item($db);

switch ($action) {
    case 'approve':
        $coordinates = trim($_POST['coordinates'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');

        if (!$coordinates || !$deadline) {
            $_SESSION['error'] = "Coordinates and deadline are required.";
            header("Location: /views/admin/returns.php");
            exit;
        }

        $result = $itemObj->adminApproveReturn($return_id, $me['id'], $coordinates, $deadline);
        break;

    case 'reject':
        $reason = trim($_POST['reason'] ?? '');

        if (!$reason) {
            $_SESSION['error'] = "Rejection reason is required.";
            header("Location: /views/admin/returns.php");
            exit;
        }

        $result = $itemObj->adminRejectReturn($return_id, $me['id'], $reason);
        break;

    case 'allow_resubmission':
        $result = $itemObj->allowResubmission($return_id, $me['id']);
        break;

    default:
        $_SESSION['error'] = "Invalid action.";
        header("Location: /views/admin/returns.php");
        exit;
}

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header("Location: /views/admin/returns.php");
exit;