<?php
// Load required dependencies and configuration
require_once __DIR__ . '/../../autoload.php';

// Ensure only logged-in users can request deletion
requireLogin();

// Set response format to JSON (this is an API endpoint)
header('Content-Type: application/json');

// Verify CSRF token to prevent cross-site request forgery attacks
if (!csrf_check()) {
    echo json_encode(["status" => false, "message" => "Invalid CSRF token."]);
    exit;
}

// ========== GET CURRENT USER DATA ==========
$me      = sessionUser();  // Returns array with user data

// ========== COLLECT AND SANITIZE INPUT DATA ==========
$item_id = intval($_POST['item_id'] ?? 0);  // Convert to integer, default 0 if missing
$reason  = trim($_POST['reason'] ?? '');    // User's explanation for deletion request

// ========== VALIDATE INPUT ==========
// Check that both required fields are provided
if (!$item_id || !$reason) {
    echo json_encode(["status" => false, "message" => "Item and reason are required."]);
    exit;
}

// ========== FETCH AND VERIFY ITEM OWNERSHIP ==========
$item   = new Item($db);
$record = $item->readOne($item_id);  // Retrieve item details from database

// Verify that the item exists AND belongs to the requesting user
// This prevents users from requesting deletion of other people's items
if (!$record || $record['user_id'] != $me['id']) {
    echo json_encode(["status" => false, "message" => "Unauthorized."]);
    exit;
}

// ========== SUBMIT DELETION REQUEST ==========
// Process the deletion request (this might create a pending request record 
// or directly delete the item, depending on business logic)
$result = $item->requestDeletion($item_id, $me['id'], $reason);

// Return JSON response to client (used by AJAX/JavaScript)
echo json_encode($result);
exit; // Terminate script execution
