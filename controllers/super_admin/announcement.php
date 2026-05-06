<?php
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

header('Content-Type: application/json');

try {
    if (!csrf_check()) {
        echo json_encode(["status" => false, "message" => "Invalid security token"]);
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'get') {
        $stmt = $db->prepare("SELECT config_value FROM SYSTEM_CONFIG WHERE config_key = 'global_announcement'");
        $stmt->execute();
        $announcement = $stmt->fetchColumn() ?? '';
        echo json_encode(["status" => true, "announcement" => $announcement]);
        exit;
    }

    if ($action === 'set') {
        $message = trim($_POST['message'] ?? '');

        $db->prepare("UPDATE SYSTEM_CONFIG SET config_value = :msg WHERE config_key = 'global_announcement'")->execute([':msg' => $message]);
        logAction($_SESSION['user_id'], 'set_global_announcement', substr($message, 0, 100));
        echo json_encode(["status" => true, "message" => "Announcement saved."]);
        exit;
    }

    echo json_encode(["status" => false, "message" => "Invalid action"]);
} catch (Throwable $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
exit;
