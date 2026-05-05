<?php
$title = "Confirm Return";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me = sessionUser();
$encrypted_return_id = urldecode($_GET['return_id'] ?? '');
$return_id = decryptId($encrypted_return_id);

error_log("DEBUG confirm_return: encrypted=" . $encrypted_return_id . ", decrypted=" . $return_id);

if (!$return_id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: /views/dashboard/index.php");
    exit;
}

// Fetch return request details
// First try by return_id, then try by item_id (for backward compatibility)
$stmt = $db->prepare("SELECT r.*, i.item_name, i.user_id AS owner_id, u.full_name AS finder_name, u.contact AS finder_contact
                      FROM RETURNS r
                      JOIN ITEMS i ON r.item_id = i.item_id
                      LEFT JOIN USERS u ON r.finder_id = u.user_id
                      WHERE r.return_id = :id OR i.item_id = :id LIMIT 1");
$stmt->execute([':id' => $return_id]);
$return = $stmt->fetch();

error_log("DEBUG confirm_return: query returned " . ($return ? "success" : "no record"));

if (!$return) {
    $_SESSION['error'] = "Return request not found.";
    header("Location: /views/dashboard/index.php");
    exit;
}

if ($return['owner_id'] != $me['id']) {
    $_SESSION['error'] = "You are not authorized to confirm this return.";
    header("Location: /views/dashboard/index.php");
    exit;
}

if ($return['status'] !== 'pending') {
    $_SESSION['error'] = "This return request has already been processed.";
    header("Location: /views/dashboard/index.php");
    exit;
}

ob_start();
?>

<div class="tt-page-header">
    <h1>Confirm Return Request</h1>
    <p>Someone claims to have found your lost item "<?= htmlspecialchars($return['item_name']) ?>".</p>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-person"></i> Finder Information</h5>
            </div>
            <div class="tt-card-body">
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Name</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($return['finder_name']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Contact</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($return['finder_contact']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Found Location</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($return['found_location']) ?></span>
                </div>
                <?php if ($return['finder_description']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Finder's Note</span>
                    <span class="tt-detail-value"><?= nl2br(htmlspecialchars($return['finder_description'])) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($return['proof_photo']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Proof Photo</span>
                    <span class="tt-detail-value">
                        <a href="<?= htmlspecialchars(siteUrl($return['proof_photo'])) ?>" target="_blank" class="tt-btn-outline-sm">
                            <i class="bi bi-image"></i> View Photo
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-check-circle"></i> Confirm Return</h5>
            </div>
            <div class="tt-card-body">
                <p>If this is your item, confirm the return. The finder will be notified, and the item will be marked as <strong>returned</strong>.</p>
                <p>If this is not your item, you can reject the request.</p>

                <div class="d-flex gap-2 mt-3">
                    <form action="/controllers/items/confirm_return.php" method="POST" id="confirmForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="return_id" value="<?= rawurlencode($encrypted_return_id) ?>">
                        <input type="hidden" name="action" value="confirm">
                        <button type="submit" class="tt-btn-primary" style="background: #2E7D32;">
                            <i class="bi bi-check-lg"></i> Confirm & Mark Returned
                        </button>
                    </form>
                    <button id="rejectBtn" class="tt-btn-outline-sm" style="border-color: var(--red); color: #EF9A9A;">
                        <i class="bi bi-x-lg"></i> Reject Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content tt-modal">
            <form action="/controllers/items/confirm_return.php" method="POST" id="rejectForm">
                <?= csrf_field() ?>
                <input type="hidden" name="return_id" value="<?= rawurlencode($encrypted_return_id) ?>">
                <input type="hidden" name="action" value="reject">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Return Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting:</p>
                    <textarea name="rejection_reason" class="tt-input" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="tt-btn-primary-sm" style="background: var(--red);">Submit Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('rejectBtn').addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
});

// Prevent double submission
document.getElementById('confirmForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
});

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
});
</script>

<style>
.tt-detail-row { display: flex; gap: 1rem; padding: .6rem 0; border-bottom: 1px solid var(--border); }
.tt-detail-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); min-width: 120px; }
.tt-detail-value { color: var(--text); }
.tt-modal { background: var(--card-bg); border: 1px solid var(--border); }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>