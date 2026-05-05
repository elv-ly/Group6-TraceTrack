<?php
$title = "My Returned Items";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me = sessionUser();
$item = new Item($db);
$returnedItems = $item->getMyReturnedItems($me['id']);

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>My Returned Items</h1>
        <p>Lost items that have been successfully returned to you.</p>
    </div>
</div>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-check-all"></i> Returned Items</h5>
        <span class="tt-badge-count"><?= count($returnedItems) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Item</th><th>Category</th><th>Location</th>
                    <th>Returned by</th><th>Returned on</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($returnedItems)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No returned items yet.</td></tr>
                <?php else: foreach ($returnedItems as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                    <td><?= ucfirst($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['finder_name']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($row['finder_contact']) ?></small>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['owner_confirmed_at'])) ?></td>
                    <td><a href="/views/items/view.php?id=<?= $row['item_id'] ?>" class="tt-btn-outline-sm">View</a></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.tt-badge-count{font-size:.82rem;color:var(--text-muted);}
.tt-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;}
.tt-page-header h1{font-size:1.8rem;font-weight:600;margin-bottom:.25rem;}
.tt-page-header p{color:var(--text-muted);font-size:0.95rem;}
.table-responsive{overflow-x:auto;}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
