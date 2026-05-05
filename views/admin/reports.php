<?php
$title = "Manage Reports";
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

$itemObj = new Item($db);
$items   = $itemObj->readAllAdmin();

function statusBadge($status) {
    $map = [
        'pending_review' => ['gold',   'hourglass-split', 'Pending Review'],
        'active'         => ['blue',   'check-circle',    'Active'],
        'claimed'        => ['purple', 'hand-index',      'Claimed'],
        'returned'       => ['green',  'check-all',       'Returned'],
        'rejected'       => ['red',    'x-circle',        'Rejected'],
        'deleted'        => ['muted',  'trash3',          'Deleted'],
    ];
    $s = $map[$status] ?? ['muted','dash',ucfirst($status)];
    return "<span class='tt-badge tt-badge-{$s[0]}'><i class='bi bi-{$s[1]}'></i> {$s[2]}</span>";
}

ob_start();
?>

<div class="tt-page-header">
    <div><h1>Manage Reports</h1><p>Review, approve, or reject item reports submitted by users.</p></div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-clipboard-data"></i> All Item Reports</h5>
        <span class="tt-badge-count"><?= count($items) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th><th>Type</th><th>Category</th>
                    <th>Reported By</th><th>Location</th>
                    <th>Date Reported</th><th>Status</th><th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No reports yet.</td></tr>
                <?php else: foreach ($items as $row): ?>
                <tr>
                    <td>
                        <div class="fw-500"><?= htmlspecialchars($row['item_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars(substr($row['description'],0,50)) ?>...</small>
                    </td>
                    <td>
                        <?= $row['report_type']==='lost'
                            ? "<span class='tt-badge tt-badge-red'><i class='bi bi-search'></i> Lost</span>"
                            : "<span class='tt-badge tt-badge-green'><i class='bi bi-box-seam'></i> Found</span>" ?>
                    </td>
                    <td><?= ucfirst($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td><?= statusBadge($row['status']) ?></td>
                    <td class="text-center text-nowrap">
                        <a href="/views/items/view.php?id=<?= $row['item_id'] ?>" class="tt-btn-outline-sm mb-1">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <?php if ($row['status'] === 'pending_review'): ?>
                        <button class="tt-btn-primary-sm mb-1 tt-approve-report"
                                data-id="<?= $row['item_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button class="tt-btn-outline-sm tt-reject-report"
                                style="border-color:rgba(198,40,40,.4);color:#EF9A9A;"
                                data-id="<?= $row['item_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-x-lg"></i> Reject
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectReportModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content tt-modal">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Report</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-3">Rejecting: <strong id="rejectReportName"></strong></p>
            <div class="tt-form-group">
                <label>Reason for Rejection <span style="color:#EF9A9A">*</span></label>
                <textarea id="rejectReportReason" class="tt-input" rows="3"
                    placeholder="State the reason why this report is being rejected..."></textarea>
            </div>
            <input type="hidden" id="rejectReportId">
        </div>
        <div class="modal-footer">
            <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="tt-btn-primary-sm" id="confirmRejectReport" style="background:var(--red);box-shadow:none;">
                <i class="bi bi-x-lg"></i> Confirm Reject
            </button>
        </div>
    </div></div>
</div>

<style>
.tt-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.tt-badge-red{background:rgba(198,40,40,.18);color:#EF9A9A;}
.tt-badge-green{background:rgba(46,125,50,.18);color:#A5D6A7;}
.tt-badge-blue{background:rgba(21,101,192,.18);color:#90CAF9;}
.tt-badge-gold{background:rgba(232,168,56,.18);color:#FFE082;}
.tt-badge-muted{background:rgba(255,255,255,.07);color:var(--text-muted);}
.tt-badge-purple{background:rgba(106,27,154,.18);color:#CE93D8;}
.tt-badge-count{font-size:.82rem;color:var(--text-muted);}
.tt-modal{background:var(--card-bg);color:var(--text);border:1px solid var(--border);}
.tt-modal .modal-header,.tt-modal .modal-footer{border-color:var(--border);}
.fw-500{font-weight:500;}
</style>

<script src="/views/admin/admin-reports-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
