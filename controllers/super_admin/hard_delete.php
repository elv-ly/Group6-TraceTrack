<?php
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

header('Content-Type: application/json');

try {
    if (!csrf_check()) {
        echo json_encode(["status" => false, "message" => "Invalid security token"]);
        exit;
    }

    $type = $_POST['type'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $allowed = ['user', 'item', 'claim'];

    if (!in_array($type, $allowed) || !$id) {
        echo json_encode(["status" => false, "message" => "Invalid request"]);
        exit;
    }

    // Prevent deleting super admin accounts
    if ($type === 'user') {
        $userStmt = $db->prepare("SELECT role FROM USERS WHERE user_id = :id");
        $userStmt->execute([':id' => $id]);
        $user = $userStmt->fetch();
        if ($user && $user['role'] === 'super_admin') {
            echo json_encode(["status" => false, "message" => "Cannot delete super admin account"]);
            exit;
        }
    }

    logAction($_SESSION['user_id'], "hard_delete_$type", "ID: $id");

    if ($type === 'user') {
        $db->prepare("DELETE FROM USERS WHERE user_id = :id AND role != 'super_admin'")->execute([':id' => $id]);
    } elseif ($type === 'item') {
        $db->prepare("DELETE FROM ITEMS WHERE item_id = :id")->execute([':id' => $id]);
    } elseif ($type === 'claim') {
        $db->prepare("DELETE FROM CLAIMS WHERE claim_id = :id")->execute([':id' => $id]);
    }

    echo json_encode(["status" => true, "message" => "Deleted permanently."]);
} catch (Throwable $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
exit;
