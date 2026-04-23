<?php
require_once __DIR__ . '/../../autoload.php';
requireLogin();

if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("Location: /views/items/create.php?type=" . ($_POST['report_type'] ?? 'lost'));
    exit;
}

$me          = sessionUser();
$report_type = $_POST['report_type'] ?? '';
$item_name   = trim($_POST['item_name']   ?? '');
$category    = $_POST['category']         ?? '';
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location']    ?? '');
$date_occured= $_POST['date_occured']     ?? '';

$allowed_types      = ['lost', 'found'];
$allowed_categories = ['electronics','clothing','documents','accessories','keys','others'];

// Validation
if (!in_array($report_type, $allowed_types)) {
    $_SESSION['error'] = 'Invalid report type.';
    header("Location: /views/items/create.php?type=lost");
    exit;
}

if (!$item_name || !$category || !$description || !$location || !$date_occured) {
    $_SESSION['error'] = 'All fields are required.';
    header("Location: /views/items/create.php?type=$report_type");
    exit;
}

if (!in_array($category, $allowed_categories)) {
    $_SESSION['error'] = 'Invalid category selected.';
    header("Location: /views/items/create.php?type=$report_type");
    exit;
}

if (strtotime($date_occured) > time()) {
    $_SESSION['error'] = 'Date cannot be in the future.';
    header("Location: /views/items/create.php?type=$report_type");
    exit;
}

// Handle photo upload
$photo = null;
if (!empty($_FILES['photo']['name'])) {
    $upload = Item::uploadPhoto($_FILES['photo']);
    if (!$upload['status']) {
        $_SESSION['error'] = $upload['message'];
        header("Location: /views/items/create.php?type=$report_type");
        exit;
    }
    $photo = $upload['path'];
}

$item              = new Item($db);
$item->user_id     = $me['id'];
$item->report_type = $report_type;
$item->item_name   = $item_name;
$item->category    = $category;
$item->description = $description;
$item->location    = $location;
$item->date_occured= $date_occured;
$item->photo       = $photo;

$result = $item->create();

if ($result['status']) {
    $_SESSION['success'] = $result['message'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Location: /views/items/my_reports.php");
} else {
    $_SESSION['error'] = $result['message'];
    header("Location: /views/items/create.php?type=$report_type");
}
exit;
