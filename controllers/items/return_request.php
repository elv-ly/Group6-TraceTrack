<?php

require_once __DIR__ . '/../../autoload.php';
requireLogin();

if (!csrf_check()) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: /views/items/browse.php");
    exit;
}

$me = sessionUser();
$encrypted_id = $_POST['item_id'] ?? '';
$item_id = decryptId($encrypted_id);
$found_location = trim($_POST['found_location'] ?? '');
$finder_description = trim($_POST['finder_description'] ?? '');
$proof_photo = null;

if (!$me['id']) {
    $_SESSION['error'] = 'Session error. Please log in again.';
    header("Location: /views/auth/login.php");
    exit;
}

if (!$item_id || !$found_location) {
    $_SESSION['error'] = "Missing required information.";
    header("Location: /views/items/return_item.php?item_id=" . urlencode($encrypted_id));
    exit;
}

// Verify item exists and is eligible
$itemObj = new Item($db);
$item = $itemObj->readOne($item_id);
if (!$item || $item['report_type'] !== 'lost' || $item['status'] !== 'active' || $item['user_id'] == $me['id']) {
    $_SESSION['error'] = "You cannot return this item.";
    header("Location: /views/items/browse.php");
    exit;
}

// Upload proof if provided
if (!empty($_FILES['proof_photo']['name'])) {
    $upload = Item::uploadPhoto($_FILES['proof_photo']);
    if (!$upload['status']) {
        $_SESSION['error'] = $upload['message'];
        header("Location: /views/items/return_item.php?item_id=" . urlencode($encrypted_id));
        exit;
    }
    $proof_photo = $upload['path'];
}

// Create return request - use method in Item class
$result = $itemObj->createReturnRequest($item_id, $me['id'], $found_location, $finder_description, $proof_photo);

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
    header("Location: /views/items/browse.php");
} else {
    $_SESSION['error'] = $result['message'];
    header("Location: /views/items/return_item.php?item_id=" . urlencode($encrypted_id));
}
exit;