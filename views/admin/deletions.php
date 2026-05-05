<?php
$title = "Deletion Requests";
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

$me = sessionUser();

// Fetch all deletion requests
$stmt = $db->prepare("SELECT dr.*, i.item_name, i.report_type, u.full_name AS requester_name
                       FROM DELETION_REQUESTS dr
                       JOIN ITEMS i ON dr.item_id = i.item_id
                       LEFT JOIN USERS u ON dr.user_id = u.user_id
                       ORDER BY dr.created_at DESC");
$stmt->execute();
$requests = $stmt->fetchAll();

function drBadge($status) {
    $map = [
        'pending'  => ['gold',  'hourglass-split', 'Pending'],
        'approved' => ['green', 'check-circle',    'Approved'],
        'rejected' => ['red',   'x-circle',        'Rejected'],
    ];
    $s = $map[$status] ?? ['muted','dash',ucfirst($status)];
    return "<span class='tt-badge tt-badge-{$s[0]}'><i class='bi bi-{$s[1]}'></i> {$s[2]}</span>";
}

ob_start();
?>

<div class="tt-page-header">
    <div><h1>Deletion Requests</h1><p>Review user requests to delete their item reports.</p></div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-trash3"></i> All Deletion Requests</h5>
        <span class="tt-badge-count"><?= count($requests) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th><th>Type</th><th>Requested By</th>
                    <th>Reason</th><th>Date</th><th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No deletion requests yet.</td></tr>
                <?php else: foreach ($requests as $row): ?>
                <tr>
                    <td class="fw-500"><?= htmlspecialchars($row['item_name']) ?></td>
                    <td>
                        <?= $row['report_type']==='lost'
                            ? "<span class='tt-badge tt-badge-red'><i class='bi bi-search'></i> Lost</span>"
                            : "<span class='tt-badge tt-badge-green'><i class='bi bi-box-seam'></i> Found</span>" ?>
                    </td>
                    <td><?= htmlspecialchars($row['requester_name']) ?></td>
                    <td>
                        <span class="tt-truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                            <?= htmlspecialchars(substr($row['reason'],0,60)) ?>...
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td><?= drBadge($row['status']) ?></td>
                    <td class="text-center text-nowrap">
                        <a href="/views/items/view.php?id=<?= $row['item_id'] ?>" class="tt-btn-outline-sm mb-1">
                            <i class="bi bi-eye"></i> View Item
                        </a>
                        <?php if ($row['status'] === 'pending'): ?>
                        <button class="tt-btn-primary-sm mb-1 tt-approve-deletion"
                                data-id="<?= $row['deletion_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button class="tt-btn-outline-sm tt-reject-deletion"
                                style="border-color:rgba(198,40,40,.4);color:#EF9A9A;"
                                data-id="<?= $row['deletion_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-x-lg"></i> Deny
                        </button>
                        <?php endif; ?>
                        <?php if ($row['admin_note']): ?>
                        <div class="tt-admin-note mt-1"><?= htmlspecialchars($row['admin_note']) ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Deny Modal -->
<div class="modal fade" id="denyDeletionModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content tt-modal">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-x-circle"></i> Deny Deletion</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-3">Denying deletion of: <strong id="denyDeletionName"></strong></p>
            <div class="tt-form-group">
                <label>Reason for Denial <span style="color:#EF9A9A">*</span></label>
                <textarea id="denyDeletionReason" class="tt-input" rows="3"
                    placeholder="State why this deletion request is being denied..."></textarea>
            </div>
            <input type="hidden" id="denyDeletionId">
        </div>
        <div class="modal-footer">
            <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="tt-btn-primary-sm" id="confirmDenyDeletion"
                    style="background:var(--red);box-shadow:none;">
                <i class="bi bi-x-lg"></i> Confirm Deny
            </button>
        </div>
    </div></div>
</div>

<style>
.tt-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.tt-badge-red{background:rgba(198,40,40,.18);color:#EF9A9A;}
.tt-badge-green{background:rgba(46,125,50,.18);color:#A5D6A7;}
.tt-badge-gold{background:rgba(232,168,56,.18);color:#FFE082;}
.tt-badge-muted{background:rgba(255,255,255,.07);color:var(--text-muted);}
.tt-badge-count{font-size:.82rem;color:var(--text-muted);}
.tt-admin-note{background:rgba(255,255,255,.04);border-radius:6px;padding:.25rem .5rem;font-size:.75rem;color:var(--text-muted);max-width:180px;}
.tt-modal{background:var(--card-bg);color:var(--text);border:1px solid var(--border);}
.tt-modal .modal-header,.tt-modal .modal-footer{border-color:var(--border);}
.tt-truncate{cursor:help;}
.fw-500{font-weight:500;}
</style>

<script src="/views/admin/admin-deletions-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
