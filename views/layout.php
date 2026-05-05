<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'TraceTrack' ?> — TraceTrack</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            padding-top: 50px;
            padding-bottom: 65px;
        }
        .tt-header-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #12243b;
            color: white;
            padding: 0.7rem 0;
            text-align: center;
            border-bottom: 3px solid #0b2c49;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .tt-header-top-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .tt-header-icon {
            font-size: 1.3rem;
        }
        .tt-wrap {
            flex: 1;
            display: flex;
        }
        .tt-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .tt-content {
            flex: 1;
        }
        .tt-footer-sticky {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #12243b;
            color: #E0E0E0;
            padding: 0.9rem 0;
            text-align: center;
            border-top: 3px solid #0b2c49;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.2);
        }
        .tt-footer-content {
            letter-spacing: 0.3px;
            font-size: 0.9rem;
        }
        .tt-footer-main {
            color: #B0BEC5;
            font-weight: 400;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="tt-header-top">
    <div class="tt-header-top-content">
        <span class="tt-header-icon">🔍</span>
        <span>TraceTrack — SLSU Main Campus Lost & Found</span>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ title: "Success!", text: '<?= addslashes($_SESSION['success']) ?>', icon: "success", confirmButtonColor: "#1565C0" });
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ title: "Error!", text: '<?= addslashes($_SESSION['error']) ?>', icon: "error", confirmButtonColor: "#1565C0" });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php
$me = sessionUser();
$notif       = new Notification($db);
$unreadCount = $notif->getUnreadCount($me['id']);
$currentPage = $_SERVER['PHP_SELF'];
function isActive($path) {
    global $currentPage;
    return strpos($currentPage, $path) !== false ? 'active' : '';
}
?>

