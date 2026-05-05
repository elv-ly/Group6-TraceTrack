<?php
$title = "Return Request Details";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me = sessionUser();
$encrypted_id = urldecode($_GET['return_id'] ?? '');
$return_id = decryptId($encrypted_id);

if (!$return_id || !is_numeric($return_id)) {
    $_SESSION['error'] = "Invalid return request ID.";
    header("Location: /views/notifications/index.php");
    exit;
}

$itemObj = new Item($db);
$returnDetails = $itemObj->getReturnDetails($return_id, $me['id']);

if (!$returnDetails) {
    $_SESSION['error'] = "Return request not found or access denied.";
    header("Location: /views/notifications/index.php");
    exit;
}

$isFinder = $returnDetails['finder_id'] == $me['id'];
$isOwner = $returnDetails['user_id'] == $me['id']; // user_id is the item owner
$isAdmin = in_array($me['role'], ['admin', 'super_admin']);

ob_start();
?>

<div class="tt-page-header">
    <h1>Return Request Details</h1>
    <p>Complete information about this return request.</p>
</div>

<?= csrf_field() ?>

<div class="row">
    <!-- Item Details -->
    <div class="col-md-6 mb-4">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-box-seam"></i> Lost Item Details</h5>
            </div>
            <div class="tt-card-body">
                <?php if ($returnDetails['item_photo']): ?>
                <div class="text-center mb-3">
                    <img src="/<?= htmlspecialchars($returnDetails['item_photo']) ?>" alt="Item Photo" class="img-fluid rounded" style="max-height: 200px;">
                </div>
                <?php endif; ?>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Item Name:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['item_name']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Category:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['category']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Description:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['item_description']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Location:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['item_location']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Date Occurred:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['date_occured']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Owner:</span>
                    <span class="tt-detail-value">
                        <?= htmlspecialchars($returnDetails['owner_name']) ?>
                        <small class="text-muted">(<?= htmlspecialchars($returnDetails['owner_contact']) ?>)</small>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Request Details -->
    <div class="col-md-6 mb-4">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-arrow-return-left"></i> Return Request Details</h5>
            </div>
            <div class="tt-card-body">
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Status:</span>
                    <span class="tt-detail-value">
                        <span class="tt-badge tt-badge-<?= $returnDetails['status'] === 'admin_pending' ? 'gold' : ($returnDetails['status'] === 'admin_approved' ? 'blue' : ($returnDetails['status'] === 'completed' ? 'green' : 'red')) ?>">
                            <?= ucfirst(str_replace('_', ' ', $returnDetails['status'])) ?>
                        </span>
                    </span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Finder:</span>
                    <span class="tt-detail-value">
                        <?= htmlspecialchars($returnDetails['finder_name']) ?>
                        <small class="text-muted">(<?= htmlspecialchars($returnDetails['finder_contact']) ?>)</small>
                    </span>
                </div>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Found Location:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['found_location']) ?></span>
                </div>

                <?php if ($returnDetails['finder_description']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Finder Description:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['finder_description']) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($returnDetails['proof_photo']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Proof Photo:</span>
                    <span class="tt-detail-value">
                        <a href="/<?= htmlspecialchars($returnDetails['proof_photo']) ?>" target="_blank" class="tt-link">View Photo</a>
                    </span>
                </div>
                <?php endif; ?>

                <?php if ($returnDetails['status'] === 'admin_approved' || $returnDetails['status'] === 'completed' || $returnDetails['status'] === 'failed'): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Return Location:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['coordinates']) ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Deadline:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['deadline']) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($returnDetails['status'] === 'failed' && $returnDetails['failure_reason']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Failure Reason:</span>
                    <span class="tt-detail-value text-danger"><?= htmlspecialchars($returnDetails['failure_reason']) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($returnDetails['admin_note']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Admin Note:</span>
                    <span class="tt-detail-value text-muted"><?= htmlspecialchars($returnDetails['admin_note']) ?></span>
                </div>
                <?php endif; ?>

                <div class="tt-detail-row">
                    <span class="tt-detail-label">Submitted:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['created_at']) ?></span>
                </div>

                <?php if ($returnDetails['admin_reviewed_at']): ?>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Admin Reviewed:</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($returnDetails['admin_reviewed_at']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<?php if ($isFinder && $returnDetails['status'] === 'admin_approved'): ?>
<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-gear"></i> Actions</h5>
    </div>
    <div class="tt-card-body">
        <div class="row">
            <div class="col-md-6">
                <form method="POST" action="/controllers/returns/actions.php">
                    <input type="hidden" name="action" value="complete">
                    <input type="hidden" name="return_id" value="<?= $return_id ?>">
                    <button type="submit" class="tt-btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Mark as Completed
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <button class="tt-btn-outline w-100" data-bs-toggle="modal" data-bs-target="#failureModal">
                    <i class="bi bi-exclamation-triangle"></i> Submit Failure Reason
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Failure Reason Modal -->
<?php if ($isFinder && $returnDetails['status'] === 'admin_approved'): ?>
<div class="modal fade" id="failureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content tt-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Submit Failure Reason</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/returns/actions.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="submit_failure">
                    <input type="hidden" name="return_id" value="<?= $return_id ?>">
                    <div class="tt-form-group">
                        <label>Please explain why you couldn't return the item:</label>
                        <textarea name="failure_reason" class="tt-input" rows="4" required
                            placeholder="e.g., I couldn't find the location, the owner didn't respond, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="tt-btn-primary-sm">Submit Reason</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>