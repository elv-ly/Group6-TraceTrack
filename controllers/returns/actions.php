<?php

require_once __DIR__ . '/../../autoload.php';
requireLogin();

if (!csrf_check()) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: /views/notifications/index.php");
    exit;
}

$action = $_POST['action'] ?? '';
$return_id = $_POST['return_id'] ?? '';

if (!$return_id || !is_numeric($return_id)) {
    $_SESSION['error'] = "Invalid return request ID.";
    header("Location: /views/notifications/index.php");
    exit;
}

$me = sessionUser();
$itemObj = new Item($db);

switch ($action) {
    case 'complete':
        $result = $itemObj->completeReturn($return_id, $me['id']);
        break;

    case 'submit_failure':
        $failure_reason = trim($_POST['failure_reason'] ?? '');

        if (!$failure_reason) {
            $_SESSION['error'] = "Failure reason is required.";
            header("Location: /views/notifications/index.php");
            exit;
        }

        $result = $itemObj->submitFailureReason($return_id, $me['id'], $failure_reason);
        break;

    default:
        $_SESSION['error'] = "Invalid action.";
        header("Location: /views/notifications/index.php");
        exit;
}

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header("Location: /views/notifications/index.php");
exit;