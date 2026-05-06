<?php
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

header('Content-Type: application/json');

try {
    $limit = 50;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $countStmt = $db->query("SELECT COUNT(*) FROM AUDIT_LOG");
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("SELECT a.*, u.full_name 
                          FROM AUDIT_LOG a 
                          LEFT JOIN USERS u ON a.user_id = u.user_id 
                          ORDER BY a.created_at DESC 
                          LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();

    echo json_encode([
        'status' => true,
        'logs' => $logs,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
exit;
