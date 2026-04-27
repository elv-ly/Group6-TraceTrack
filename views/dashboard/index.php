<?php
// Page title for the layout
$title = "Dashboard";

// Load required dependencies
require_once __DIR__ . '/../../autoload.php';

// Ensure user is logged in
requireLogin();

// Get current user data and dashboard statistics
$me    = sessionUser();
$user  = new User($db);
$stats = $user->getDashboardStats($me['id'], $me['role']);

// Start output buffering
ob_start();
?>

<!-- Page Header -->
<div class="tt-page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <strong><?= htmlspecialchars($me['name']) ?></strong>. Here's what's happening on campus.</p>
    </div>
</div>

<!-- STATISTICS CARDS -->
<?php if ($me['role'] === 'admin'): ?>
    <!-- ADMIN DASHBOARD STATS -->
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

    <!-- ADMIN QUICK ACTIONS -->
    <div class="tt-card mt-4">
        <div class="tt-card-header">
            <h5><i class="bi bi-lightning-charge"></i> Admin Quick Actions</h5>
        </div>
        <div class="tt-card-body d-flex flex-wrap gap-2">
            <a href="#" class="tt-btn-primary-sm"><i class="bi bi-shield-check"></i> Review Claims</a>
            <a href="#" class="tt-btn-outline-sm"><i class="bi bi-eye"></i> Review Reports</a>
            <a href="#" class="tt-btn-outline-sm"><i class="bi bi-trash3"></i> Deletion Requests</a>
            <a href="#" class="tt-btn-outline-sm"><i class="bi bi-people"></i> Manage Users</a>
        </div>
    </div>

<?php else: ?>
    <!-- REGULAR USER DASHBOARD STATS -->
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
            <a href="#" class="tt-btn-outline-sm"><i class="bi bi-search"></i> Browse Items</a>
        </div>
    </div>

<?php endif; ?>

<!-- RECENT REPORTS TABLE (Placeholder) -->
<div class="tt-card mt-4">
    <div class="tt-card-header">
        <h5><i class="bi bi-clock-history"></i> Recent Item Reports</h5>
        <a href="#" class="tt-btn-outline-sm">View All</a>
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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                        No reports yet. Be the first to report an item.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
// Capture buffered content and include layout template
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
