<?php
$title = "My Reports";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me      = sessionUser();
$item    = new Item($db);
$reports = $item->readMyReports($me['id']);

function statusBadge($status) {
    $map = [
        'pending_review' => ['gold',   'hourglass-split', 'Pending Review'],
        'active'         => ['blue',   'check-circle',    'Active'],
        'claimed'        => ['purple', 'hand-index',      'Claimed'],
        'returned'       => ['green',  'check-all',       'Returned'],
        'rejected'       => ['red',    'x-circle',        'Rejected'],
        'deleted'        => ['muted',  'trash3',          'Deleted'],
    ];
    $s = $map[$status] ?? ['muted', 'dash', ucfirst($status)];
    return "<span class='tt-badge tt-badge-{$s[0]}'><i class='bi bi-{$s[1]}'></i> {$s[2]}</span>";
}

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>My Reports</h1>
        <p>All item reports you have submitted.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/views/items/create.php?type=lost" class="tt-btn-primary-sm">
            <i class="bi bi-search"></i> Report Lost
        </a>
        <a href="/views/items/create.php?type=found" class="tt-btn-outline-sm">
            <i class="bi bi-box-seam"></i> Report Found
        </a>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-file-earmark-text"></i> My Submitted Reports</h5>
        <span class="tt-badge-count"><?= count($reports) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Date Lost/Found</th>
                    <th>Date Reported</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        You have no reports yet.
                        <a href="/views/items/create.php?type=lost">Submit one now.</a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($reports as $row): ?>
                <tr>
                    <td>
                        <div class="tt-item-name"><?= htmlspecialchars($row['item_name']) ?></div>
                        <?php if ($row['photo']): ?>
                            <small class="text-muted"><i class="bi bi-image"></i> Has photo</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['report_type'] === 'lost'): ?>
                            <span class="tt-badge tt-badge-red"><i class="bi bi-search"></i> Lost</span>
                        <?php else: ?>
                            <span class="tt-badge tt-badge-green"><i class="bi bi-box-seam"></i> Found</span>
                        <?php endif; ?>
                    </td>
                    <td><?= ucfirst($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['date_occured'])) ?></td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td><?= statusBadge($row['status']) ?></td>
                    <td class="text-center text-nowrap">
                        <?php if (in_array($row['status'], ['pending_review', 'active'])): ?>
                        <button class="tt-btn-outline-sm tt-delete-btn"
                            data-id="<?= encryptId($row['item_id']) ?>"
                            data-name="<?= htmlspecialchars($row['item_name']) ?>">
                            <i class="bi bi-trash3"></i> Request Deletion
                        </button>
                        <?php elseif ($row['status'] === 'deleted'): ?>
                            <span class="tt-badge tt-badge-muted">Deleted</span>
                        <?php else: ?>
                            <span class="tt-badge tt-badge-muted">No actions</span>
                        <?php endif; ?>

                        <?php if ($row['admin_note']): ?>
                        <div class="tt-admin-note mt-1">
                            <i class="bi bi-chat-left-text"></i>
                            <small><?= htmlspecialchars($row['admin_note']) ?></small>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Deletion Request Modal -->
<div class="modal fade" id="deletionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content tt-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash3"></i> Request Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    You are requesting deletion of: <strong id="deletionItemName"></strong><br>
                    The admin will review your request before it is removed.
                </p>
                <div class="tt-form-group">
                    <label>Reason for Deletion <span style="color:#EF9A9A">*</span></label>
                    <textarea id="deletionReason" class="tt-input" rows="3"
                        placeholder="e.g. I already found my item, Duplicate report..."></textarea>
                </div>
                <input type="hidden" id="deletionItemId">
            </div>
            <div class="modal-footer">
                <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="tt-btn-primary-sm" id="submitDeletion" style="background:var(--red);">
                    <i class="bi bi-send"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.tt-item-name { font-weight:500; }
.tt-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:99px; font-size:.75rem; font-weight:600; }
.tt-badge-red    { background:rgba(198,40,40,.18);   color:#EF9A9A; }
.tt-badge-green  { background:rgba(46,125,50,.18);   color:#A5D6A7; }
.tt-badge-blue   { background:rgba(21,101,192,.18);  color:#90CAF9; }
.tt-badge-gold   { background:rgba(232,168,56,.18);  color:#FFE082; }
.tt-badge-muted  { background:rgba(255,255,255,.07); color:var(--text-muted); }
.tt-badge-purple { background:rgba(106,27,154,.18);  color:#CE93D8; }
.tt-badge-count  { font-size:.82rem; color:var(--text-muted); }
.tt-admin-note   { background:rgba(255,255,255,.04); border-radius:6px; padding:.3rem .5rem; font-size:.78rem; color:var(--text-muted); max-width:200px; }
.tt-modal { background:var(--card-bg); color:var(--text); border:1px solid var(--border); }
.tt-modal .modal-header { border-color:var(--border); }
.tt-modal .modal-footer { border-color:var(--border); }
</style>

<script src="/views/items/items-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
