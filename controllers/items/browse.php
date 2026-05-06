<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

header('Content-Type: application/json');

$me       = sessionUser();
$search   = trim($_GET['search']   ?? '');
$type     = trim($_GET['type']     ?? '');
$category = trim($_GET['category'] ?? '');

$itemObj = new Item($db);
$items   = $itemObj->browse($me['id'], $search, $type, $category);

// Add encrypted ID for safe URL usage
foreach ($items as &$item) {
    $item['encrypted_id'] = encryptId($item['item_id']);
}

echo json_encode([
    "status" => true,
    "items" => $items,
    "current_user_id" => $me['id']
]);
exit;