<?php
$title = "Notifications";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me    = sessionUser();
$notif = new Notification($db);
$notifications = $notif->getAll($me['id']);

$typeLabels = [
    'claim_submitted'      => ['bi-hand-index',          'blue',   'Claim Submitted'],
    'claim_approved'       => ['bi-check-circle',         'green',  'Claim Approved'],
    'claim_rejected'       => ['bi-x-circle',             'red',    'Claim Rejected'],
    'report_approved'      => ['bi-check-circle',         'green',  'Report Approved'],
    'report_rejected'      => ['bi-x-circle',             'red',    'Report Rejected'],
    'deletion_approved'    => ['bi-trash3',               'gold',   'Deletion Approved'],
    'deletion_rejected'    => ['bi-trash3',               'red',    'Deletion Rejected'],
    'item_returned'        => ['bi-check-all',            'green',  'Item Returned'],
    'new_report'           => ['bi-clipboard-plus',       'blue',   'New Report'],
    'new_claim'            => ['bi-hand-index',           'gold',   'New Claim'],
    'new_deletion_request' => ['bi-trash3',               'gold',   'Deletion Request'],
    'new_user'             => ['bi-person-plus',          'blue',   'New User'],
    'return_request_submitted' => ['bi-arrow-return-left', 'blue',   'Return Request Submitted'],
    'return_request_approved'  => ['bi-check-circle',      'green',  'Return Request Approved'],
    'return_request_rejected'  => ['bi-x-circle',          'red',    'Return Request Rejected'],
    'return_deadline_set'      => ['bi-clock',             'gold',   'Return Deadline Set'],
    'return_failed'            => ['bi-exclamation-triangle', 'red', 'Return Failed'],
    'return_completed'         => ['bi-check-all',         'green',  'Return Completed'],
];

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Notifications</h1>
        <p>All your system notifications.</p>
    </div>
    <?php $unread = array_filter($notifications, fn($n) => !$n['is_read']); ?>
    <?php if (count($unread) > 0): ?>
    <button class="tt-btn-outline-sm" id="markAllRead">
        <i class="bi bi-check2-all"></i> Mark All as Read
    </button>
    <?php endif; ?>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-bell"></i> All Notifications</h5>
        <span class="tt-badge-count"><?= count($unread) ?> unread</span>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="tt-card-body text-center py-5">
        <i class="bi bi-bell-slash fs-1 d-block mb-2" style="color:var(--text-muted)"></i>
        <p style="color:var(--text-muted)">You have no notifications yet.</p>
    </div>
    <?php else: ?>
    <div class="tt-notif-list">
        <?php foreach ($notifications as $n):
            $meta  = $typeLabels[$n['type']] ?? ['bi-bell', 'muted', 'Notification'];
            $unreadClass = !$n['is_read'] ? 'tt-notif-unread' : '';
            $link = '';
                if ($n['reference_id'] && in_array($n['type'], ['return_request_submitted', 'return_request_approved', 'return_request_rejected', 'return_deadline_set', 'return_failed', 'return_completed'])) {
                    $link = "/views/returns/view.php?return_id=" . urlencode(encryptId($n['reference_id']));
                } elseif ($n['reference_id'] && $n['reference_type'] === 'item') {
                    // Admins go to manage reports, others go to item view
                    if ($me['role'] === 'admin' || $me['role'] === 'super_admin') {
                        $link = "/views/admin/reports.php";
                    } else {
                        $link = "/views/items/view.php?id={$n['reference_id']}";
                    }
                } elseif ($n['reference_id'] && $n['reference_type'] === 'claim') {
                    if ($me['role'] === 'admin' || $me['role'] === 'super_admin') {
                        // Admins go to manage claims
                        $link = "/views/admin/claims.php";
                    } else {
                        // Check if user is the claimant or the item owner
                        $claimQuery = $db->prepare("
                            SELECT c.user_id, i.user_id AS item_owner_id 
                            FROM CLAIMS c 
                            JOIN ITEMS i ON c.item_id = i.item_id 
                            WHERE c.claim_id = :claim_id
                        ");
                        $claimQuery->execute([':claim_id' => $n['reference_id']]);
                        $claimData = $claimQuery->fetch();
                        
                        if ($claimData && $claimData['item_owner_id'] == $me['id']) {
                            // User is the item owner → go to My Reports
                            $link = "/views/items/my_reports.php";
                        } else {
                            // User is the claimant → go to My Claims
                            $link = "/views/claims/my_claims.php";
                        }
                    }
                }
        ?>
        <div class="tt-notif-item <?= $unreadClass ?>"
             data-id="<?= $n['notification_id'] ?>"
             data-read="<?= $n['is_read'] ?>"
             <?= $link ? "data-link=\"$link\"" : '' ?>>
            <div class="tt-notif-icon tt-stat-icon <?= $meta[1] ?>">
                <i class="bi <?= $meta[0] ?>"></i>
            </div>
            <div class="tt-notif-body">
                <div class="tt-notif-label"><?= $meta[2] ?></div>
                <div class="tt-notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                <div class="tt-notif-time">
                    <i class="bi bi-clock"></i>
                    <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
                </div>
            </div>
            <?php if (!$n['is_read']): ?>
            <div class="tt-notif-dot"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.tt-notif-list { display:flex; flex-direction:column; }
.tt-notif-item {
    display:flex; align-items:flex-start; gap:1rem;
    padding:1rem 1.4rem;
    border-bottom:1px solid var(--border);
    cursor:pointer;
    transition:background .15s;
    position:relative;
}
.tt-notif-item:last-child { border-bottom:none; }
.tt-notif-item:hover { background:rgba(255,255,255,.03); }
.tt-notif-unread { background:rgba(21,101,192,.06); }
.tt-notif-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.tt-stat-icon.blue   { background:rgba(21,101,192,.18);  color:#90CAF9; }
.tt-stat-icon.gold   { background:rgba(232,168,56,.18);  color:#FFE082; }
.tt-stat-icon.green  { background:rgba(46,125,50,.18);   color:#A5D6A7; }
.tt-stat-icon.red    { background:rgba(198,40,40,.18);   color:#EF9A9A; }
.tt-stat-icon.muted  { background:rgba(255,255,255,.06); color:var(--text-muted); }
.tt-notif-body { flex:1; }
.tt-notif-label { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted); margin-bottom:.2rem; }
.tt-notif-msg { font-size:.9rem; color:var(--text); margin-bottom:.3rem; }
.tt-notif-time { font-size:.78rem; color:var(--text-muted); }
.tt-notif-dot { width:9px; height:9px; border-radius:50%; background:var(--blue-glow); flex-shrink:0; margin-top:.4rem; }
.tt-badge-count { font-size:.82rem; color:var(--text-muted); }
</style>

<script src="/views/notifications/notif-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
