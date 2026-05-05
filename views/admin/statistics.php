<?php
$title = "Statistics";
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

// Fetch all stats
$stats = [];
$queries = [
    'total_users'        => "SELECT COUNT(*) FROM USERS WHERE role IN ('student', 'faculty')",
    'total_students'     => "SELECT COUNT(*) FROM USERS WHERE role = 'student'",
    'total_faculty'      => "SELECT COUNT(*) FROM USERS WHERE role = 'faculty'",
    'inactive_users'     => "SELECT COUNT(*) FROM USERS WHERE is_active = 0 AND role IN ('student', 'faculty')",
    'total_reports'      => "SELECT COUNT(*) FROM ITEMS",
    'lost_reports'       => "SELECT COUNT(*) FROM ITEMS WHERE report_type = 'lost'",
    'found_reports'      => "SELECT COUNT(*) FROM ITEMS WHERE report_type = 'found'",
    'pending_review'     => "SELECT COUNT(*) FROM ITEMS WHERE status = 'pending_review'",
    'active_items'       => "SELECT COUNT(*) FROM ITEMS WHERE status = 'active'",
    'claimed_items'      => "SELECT COUNT(*) FROM ITEMS WHERE status = 'claimed'",
    'returned_items'     => "SELECT COUNT(*) FROM ITEMS WHERE status = 'returned'",
    'rejected_items'     => "SELECT COUNT(*) FROM ITEMS WHERE status = 'rejected'",
    'total_claims'       => "SELECT COUNT(*) FROM CLAIMS",
    'pending_claims'     => "SELECT COUNT(*) FROM CLAIMS WHERE status = 'pending'",
    'approved_claims'    => "SELECT COUNT(*) FROM CLAIMS WHERE status = 'approved'",
    'rejected_claims'    => "SELECT COUNT(*) FROM CLAIMS WHERE status = 'rejected'",
    'returned_claims'    => "SELECT COUNT(*) FROM CLAIMS WHERE status = 'returned'",
    'cancel_requests'    => "SELECT COUNT(*) FROM CLAIMS WHERE status = 'cancel_requested'",
    'total_deletions'    => "SELECT COUNT(*) FROM DELETION_REQUESTS",
    'pending_deletions'  => "SELECT COUNT(*) FROM DELETION_REQUESTS WHERE status = 'pending'",
];

foreach ($queries as $key => $sql) {
    $s = $db->prepare($sql);
    $s->execute();
    $stats[$key] = (int) $s->fetchColumn();
}

ob_start();
?>

<div class="tt-page-header">
    <div><h1>System Statistics</h1><p>Overview of all activity in TraceTrack — SLSU Main Campus.</p></div>
</div>

<!-- Users -->
<h5 class="tt-section-title"><i class="bi bi-people"></i> Users</h5>
<div class="tt-stats-grid mb-4">
    <div class="tt-stat-card"><div class="tt-stat-icon blue"><i class="bi bi-people"></i></div><div><div class="tt-stat-value"><?= $stats['total_users'] ?></div><div class="tt-stat-label">Total Users</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon blue"><i class="bi bi-person"></i></div><div><div class="tt-stat-value"><?= $stats['total_students'] ?></div><div class="tt-stat-label">Students</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon blue"><i class="bi bi-person-badge"></i></div><div><div class="tt-stat-value"><?= $stats['total_faculty'] ?></div><div class="tt-stat-label">Faculty & Staff</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon red"><i class="bi bi-person-x"></i></div><div><div class="tt-stat-value"><?= $stats['inactive_users'] ?></div><div class="tt-stat-label">Deactivated</div></div></div>
</div>

<!-- Reports -->
<h5 class="tt-section-title"><i class="bi bi-clipboard-data"></i> Item Reports</h5>
<div class="tt-stats-grid mb-4">
    <div class="tt-stat-card"><div class="tt-stat-icon blue"><i class="bi bi-clipboard-data"></i></div><div><div class="tt-stat-value"><?= $stats['total_reports'] ?></div><div class="tt-stat-label">Total Reports</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon red"><i class="bi bi-search"></i></div><div><div class="tt-stat-value"><?= $stats['lost_reports'] ?></div><div class="tt-stat-label">Lost Reports</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon green"><i class="bi bi-box-seam"></i></div><div><div class="tt-stat-value"><?= $stats['found_reports'] ?></div><div class="tt-stat-label">Found Reports</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon gold"><i class="bi bi-hourglass-split"></i></div><div><div class="tt-stat-value"><?= $stats['pending_review'] ?></div><div class="tt-stat-label">Pending Review</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon blue"><i class="bi bi-check-circle"></i></div><div><div class="tt-stat-value"><?= $stats['active_items'] ?></div><div class="tt-stat-label">Active</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon purple"><i class="bi bi-hand-index"></i></div><div><div class="tt-stat-value"><?= $stats['claimed_items'] ?></div><div class="tt-stat-label">Claimed</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon green"><i class="bi bi-check-all"></i></div><div><div class="tt-stat-value"><?= $stats['returned_items'] ?></div><div class="tt-stat-label">Returned</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon red"><i class="bi bi-x-circle"></i></div><div><div class="tt-stat-value"><?= $stats['rejected_items'] ?></div><div class="tt-stat-label">Rejected</div></div></div>
</div>

<!-- Claims -->
<h5 class="tt-section-title"><i class="bi bi-shield-check"></i> Claims</h5>
<div class="tt-stats-grid mb-4">
    <div class="tt-stat-card"><div class="tt-stat-icon blue"><i class="bi bi-hand-index"></i></div><div><div class="tt-stat-value"><?= $stats['total_claims'] ?></div><div class="tt-stat-label">Total Claims</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon gold"><i class="bi bi-hourglass-split"></i></div><div><div class="tt-stat-value"><?= $stats['pending_claims'] ?></div><div class="tt-stat-label">Pending</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon green"><i class="bi bi-check-circle"></i></div><div><div class="tt-stat-value"><?= $stats['approved_claims'] ?></div><div class="tt-stat-label">Approved</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon red"><i class="bi bi-x-circle"></i></div><div><div class="tt-stat-value"><?= $stats['rejected_claims'] ?></div><div class="tt-stat-label">Rejected</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon green"><i class="bi bi-check-all"></i></div><div><div class="tt-stat-value"><?= $stats['returned_claims'] ?></div><div class="tt-stat-label">Returned</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon purple"><i class="bi bi-arrow-counterclockwise"></i></div><div><div class="tt-stat-value"><?= $stats['cancel_requests'] ?></div><div class="tt-stat-label">Cancel Requests</div></div></div>
</div>

<!-- Deletions -->
<h5 class="tt-section-title"><i class="bi bi-trash3"></i> Other</h5>
<div class="tt-stats-grid mb-4">
    <div class="tt-stat-card"><div class="tt-stat-icon gold"><i class="bi bi-trash3"></i></div><div><div class="tt-stat-value"><?= $stats['total_deletions'] ?></div><div class="tt-stat-label">Deletion Requests</div></div></div>
    <div class="tt-stat-card"><div class="tt-stat-icon red"><i class="bi bi-hourglass-split"></i></div><div><div class="tt-stat-value"><?= $stats['pending_deletions'] ?></div><div class="tt-stat-label">Pending Deletions</div></div></div>
</div>

<style>
.tt-section-title { font-family:'Sora',sans-serif; font-size:1rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
