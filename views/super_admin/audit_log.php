<?php
$title = "Audit Log";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

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

$totalPages = ceil($total / $limit);

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Audit Log</h1>
        <p>System-wide audit trail of all administrative actions.</p>
    </div>
</div>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-journal-text"></i> Audit Events</h5>
        <span class="tt-badge-count"><?= $total ?> total events</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>User</th><th>Action</th><th>Details</th><th>IP Address</th><th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No audit events yet.</td></tr>
                <?php else: foreach ($logs as $log): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></strong></td>
                    <td>
                        <span class="tt-badge tt-badge-blue">
                            <i class="bi bi-arrow-repeat"></i> <?= htmlspecialchars($log['action']) ?>
                        </span>
                    </td>
                    <td><small><?= htmlspecialchars(substr($log['details'] ?? '', 0, 80)) ?></small></td>
                    <td><code><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code></td>
                    <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<style>
.tt-badge { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .65rem; border-radius: 99px; font-size: .75rem; font-weight: 600; }
.tt-badge-blue { background: rgba(21, 101, 192, .18); color: #90CAF9; }
.tt-badge-count { font-size: .82rem; color: var(--text-muted); }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
