<?php
require_once __DIR__ . '/../../../autoload.php';
requireAdmin();

header('Content-Type: application/json');

try {
    if (!csrf_check()) {
        echo json_encode(["status" => false, "message" => "Invalid security token"]);
        exit;
    }

    $enable = (int)($_POST['enable'] ?? 0);

    $db->prepare("UPDATE SYSTEM_CONFIG SET config_value = :val WHERE config_key = 'maintenance_mode'")->execute([':val' => $enable]);
    logAction($_SESSION['user_id'], $enable ? 'maintenance_mode_on' : 'maintenance_mode_off');

    echo json_encode([
        "status" => true,
        "message" => $enable ? "Maintenance mode is now ON" : "Maintenance mode is now OFF"
    ]);
} catch (Throwable $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
exit;
