<?php
require_once __DIR__ . '/../../../autoload.php';
requireAdmin();

header('Content-Type: application/json');

try {
    if (!csrf_check()) {
        echo json_encode(["status" => false, "message" => "Invalid security token"]);
        exit;
    }

    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';

    if (!$user_id || strlen($new_password) < 8) {
        echo json_encode(["status" => false, "message" => "Invalid user or weak password (min 8 chars)"]);
        exit;
    }

    // Check if user exists
    $userStmt = $db->prepare("SELECT role FROM USERS WHERE user_id = :id");
    $userStmt->execute([':id' => $user_id]);
    $user = $userStmt->fetch();

    if (!$user) {
        echo json_encode(["status" => false, "message" => "User not found"]);
        exit;
    }

    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    $db->prepare("UPDATE USERS SET password = :pwd WHERE user_id = :id")->execute([
        ':pwd' => $hashed,
        ':id' => $user_id
    ]);

    logAction($_SESSION['user_id'], 'password_reset', "User ID: $user_id");
    echo json_encode(["status" => true, "message" => "Password reset successfully."]);
} catch (Throwable $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
exit;
