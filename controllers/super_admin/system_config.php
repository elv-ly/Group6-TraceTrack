<?php
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';

    if ($action === 'get') {
        $stmt = $db->query("SELECT config_key, config_value FROM SYSTEM_CONFIG");
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['config_key']] = $row['config_value'];
        }
        echo json_encode(["status" => true, "config" => $config]);
        exit;
    }

    if ($action === 'update') {
        if (!csrf_check()) {
            echo json_encode(["status" => false, "message" => "Invalid security token"]);
            exit;
        }

        $site_name = trim($_POST['site_name'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $max_upload = (int)($_POST['max_upload_size'] ?? 5242880);
        $expiry_days = (int)($_POST['item_expiry_days'] ?? 30);

        if (!$site_name || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => false, "message" => "Invalid input data"]);
            exit;
        }

        $db->prepare("UPDATE SYSTEM_CONFIG SET config_value = :val WHERE config_key = 'site_name'")->execute([':val' => $site_name]);
        $db->prepare("UPDATE SYSTEM_CONFIG SET config_value = :val WHERE config_key = 'contact_email'")->execute([':val' => $contact_email]);
        $db->prepare("UPDATE SYSTEM_CONFIG SET config_value = :val WHERE config_key = 'max_upload_size'")->execute([':val' => $max_upload]);
        $db->prepare("UPDATE SYSTEM_CONFIG SET config_value = :val WHERE config_key = 'item_expiry_days'")->execute([':val' => $expiry_days]);

        logAction($_SESSION['user_id'], 'update_system_config', "Site: $site_name");
        echo json_encode(["status" => true, "message" => "Settings saved successfully."]);
        exit;
    }

    echo json_encode(["status" => false, "message" => "Invalid action"]);
} catch (Throwable $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
exit;
