<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("Location: /views/items/browse.php");
    exit;
}

$me             = sessionUser();
$item_id        = intval($_POST['item_id'] ?? 0);
$description    = trim($_POST['description']    ?? '');
$additional_info= trim($_POST['additional_info']?? '');

if (!$me['id']) {
    $_SESSION['error'] = 'Session error. Please log in again.';
    header("Location: /views/auth/login.php");
    exit;
}

if (!$item_id || !$description) {
    $_SESSION['error'] = 'Item and description are required.';
    header("Location: /views/items/browse.php");
    exit;
}

// Handle proof photo upload
$proof_photo = null;
if (!empty($_FILES['proof_photo']['name'])) {
    $upload = Claim::uploadProof($_FILES['proof_photo']);
    if (!$upload['status']) {
        $_SESSION['error'] = $upload['message'];
        header("Location: /views/items/browse.php");
        exit;
    }
    $proof_photo = $upload['path'];
}

$claim  = new Claim($db);
$result = $claim->create($item_id, $me['id'], $description, $proof_photo, $additional_info);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Location: /views/claims/my_claims.php");
} else {
    $_SESSION['error'] = $result['message'];
    header("Location: /views/items/browse.php");
}
exit;