<div class="tt-wrap">
    <aside class="tt-sidebar" id="sidebar">
        <div class="tt-brand">
            <span class="tt-brand-main">Trace<span>Track</span></span>
            <small class="tt-brand-sub">SLSU Main Campus</small>
        </div>

        <div class="tt-user-block">
            <div class="tt-avatar"><?= strtoupper(substr($me['name'], 0, 1)) ?></div>
            <div>
                <div class="tt-user-name"><?= htmlspecialchars($me['name']) ?></div>
                <div class="tt-user-role"><?= ucfirst($me['role']) ?></div>
            </div>
        </div>

        <nav class="tt-nav">
            <div class="tt-nav-label">Main</div>
            <a href="/views/dashboard/index.php" class="tt-nav-item <?= isActive('dashboard') ?>">
                <i class="bi bi-house-door"></i> Dashboard
            </a>

            <?php if ($me['role'] === 'student' || $me['role'] === 'faculty'): ?>
            <a href="/views/items/browse.php" class="tt-nav-item <?= isActive('browse') ?>">
                <i class="bi bi-search"></i> Browse Items
            </a>
            <a href="/views/items/create.php?type=lost" class="tt-nav-item <?= (isActive('create') && ($_GET['type']??'') === 'lost') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-plus"></i> Report Lost Item
            </a>
            <a href="/views/items/create.php?type=found" class="tt-nav-item <?= (isActive('create') && ($_GET['type']??'') === 'found') ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Report Found Item
            </a>

            <div class="tt-nav-label">My Activity</div>
            <a href="/views/items/my_reports.php" class="tt-nav-item <?= isActive('my_reports') ?>">
                <i class="bi bi-file-earmark-text"></i> My Reports
            </a>
            <a href="/views/claims/my_claims.php" class="tt-nav-item <?= isActive('my_claims') ?>">
                <i class="bi bi-hand-index"></i> My Claims
            </a>
            <a href="/views/items/my_returned_items.php" class="tt-nav-item <?= isActive('my_returned_items') ?>">
                <i class="bi bi-arrow-return-left"></i> My Returned Items
            </a>

            <a href="/views/notifications/index.php" class="tt-nav-item <?= isActive('notifications') ?>">
                <i class="bi bi-bell"></i> Notifications
                <?php if ($unreadCount > 0): ?>
                <span class="tt-notif-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <a href="/views/items/browse.php" class="tt-nav-item <?= isActive('browse') ?>">
                <i class="bi bi-search"></i> Browse Items
            </a>

            <a href="/views/auth/profile.php" class="tt-nav-item <?= isActive('profile') ?>">
                <i class="bi bi-person-circle"></i> My Profile
            </a>

            <?php if ($me['role'] === 'admin'): ?>
            <div class="tt-nav-label">Management</div>
            <a href="/views/admin/reports.php" class="tt-nav-item <?= isActive('admin/reports') ?>">
                <i class="bi bi-clipboard-data"></i> Manage Reports
            </a>
            <a href="/views/admin/claims.php" class="tt-nav-item <?= isActive('admin/claims') ?>">
                <i class="bi bi-shield-check"></i> Manage Claims
            </a>
            <a href="/views/admin/returns.php" class="tt-nav-item <?= isActive('admin/returns') ?>">
                <i class="bi bi-arrow-return-left"></i> Manage Returns
            </a>
            <a href="/views/admin/deletions.php" class="tt-nav-item <?= isActive('admin/deletions') ?>">
                <i class="bi bi-trash3"></i> Deletion Requests
            </a>
            <a href="/views/admin/users.php" class="tt-nav-item <?= isActive('admin/users') ?>">
                <i class="bi bi-people"></i> Manage Users
            </a>
            <a href="/views/admin/statistics.php" class="tt-nav-item <?= isActive('admin/statistics') ?>">
                <i class="bi bi-bar-chart"></i> Statistics
            </a>
            <a href="/views/notifications/index.php" class="tt-nav-item <?= isActive('notifications') ?>">
                <i class="bi bi-bell"></i> Notifications
                <?php if ($unreadCount > 0): ?>
                <span class="tt-notif-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>

            <div class="tt-nav-label">Governance</div>
            <a href="/views/admin/governance/audit_log.php" class="tt-nav-item <?= isActive('admin/governance/audit_log') ?>">
                <i class="bi bi-journal-text"></i> Audit Log
            </a>
            <a href="/views/admin/governance/system_config.php" class="tt-nav-item <?= isActive('admin/governance/system_config') ?>">
                <i class="bi bi-gear"></i> System Config
            </a>
            <a href="/views/admin/governance/maintenance.php" class="tt-nav-item <?= isActive('admin/governance/maintenance') ?>">
                <i class="bi bi-wrench"></i> Maintenance Mode
            </a>
            <a href="/views/admin/governance/backup.php" class="tt-nav-item <?= isActive('admin/governance/backup') ?>">
                <i class="bi bi-cloud-download"></i> Backup Database
            </a>
            <a href="/views/admin/governance/password_reset.php" class="tt-nav-item <?= isActive('admin/governance/password_reset') ?>">
                <i class="bi bi-key"></i> Reset Passwords
            </a>
            <a href="/views/admin/governance/announcement.php" class="tt-nav-item <?= isActive('admin/governance/announcement') ?>">
                <i class="bi bi-megaphone"></i> Global Announcement
            </a>
            <?php endif; ?>
        </nav>

        <div class="tt-sidebar-footer">
            <a href="/controllers/auth/logout.php" class="tt-logout-btn">
                <i class="bi bi-box-arrow-left"></i> Sign Out
            </a>
        </div>
    </aside>

    <div class="tt-main">
        <header class="tt-topbar">
            <button class="tt-toggle-btn d-md-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="tt-topbar-title"><?= $title ?? 'Dashboard' ?></div>
            <div class="tt-topbar-right d-flex align-items-center gap-3">
                <a href="/views/notifications/index.php" class="tt-notif-bell">
                    <i class="bi bi-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                    <span class="tt-notif-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <span class="badge tt-role-badge"><?= ucfirst($me['role']) ?></span>
            </div>
        </header>

        <div class="tt-content">
            <?= $content ?>
        </div>

        <!-- FOOTER -->
        <footer class="tt-footer-sticky">
            <div class="tt-footer-content">
                <div class="tt-footer-main">TraceTrack • 2026 • All Rights Reserved</div>
            </div>
        </footer>
    </div>
</div>

<script>
document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
</body>
</html>
