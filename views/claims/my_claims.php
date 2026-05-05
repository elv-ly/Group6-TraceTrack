<?php
$title = "My Claims";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me     = sessionUser();
$claim  = new Claim($db);
$claims = $claim->getMyClaims($me['id']);

function claimStatusBadge($status) {
    $map = [
        'pending'          => ['gold',   'hourglass-split', 'Pending'],
        'approved'         => ['green',  'check-circle',    'Approved'],
        'rejected'         => ['red',    'x-circle',        'Rejected'],
        'returned'         => ['blue',   'check-all',       'Returned'],
        'cancel_requested' => ['purple', 'arrow-counterclockwise', 'Cancel Requested'],
    ];
    $s = $map[$status] ?? ['muted', 'dash', ucfirst($status)];
    return "<span class='tt-badge tt-badge-{$s[0]}'><i class='bi bi-{$s[1]}'></i> {$s[2]}</span>";
}

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>My Claims</h1>
        <p>All ownership claims you have submitted.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-hand-index"></i> My Submitted Claims</h5>
        <span class="tt-badge-count"><?= count($claims) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>My Description</th>
                    <th>Date Claimed</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($claims)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        You have no claims yet.
                        <a href="/views/items/browse.php">Browse found items</a> to file a claim.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($claims as $row): ?>
                <tr>
                    <td>
                        <div class="tt-item-name"><?= htmlspecialchars($row['item_name']) ?></div>
                        <?php if ($row['photo']): ?>
                            <small class="text-muted"><i class="bi bi-image"></i> Has photo</small>
                        <?php endif; ?>
                    </td>
                    <td><?= ucfirst($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td>
                        <span class="tt-truncate" title="<?= htmlspecialchars($row['description']) ?>">
                            <?= htmlspecialchars(substr($row['description'], 0, 60)) ?>
                            <?= strlen($row['description']) > 60 ? '...' : '' ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td><?= claimStatusBadge($row['status']) ?></td>
                    <td>
                        <?php if ($row['admin_note']): ?>
                            <span class="tt-admin-note"><?= htmlspecialchars($row['admin_note']) ?></span>
                        <?php else: ?>
                            <span style="color:var(--text-muted); font-size:.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="/views/items/view.php?id=<?= $row['item_id'] ?>"
                           class="tt-btn-outline-sm mb-1">
                            <i class="bi bi-eye"></i> View Item
                        </a>

                        <?php if ($row['status'] === 'pending'): ?>
                        <button class="tt-btn-outline-sm tt-cancel-btn"
                                style="border-color:rgba(198,40,40,.4); color:#EF9A9A;"
                                data-id="<?= encryptId($row['claim_id']) ?>"
                                data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <?php elseif ($row['status'] === 'cancel_requested'): ?>
                            <span class="tt-badge tt-badge-purple" style="font-size:.72rem;">
                                <i class="bi bi-hourglass"></i> Awaiting Admin
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content tt-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Cancel Claim</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    You are requesting to cancel your claim on:
                    <strong id="cancelItemName"></strong>
                </p>
                <div class="tt-form-info">
                    <i class="bi bi-info-circle"></i>
                    The admin must approve your cancel request before it takes effect.
                    Your claim will be marked as <strong>Cancel Requested</strong> in the meantime.
                </div>
                <input type="hidden" id="cancelClaimId">
            </div>
            <div class="modal-footer">
                <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Go Back</button>
                <button type="button" class="tt-btn-primary-sm" id="confirmCancel"
                        style="background:var(--red); box-shadow:none;">
                    <i class="bi bi-send"></i> Submit Cancel Request
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.tt-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:99px; font-size:.75rem; font-weight:600; }
.tt-badge-red    { background:rgba(198,40,40,.18);   color:#EF9A9A; }
.tt-badge-green  { background:rgba(46,125,50,.18);   color:#A5D6A7; }
.tt-badge-blue   { background:rgba(21,101,192,.18);  color:#90CAF9; }
.tt-badge-gold   { background:rgba(232,168,56,.18);  color:#FFE082; }
.tt-badge-muted  { background:rgba(255,255,255,.07); color:var(--text-muted); }
.tt-badge-purple { background:rgba(106,27,154,.18);  color:#CE93D8; }
.tt-badge-count  { font-size:.82rem; color:var(--text-muted); }
.tt-item-name    { font-weight:500; }
.tt-truncate     { cursor:help; }
.tt-admin-note   { background:rgba(255,255,255,.04); border-radius:6px; padding:.25rem .5rem; font-size:.78rem; color:var(--text-muted); display:block; max-width:180px; }
.tt-modal { background:var(--card-bg); color:var(--text); border:1px solid var(--border); }
.tt-modal .modal-header { border-color:var(--border); }
.tt-modal .modal-footer { border-color:var(--border); }
.tt-form-info { background:rgba(21,101,192,.1); border:1px solid rgba(21,101,192,.25); border-radius:8px; padding:.75rem 1rem; font-size:.86rem; color:var(--text-muted); display:flex; align-items:flex-start; gap:.5rem; margin-top:.75rem; }
.tt-form-info i { color:var(--blue-glow); margin-top:.1rem; flex-shrink:0; }
</style>

<script src="/views/claims/claims-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
