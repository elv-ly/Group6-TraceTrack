<?php
// Load required dependencies and configuration
require_once __DIR__ . '/../../autoload.php';
// Ensure user is logged in before allowing item creation
requireLogin();

// Verify CSRF token to prevent cross-site request forgery attacks
if (!csrf_check()) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    // Preserve the report type parameter when redirecting back to form
    header("Location: /views/items/create.php?type=" . ($_POST['report_type'] ?? 'lost'));
    exit;
}

// ========== GET CURRENT USER INFORMATION ==========
$me          = sessionUser(); // Returns array with user data (id, name, role, etc.)

// ========== COLLECT FORM DATA ==========
$report_type = $_POST['report_type'] ?? '';
$item_name   = trim($_POST['item_name']   ?? '');
$category    = $_POST['category']         ?? '';
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location']    ?? '');
$date_occured= $_POST['date_occured']     ?? '';

// ========== ALLOWED VALUES FOR VALIDATION ==========
$allowed_types      = ['lost', 'found'];
$allowed_categories = ['electronics','clothing','documents','accessories','keys','others'];

// ========== VALIDATION SECTION ==========
if (!in_array($report_type, $allowed_types)) {
    $_SESSION['error'] = 'Invalid report type.';
    header("Location: /views/items/create.php?type=lost");
    exit;
}
// Check that all required fields are filled
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
// Prevent future dates (can't lose something tomorrow)
if (strtotime($date_occured) > time()) {
    $_SESSION['error'] = 'Date cannot be in the future.';
    header("Location: /views/items/create.php?type=$report_type");
    exit;
}

// Handle photo upload
$photo = null; / Initialize photo path as null (no photo)
if (!empty($_FILES['photo']['name'])) {
    // Attempt to upload the photo file
    $upload = Item::uploadPhoto($_FILES['photo']);
    // Check if upload failed
    if (!$upload['status']) {
        $_SESSION['error'] = $upload['message'];
        header("Location: /views/items/create.php?type=$report_type");
        exit;
    }
    // Store the uploaded file path
    $photo = $upload['path'];
}

// ========== CREATE ITEM RECORD ==========
$item              = new Item($db);
$item->user_id     = $me['id'];
$item->report_type = $report_type;
$item->item_name   = $item_name;
$item->category    = $category;
$item->description = $description;
$item->location    = $location;
$item->date_occured= $date_occured;
$item->photo       = $photo;
// Attempt to save the item to database
$result = $item->create();

// ========== HANDLE RESULT ==========
if ($result['status']) {
    $_SESSION['success'] = $result['message'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    // Redirect to user's list of reports
    header("Location: /views/items/my_reports.php");
} else {
    // Failure - store error message and redirect back to form
    $_SESSION['error'] = $result['message'];
    header("Location: /views/items/create.php?type=$report_type");
}
exit; // Terminate script execution
