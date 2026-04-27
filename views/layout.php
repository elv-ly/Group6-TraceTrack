<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'TraceTrack' ?> — TraceTrack</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- SweetAlert for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Display success message if exists -->
<?php if (isset($_SESSION['success'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ 
            title: "Success!", 
            text: '<?= addslashes($_SESSION['success']) ?>', 
            icon: "success", 
            confirmButtonColor: "#1565C0" 
        });
    });
</script>
<?php unset($_SESSION['success']); endif; ?>

<!-- Display error message if exists -->
<?php if (isset($_SESSION['error'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({ 
            title: "Error!", 
            text: '<?= addslashes($_SESSION['error']) ?>', 
            icon: "error", 
            confirmButtonColor: "#1565C0" 
        });
    });
</script>
<?php unset($_SESSION['error']); endif; ?>

<!-- MAIN APP LAYOUT: Sidebar + Main Content -->
<div class="tt-wrap">

    <!-- Sidebar -->
    <aside class="tt-sidebar" id="sidebar">
        <!-- Brand / Logo -->
        <div class="tt-brand">
            <span class="tt-brand-main">Trace<span>Track</span></span>
            <small class="tt-brand-sub">SLSU Main Campus</small>
        </div>

        <!-- Current User Info -->
        <?php $me = sessionUser(); ?>
        <div class="tt-user-block">
            <div class="tt-avatar"><?= strtoupper(substr($me['name'], 0, 1)) ?></div>
            <div>
                <div class="tt-user-name"><?= htmlspecialchars($me['name']) ?></div>
                <div class="tt-user-role"><?= ucfirst($me['role']) ?></div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="tt-nav">
            <div class="tt-nav-label">Main</div>
            <a href="/views/dashboard/index.php" class="tt-nav-item <?= (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) ? 'active' : '' ?>">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-search"></i> Browse Items
            </a>
            <a href="/views/items/create.php?type=lost" class="tt-nav-item <?= (strpos($_SERVER['PHP_SELF'], 'create') !== false && ($_GET['type'] ?? '') === 'lost') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-plus"></i> Report Lost Item
            </a>
            <a href="/views/items/create.php?type=found" class="tt-nav-item <?= (strpos($_SERVER['PHP_SELF'], 'create') !== false && ($_GET['type'] ?? '') === 'found') ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Report Found Item
            </a>

            <div class="tt-nav-label">My Activity</div>
            <a href="/views/items/my_reports.php" class="tt-nav-item <?= (strpos($_SERVER['PHP_SELF'], 'my_reports') !== false) ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> My Reports
            </a>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-hand-index"></i> My Claims
            </a>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-bell"></i> Notifications
            </a>

            <!-- Admin Section (visible only to admins) -->
            <?php if ($me['role'] === 'admin'): ?>
            <div class="tt-nav-label">Admin</div>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-shield-check"></i> Manage Claims
            </a>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-trash3"></i> Deletion Requests
            </a>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-people"></i> Users
            </a>
            <a href="#" class="tt-nav-item">
                <i class="bi bi-bar-chart"></i> Statistics
            </a>
            <?php endif; ?>
        </nav>

        <!-- Sidebar Footer with Logout -->
        <div class="tt-sidebar-footer">
            <a href="/controllers/auth/logout.php" class="tt-logout-btn">
                <i class="bi bi-box-arrow-left"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="tt-main">
        <!-- Topbar -->
        <header class="tt-topbar">
            <!-- Mobile sidebar toggle button -->
            <button class="tt-toggle-btn d-md-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="tt-topbar-title"><?= $title ?? 'Dashboard' ?></div>
            <div class="tt-topbar-right">
                <span class="badge tt-role-badge"><?= ucfirst($me['role']) ?></span>
            </div>
        </header>

        <!-- Page Content (injected from child pages) -->
        <div class="tt-content">
            <?= $content ?>
        </div>
    </div>
</div>

<!-- Mobile sidebar toggle script -->
<script>
document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>

</body>
</html>
