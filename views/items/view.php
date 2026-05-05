<?php
$title = "Item Details";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me      = sessionUser();
$item_id = intval($_GET['id'] ?? 0);

if (!$item_id) {
    $_SESSION['error'] = "Invalid item.";
    header("Location: /views/items/my_reports.php");
    exit;
}

$item   = new Item($db);
$record = $item->readOne($item_id);

if (!$record) {
    $_SESSION['error'] = "Item not found.";
    header("Location: /views/items/my_reports.php");
    exit;
}

// Status badge helper
function statusBadge($status) {
    $map = [
        'pending_review'   => ['gold',   'hourglass-split', 'Pending Review'],
        'active'           => ['blue',   'check-circle',    'Active'],
        'claimed'          => ['purple', 'hand-index',      'Claimed'],
        'returned'         => ['green',  'check-all',       'Returned'],
        'rejected'         => ['red',    'x-circle',        'Rejected'],
        'deleted'          => ['muted',  'trash3',          'Deleted'],
    ];
    $s = $map[$status] ?? ['muted', 'dash', ucfirst($status)];
    return "<span class='tt-badge tt-badge-{$s[0]}'><i class='bi bi-{$s[1]}'></i> {$s[2]}</span>";
}

// Fetch return details if item is returned
$returnDetails = null;
if ($record['status'] === 'returned') {
    $returnSql = "SELECT r.*, u.full_name AS finder_name, u.contact AS finder_contact
                  FROM RETURNS r
                  LEFT JOIN USERS u ON r.finder_id = u.user_id
                  WHERE r.item_id = :item_id AND r.status = 'confirmed' LIMIT 1";
    $returnStmt = $db->prepare($returnSql);
    $returnStmt->execute([':item_id' => $item_id]);
    $returnDetails = $returnStmt->fetch();
}

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Item Details</h1>
        <p>Full information about this reported item.</p>
    </div>
</div>

<div class="row g-4">

    <!-- Left: Photo -->
    <div class="col-md-4">
        <div class="tt-card h-100">
            <div class="tt-card-body text-center">
                <?php if ($record['photo']): ?>
                    <img src="<?= htmlspecialchars(siteUrl($record['photo'])) ?>"
                         alt="Item Photo"
                         style="width:100%; max-height:280px; object-fit:cover; border-radius:10px;">
                <?php else: ?>
                    <div style="height:220px; display:flex; align-items:center; justify-content:center;
                                border:2px dashed var(--border); border-radius:10px; flex-direction:column; gap:.5rem;">
                        <i class="bi bi-image fs-1" style="color:var(--text-muted)"></i>
                        <p style="color:var(--text-muted); font-size:.85rem;">No photo provided</p>
                    </div>
                <?php endif; ?>

                <div class="mt-3">
                    <?= statusBadge($record['status']) ?>
                </div>

                <?php if ($record['report_type'] === 'lost'): ?>
                    <span class="tt-badge tt-badge-red mt-2"><i class="bi bi-search"></i> Lost Item</span>
                <?php else: ?>
                    <span class="tt-badge tt-badge-green mt-2"><i class="bi bi-box-seam"></i> Found Item</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Details -->
    <div class="col-md-8">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-info-circle"></i> Item Information</h5>
            </div>
            <div class="tt-card-body">

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Item Name</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($record['item_name']) ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Category</span>
                    <span class="tt-detail-value"><?= ucfirst($record['category']) ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Description</span>
                    <span class="tt-detail-value"><?= nl2br(htmlspecialchars($record['description'])) ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label"><?= $record['report_type'] === 'lost' ? 'Last Known Location' : 'Location Found' ?></span>
                    <span class="tt-detail-value"><?= htmlspecialchars($record['location']) ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label"><?= $record['report_type'] === 'lost' ? 'Date Lost' : 'Date Found' ?></span>
                    <span class="tt-detail-value"><?= date('F d, Y', strtotime($record['date_occured'])) ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Date Reported</span>
                    <span class="tt-detail-value"><?= date('F d, Y h:i A', strtotime($record['created_at'])) ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Reported By</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($record['full_name']) ?></span>
                </div>

                <?php if ($record['status'] === 'active' && $record['report_type'] === 'found'): ?>
                    <?php
                    // Only show contact if this is NOT the owner viewing their own report
                    $isOwner = ($record['user_id'] == $me['id']);
                    ?>
                    <?php if (!$isOwner): ?>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Reporter Contact</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($record['contact']) ?></span>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($record['admin_note']): ?>
                <div class="tt-form-info mt-3" style="border-color:rgba(198,40,40,.3); background:rgba(198,40,40,.08);">
                    <i class="bi bi-chat-left-text" style="color:#EF9A9A"></i>
                    <div>
                        <strong style="color:#EF9A9A;">Admin Note:</strong>
                        <span><?= htmlspecialchars($record['admin_note']) ?></span>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
            <a href="javascript:history.back()" class="tt-btn-outline-sm">
                <i class="bi bi-arrow-left"></i> Go Back
            </a>
            <?php if ($record['report_type'] === 'lost' && $record['status'] === 'active' && $record['user_id'] != $me['id'] && !$item->hasReturnRequest($record['item_id'], $me['id'])): ?>
                <a href="/views/items/return_item.php?item_id=<?= urlencode(encryptId($record['item_id'])) ?>" class="tt-btn-primary-sm" style="background: #2E7D32;">
                    <i class="bi bi-arrow-return-left"></i> Return Item
                </a>
            <?php elseif ($record['report_type'] === 'lost' && $record['status'] === 'active' && $record['user_id'] != $me['id'] && $item->hasReturnRequest($record['item_id'], $me['id'])): ?>
                <button class="tt-btn-outline-sm" disabled style="cursor:not-allowed;">
                    <i class="bi bi-arrow-return-left"></i> Return Submitted
                </button>
            <?php endif; ?>
        </div>

        <?php if ($record['status'] === 'returned' && $returnDetails): ?>
        <div class="tt-card mt-4">
            <div class="tt-card-header">
                <h5><i class="bi bi-arrow-return-left"></i> Return Details</h5>
            </div>
            <div class="tt-card-body">
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Returned By</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['finder_name'] ?? 'N/A') ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Finder Contact</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['finder_contact'] ?? 'N/A') ?></span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Location Found</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['found_location'] ?? 'N/A') ?></span>
                </div>

                <?php if ($returnDetails['finder_description']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Finder's Note</span>
                    <span class="tt-detail-value"><?= nl2br(htmlspecialchars($returnDetails['finder_description'])) ?></span>
                </div>
                <?php endif; ?>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Confirmed On</span>
                    <span class="tt-detail-value"><?= date('F d, Y h:i A', strtotime($returnDetails['owner_confirmed_at'])) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
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

.tt-detail-row {
    display: flex;
    gap: 1rem;
    padding: .65rem 0;
    border-bottom: 1px solid var(--border);
    align-items: flex-start;
}

.tt-detail-row:last-child { border-bottom: none; }

.tt-detail-label {
    font-size: .8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: var(--text-muted);
    min-width: 160px;
    flex-shrink: 0;
}

.tt-detail-value {
    font-size: .92rem;
    color: var(--text);
}

.tt-form-info {
    background:rgba(21,101,192,.1);
    border:1px solid rgba(21,101,192,.25);
    border-radius:8px;
    padding:.75rem 1rem;
    font-size:.86rem;
    color:var(--text-muted);
    display:flex;
    align-items:flex-start;
    gap:.5rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
