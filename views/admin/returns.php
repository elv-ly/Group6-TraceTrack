<?php
$title = "Manage Return Requests";
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

$me = sessionUser();

// Get return requests for admin review
$stmt = $db->prepare("
    SELECT r.*, i.item_name, i.description AS item_description, i.photo AS item_photo,
           i.category, i.location AS item_location, i.date_occured,
           u.full_name AS finder_name, u.contact AS finder_contact, u.email AS finder_email,
           owner.full_name AS owner_name, owner.contact AS owner_contact
    FROM RETURNS r
    JOIN ITEMS i ON r.item_id = i.item_id
    LEFT JOIN USERS u ON r.finder_id = u.user_id
    LEFT JOIN USERS owner ON i.user_id = owner.user_id
    WHERE r.status IN ('admin_pending', 'admin_approved', 'failed')
    ORDER BY r.created_at DESC
");
$stmt->execute();
$returnRequests = $stmt->fetchAll();

ob_start();
?>

<div class="tt-page-header">
    <h1>Manage Return Requests</h1>
    <p>Review and approve return requests for lost items.</p>
</div>

<?= csrf_field() ?>

<?php if (empty($returnRequests)): ?>
<div class="tt-card">
    <div class="tt-card-body text-center py-5">
        <i class="bi bi-check-circle fs-1 text-muted"></i>
        <h5 class="mt-3">No Return Requests</h5>
        <p class="text-muted">All return requests have been processed.</p>
    </div>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($returnRequests as $request): ?>
    <div class="col-md-6 mb-4">
        <div class="tt-card">
            <div class="tt-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Return Request #<?= $request['return_id'] ?></h5>
                    <span class="tt-badge tt-badge-<?= $request['status'] === 'admin_pending' ? 'gold' : ($request['status'] === 'admin_approved' ? 'blue' : 'red') ?>">
                        <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                    </span>
                </div>
            </div>
            <div class="tt-card-body">
                <!-- Item Details -->
                <div class="mb-3">
                    <h6>Lost Item Details</h6>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Item:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['item_name']) ?></span>
                    </div>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Category:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['category']) ?></span>
                    </div>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Location:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['item_location']) ?></span>
                    </div>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Owner:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['owner_name']) ?> (<?= htmlspecialchars($request['owner_contact']) ?>)</span>
                    </div>
                </div>

                <!-- Finder Details -->
                <div class="mb-3">
                    <h6>Finder Details</h6>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Name:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['finder_name']) ?></span>
                    </div>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Contact:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['finder_contact']) ?></span>
                    </div>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Found Location:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['found_location']) ?></span>
                    </div>
                    <?php if ($request['finder_description']): ?>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Description:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['finder_description']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($request['status'] === 'admin_approved'): ?>
                <!-- Approved Details -->
                <div class="mb-3">
                    <h6>Return Details</h6>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Coordinates:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['coordinates']) ?></span>
                    </div>
                    <div class="tt-detail-row">
                        <span class="tt-detail-label">Deadline:</span>
                        <span class="tt-detail-value"><?= htmlspecialchars($request['deadline']) ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($request['status'] === 'failed' && $request['failure_reason']): ?>
                <!-- Failure Details -->
                <div class="mb-3">
                    <h6>Failure Reason</h6>
                    <p class="text-muted"><?= htmlspecialchars($request['failure_reason']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <?php if ($request['status'] === 'admin_pending'): ?>
                    <!-- Approve Form -->
                    <form method="POST" class="flex-fill">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="return_id" value="<?= $request['return_id'] ?>">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" name="coordinates" class="tt-input tt-input-sm" placeholder="Coordinates" required>
                            </div>
                            <div class="col-6">
                                <input type="datetime-local" name="deadline" class="tt-input tt-input-sm" required>
                            </div>
                        </div>
                        <button type="submit" class="tt-btn-primary-sm w-100">Approve & Set Details</button>
                    </form>

                    <!-- Reject Form -->
                    <form method="POST" class="flex-fill">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="return_id" value="<?= $request['return_id'] ?>">
                        <input type="text" name="reason" class="tt-input tt-input-sm mb-2" placeholder="Rejection reason" required>
                        <button type="submit" class="tt-btn-outline-sm w-100">Reject</button>
                    </form>

                    <?php elseif ($request['status'] === 'failed'): ?>
                    <!-- Allow Resubmission -->
                    <form method="POST" class="w-100">
                        <input type="hidden" name="action" value="allow_resubmission">
                        <input type="hidden" name="return_id" value="<?= $request['return_id'] ?>">
                        <button type="submit" class="tt-btn-primary-sm w-100">Allow Re-submission</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
// Auto-refresh every 30 seconds
setTimeout(() => {
    window.location.reload();
}, 30000);
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>