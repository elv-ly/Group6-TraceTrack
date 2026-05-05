<?php
$title = "Dashboard";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me    = sessionUser();
$user  = new User($db);
$stats = $user->getDashboardStats($me['id'], $me['role']);

// Fetch global announcement
$announcementStmt = $db->prepare("SELECT config_value FROM SYSTEM_CONFIG WHERE config_key = 'global_announcement'");
$announcementStmt->execute();
$announcement = $announcementStmt->fetchColumn() ?? '';

// Fetch only active (approved) items – include user_id for ownership check
$recentStmt = $db->prepare("
    SELECT i.item_id, i.item_name, i.report_type, i.category, i.location, i.status, i.user_id,
           DATE_FORMAT(i.created_at, '%b %d, %Y') as formatted_date,
           CASE WHEN r.return_id IS NOT NULL THEN 1 ELSE 0 END AS has_return_request
    FROM ITEMS i
    LEFT JOIN RETURNS r ON i.item_id = r.item_id AND r.finder_id = :user_id
    WHERE i.status = 'active'
    ORDER BY i.created_at DESC
    LIMIT 5
");
$recentStmt->execute([':user_id' => $me['id']]);
$recentItems = $recentStmt->fetchAll();

// Helper function for status badges
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

// Check if first login
$isFirstLogin = ($_SESSION['first_login_seen'] ?? 0) == 0;
if ($isFirstLogin) {
    // Mark as seen in session
    $_SESSION['first_login_seen'] = 1;
    // Mark as seen in database
    $updateStmt = $db->prepare("UPDATE USERS SET first_login_seen = 1 WHERE user_id = :user_id");
    $updateStmt->execute([':user_id' => $me['id']]);
}

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Dashboard</h1>
        <?php if ($isFirstLogin): ?>
            <p>Welcome to <strong>TraceTrack</strong> — SLSU Main Campus. Here's what you can do.</p>
        <?php else: ?>
            <p>Welcome back, <strong><?= htmlspecialchars($me['name']) ?></strong>. Here's what's happening on campus.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($announcement): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert" style="border-left: 4px solid #1565C0; background: rgba(21, 101, 192, 0.08); color: var(--text);">
    <i class="bi bi-megaphone" style="color: #1565C0;"></i>
    <strong style="color: #1565C0;">System Announcement:</strong>
    <div style="margin-top: 0.5rem;">
        <?= nl2br(htmlspecialchars($announcement)) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- STAT CARDS -->
<?php if ($me['role'] === 'admin'): ?>
<div class="tt-stats-grid">
    <div class="tt-stat-card">
        <div class="tt-stat-icon blue"><i class="bi bi-people"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['total_users'] ?? 0 ?></div>
            <div class="tt-stat-label">Registered Users</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon gold"><i class="bi bi-clipboard-data"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['total_reports'] ?? 0 ?></div>
            <div class="tt-stat-label">Total Reports</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon red"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['pending_review'] ?? 0 ?></div>
            <div class="tt-stat-label">Pending Review</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon purple"><i class="bi bi-hand-index"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['pending_claims'] ?? 0 ?></div>
            <div class="tt-stat-label">Pending Claims</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon green"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['total_returned'] ?? 0 ?></div>
            <div class="tt-stat-label">Items Returned</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon red"><i class="bi bi-trash3"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['pending_deletions'] ?? 0 ?></div>
            <div class="tt-stat-label">Deletion Requests</div>
        </div>
    </div>
</div>

<!-- ADMIN/SUPER ADMIN QUICK ACTIONS -->
<div class="tt-card mt-4">
    <div class="tt-card-header">
        <h5><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
    </div>
    <div class="tt-card-body d-flex flex-wrap gap-2">
        <a href="/views/admin/claims.php" class="tt-btn-primary-sm"><i class="bi bi-shield-check"></i> Review Claims</a>
        <a href="/views/admin/returns.php" class="tt-btn-primary-sm"><i class="bi bi-arrow-return-left"></i> Review Returns</a>
        <a href="/views/admin/reports.php" class="tt-btn-outline-sm"><i class="bi bi-eye"></i> Review Reports</a>
        <a href="/views/admin/deletions.php" class="tt-btn-outline-sm"><i class="bi bi-trash3"></i> Deletion Requests</a>
        <a href="/views/admin/users.php" class="tt-btn-outline-sm"><i class="bi bi-people"></i> Manage Users</a>
    </div>
</div>

<?php else: ?>

<div class="tt-stats-grid">
    <div class="tt-stat-card">
        <div class="tt-stat-icon blue"><i class="bi bi-file-earmark-text"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['my_reports'] ?? 0 ?></div>
            <div class="tt-stat-label">My Reports</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon gold"><i class="bi bi-hand-index"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['my_claims'] ?? 0 ?></div>
            <div class="tt-stat-label">My Claims</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon red"><i class="bi bi-search"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['lost_items'] ?? 0 ?></div>
            <div class="tt-stat-label">Active Lost Items</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon green"><i class="bi bi-box-seam"></i></div>
        <div>
            <div class="tt-stat-value"><?= $stats['found_items'] ?? 0 ?></div>
            <div class="tt-stat-label">Active Found Items</div>
        </div>
    </div>
</div>

<!-- USER QUICK ACTIONS -->
<div class="tt-card mt-4">
    <div class="tt-card-header">
        <h5><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
    </div>
    <div class="tt-card-body d-flex flex-wrap gap-2">
        <a href="/views/items/create.php?type=lost" class="tt-btn-primary-sm"><i class="bi bi-clipboard-plus"></i> Report Lost Item</a>
        <a href="/views/items/create.php?type=found" class="tt-btn-outline-sm"><i class="bi bi-box-seam"></i> Report Found Item</a>
        <a href="/views/items/browse.php" class="tt-btn-outline-sm"><i class="bi bi-search"></i> Browse Items</a>
    </div>
</div>

<?php endif; ?>

<!-- RECENT REPORTS TABLE (only active items, visible to all) -->
<div class="tt-card mt-4">
    <div class="tt-card-header">
        <h5><i class="bi bi-clock-history"></i> Recent Approved Reports</h5>
        <a href="/views/items/browse.php" class="tt-btn-outline-sm">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentItems)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                        No approved reports yet. Check back later.
                    </td>
                </tr>
                <?php else: foreach ($recentItems as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td>
                        <?php if ($item['report_type'] === 'lost'): ?>
                            <span class="tt-badge tt-badge-red"><i class="bi bi-search"></i> Lost</span>
                        <?php else: ?>
                            <span class="tt-badge tt-badge-green"><i class="bi bi-box-seam"></i> Found</span>
                        <?php endif; ?>
                    </td>
                    <td><?= ucfirst($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['location']) ?></td>
                    <td><?= statusBadge($item['status']) ?></td>
                    <td><?= $item['formatted_date'] ?></td>
                    <td class="text-nowrap">
                        <!-- View button always appears -->
                        <a href="/views/items/view.php?id=<?= $item['item_id'] ?>" class="tt-btn-outline-sm">
                            <i class="bi bi-eye"></i> View
                        </a>

                        <!-- Claim button for found items (only if not owned by current user) -->
                        <?php if ($item['report_type'] === 'found' && $item['user_id'] != $me['id']): ?>
                            <button class="tt-btn-primary-sm tt-claim-dashboard-btn mt-1 mt-sm-0 ms-sm-1"
                                    data-id="<?= $item['item_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['item_name']) ?>">
                                <i class="bi bi-hand-index"></i> Claim
                            </button>
                        <?php endif; ?>

                        <!-- Return button for lost items (only if not owned by current user and no return request submitted) -->
                        <?php if ($item['report_type'] === 'lost' && $item['user_id'] != $me['id'] && !$item['has_return_request']): ?>
                            <a href="/views/items/return_item.php?item_id=<?= urlencode(encryptId($item['item_id'])) ?>" 
                               class="tt-btn-primary-sm mt-1 mt-sm-0 ms-sm-1" style="background:#2E7D32;">
                                <i class="bi bi-arrow-return-left"></i> Return
                            </a>
                        <?php elseif ($item['report_type'] === 'lost' && $item['user_id'] != $me['id'] && $item['has_return_request']): ?>
                            <button class="tt-btn-outline-sm mt-1 mt-sm-0 ms-sm-1" disabled style="cursor:not-allowed;">
                                <i class="bi bi-arrow-return-left"></i> Return Submitted
                            </button>
                        <?php endif; ?>
                     </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Claim Modal (same as browse page) -->
<div class="modal fade" id="claimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content tt-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-hand-index"></i> File a Claim</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/controllers/claims/create.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" id="claimItemId">
                <div class="modal-body">
                    <div class="tt-form-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        You are claiming: <strong id="claimItemName"></strong>.
                        Provide thorough proof of ownership — the admin will review everything before approving.
                    </div>

                    <div class="tt-form-group">
                        <label>Describe Distinguishing Features <span class="tt-required">*</span></label>
                        <textarea name="description" class="tt-input" rows="4"
                            placeholder="Describe specific features that only the owner would know — scratches, stickers, contents inside, engravings, color details, etc."
                            required></textarea>
                    </div>

                    <div class="tt-form-group">
                        <label>Proof Photo <span class="tt-muted-label">(Receipt, photo with item, ID alongside item, etc.)</span></label>
                        <div class="tt-file-drop" id="proofDrop">
                            <input type="file" name="proof_photo" id="proofInput" accept="image/jpeg,image/png,image/webp" hidden>
                            <div id="proofPrompt">
                                <i class="bi bi-cloud-arrow-up fs-2" style="color:var(--text-muted)"></i>
                                <p>Click to upload proof photo</p>
                            </div>
                            <div id="proofPreview" style="display:none;">
                                <img id="proofImg" src="" style="max-height:140px; border-radius:8px;">
                                <p id="proofFileName" class="mt-1" style="font-size:.8rem; color:var(--text-muted)"></p>
                            </div>
                        </div>
                    </div>

                    <div class="tt-form-group">
                        <label>Additional Information <span class="tt-muted-label">(Serial number, brand, model, purchase date, etc.)</span></label>
                        <textarea name="additional_info" class="tt-input" rows="3"
                            placeholder="Any other identifying details that support your claim..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="tt-btn-outline-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="tt-btn-primary-sm">
                        <i class="bi bi-send"></i> Submit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Claim buttons on dashboard
    $(".tt-claim-dashboard-btn").on("click", function() {
        const id   = $(this).data("id");
        const name = $(this).data("name");
        $("#claimItemId").val(id);
        $("#claimItemName").text(name);
        // Reset form
        $("[name='description']").val("");
        $("[name='additional_info']").val("");
        $("#proofPrompt").show();
        $("#proofPreview").hide();
        new bootstrap.Modal(document.getElementById("claimModal")).show();
    });

    // Proof photo preview (same as browse page)
    const proofDrop    = document.getElementById("proofDrop");
    const proofInput   = document.getElementById("proofInput");
    const proofPrompt  = document.getElementById("proofPrompt");
    const proofPreview = document.getElementById("proofPreview");
    const proofImg     = document.getElementById("proofImg");
    const proofName    = document.getElementById("proofFileName");

    if (proofDrop) {
        proofDrop.addEventListener("click", () => proofInput.click());
        proofInput.addEventListener("change", function() {
            if (proofInput.files[0]) handleProof(proofInput.files[0]);
        });
    }

    function handleProof(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            proofImg.src = e.target.result;
            proofName.textContent = file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
            proofPrompt.style.display  = "none";
            proofPreview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>