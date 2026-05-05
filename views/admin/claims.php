<?php
$title = "Manage Claims";
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

$claimObj = new Claim($db);
$claims   = $claimObj->getAllAdmin();

function claimBadge($status) {
    $map = [
        'pending'          => ['gold',   'hourglass-split',        'Pending'],
        'approved'         => ['green',  'check-circle',           'Approved'],
        'rejected'         => ['red',    'x-circle',               'Rejected'],
        'returned'         => ['blue',   'check-all',              'Returned'],
        'cancel_requested' => ['purple', 'arrow-counterclockwise', 'Cancel Requested'],
    ];
    $s = $map[$status] ?? ['muted','dash',ucfirst($status)];
    return "<span class='tt-badge tt-badge-{$s[0]}'><i class='bi bi-{$s[1]}'></i> {$s[2]}</span>";
}

ob_start();
?>

<div class="tt-page-header">
    <div><h1>Manage Claims</h1><p>Review and decide on ownership claim submissions.</p></div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-shield-check"></i> All Claims</h5>
        <span class="tt-badge-count"><?= count($claims) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th><th>Claimant</th><th>Contact</th>
                    <th>Description</th><th>Proof</th>
                    <th>Date Filed</th><th>Status</th><th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($claims)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No claims yet.</td></tr>
                <?php else: foreach ($claims as $row): ?>
                <tr>
                    <td>
                        <div class="fw-500"><?= htmlspecialchars($row['item_name']) ?></div>
                        <?= $row['report_type']==='lost'
                            ? "<span class='tt-badge tt-badge-red' style='font-size:.68rem'><i class='bi bi-search'></i> Lost</span>"
                            : "<span class='tt-badge tt-badge-green' style='font-size:.68rem'><i class='bi bi-box-seam'></i> Found</span>" ?>
                    </td>
                    <td><?= htmlspecialchars($row['claimant_name']) ?></td>
                    <td><?= htmlspecialchars($row['claimant_contact']) ?></td>
                    <td>
                        <span class="tt-truncate" title="<?= htmlspecialchars($row['description']) ?>">
                            <?= htmlspecialchars(substr($row['description'],0,50)) ?>...
                        </span>
                        <br>
                        <button class="tt-btn-outline-sm tt-view-claim-details" style="font-size:.75rem;padding:.15rem .4rem;margin-top:.25rem;"
                                data-id="<?= $row['claim_id'] ?>">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                        <?php if ($row['additional_info']): ?>
                        <br><small class="text-muted"><i class="bi bi-info-circle"></i> Has additional info</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['proof_photo']): ?>
                        <a href="<?= htmlspecialchars(siteUrl($row['proof_photo'])) ?>" target="_blank" class="tt-btn-outline-sm">
                            <i class="bi bi-image"></i> View
                        </a>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.82rem;">None</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td><?= claimBadge($row['status']) ?></td>
                    <td class="text-center text-nowrap">
                        <?php if ($row['status'] === 'pending'): ?>
                        <button class="tt-btn-primary-sm mb-1 tt-approve-claim"
                                data-id="<?= $row['claim_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button class="tt-btn-outline-sm tt-reject-claim"
                                style="border-color:rgba(198,40,40,.4);color:#EF9A9A;"
                                data-id="<?= $row['claim_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-x-lg"></i> Reject
                        </button>

                        <?php elseif ($row['status'] === 'approved'): ?>
                        <button class="tt-btn-outline-sm tt-mark-returned"
                                style="border-color:rgba(46,125,50,.4);color:#A5D6A7;"
                                data-id="<?= $row['claim_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-check-all"></i> Mark Returned
                        </button>

                        <?php elseif ($row['status'] === 'cancel_requested'): ?>
                        <button class="tt-btn-primary-sm mb-1 tt-approve-cancel"
                                data-id="<?= $row['claim_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-check-lg"></i> Allow Cancel
                        </button>
                        <button class="tt-btn-outline-sm tt-reject-cancel"
                                style="border-color:rgba(198,40,40,.4);color:#EF9A9A;"
                                data-id="<?= $row['claim_id'] ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-x-lg"></i> Deny Cancel
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

<!-- Reject Claim Modal -->
<div class="modal fade" id="rejectClaimModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content tt-modal">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Claim</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-3">Rejecting claim on: <strong id="rejectClaimName"></strong></p>
            <div class="tt-form-group">
                <label>Reason for Rejection <span style="color:#EF9A9A">*</span></label>
                <textarea id="rejectClaimReason" class="tt-input" rows="3"
                    placeholder="State why this claim is being rejected..."></textarea>
            </div>
            <input type="hidden" id="rejectClaimId">
        </div>
        <div class="modal-footer">
            <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="tt-btn-primary-sm" id="confirmRejectClaim"
                    style="background:var(--red);box-shadow:none;">
                <i class="bi bi-x-lg"></i> Confirm Reject
            </button>
        </div>
    </div></div>
</div>

<!-- Deny Cancel Modal -->
<div class="modal fade" id="denyCancelModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content tt-modal">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-x-circle"></i> Deny Cancellation</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-3">Denying cancel request for: <strong id="denyCancelName"></strong></p>
            <div class="tt-form-group">
                <label>Reason for Denial <span style="color:#EF9A9A">*</span></label>
                <textarea id="denyCancelReason" class="tt-input" rows="3"
                    placeholder="State why the cancellation is being denied..."></textarea>
            </div>
            <input type="hidden" id="denyCancelId">
        </div>
        <div class="modal-footer">
            <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="tt-btn-primary-sm" id="confirmDenyCancel"
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
.tt-badge-blue{background:rgba(21,101,192,.18);color:#90CAF9;}
.tt-badge-gold{background:rgba(232,168,56,.18);color:#FFE082;}
.tt-badge-muted{background:rgba(255,255,255,.07);color:var(--text-muted);}
.tt-badge-purple{background:rgba(106,27,154,.18);color:#CE93D8;}
.tt-badge-count{font-size:.82rem;color:var(--text-muted);}
.tt-admin-note{background:rgba(255,255,255,.04);border-radius:6px;padding:.25rem .5rem;font-size:.75rem;color:var(--text-muted);max-width:180px;}
.tt-modal{background:var(--card-bg);color:var(--text);border:1px solid var(--border);}
.tt-modal .modal-header,.tt-modal .modal-footer{border-color:var(--border);}
.tt-truncate{cursor:help;}
.fw-500{font-weight:500;}
</style>

<script src="/views/admin/admin-claims-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
