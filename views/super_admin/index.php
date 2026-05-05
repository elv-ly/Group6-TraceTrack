<?php
$title = "Super Admin Dashboard";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

// Fetch stats
$auditStmt = $db->query("SELECT COUNT(*) FROM AUDIT_LOG");
$auditCount = (int)$auditStmt->fetchColumn();

$usersStmt = $db->query("SELECT COUNT(*) FROM USERS");
$userCount = (int)$usersStmt->fetchColumn();

$inactiveStmt = $db->query("SELECT COUNT(*) FROM USERS WHERE is_active = 0");
$inactiveCount = (int)$inactiveStmt->fetchColumn();

$itemsStmt = $db->query("SELECT COUNT(*) FROM ITEMS");
$itemCount = (int)$itemsStmt->fetchColumn();

// Get system config
$configStmt = $db->query("SELECT config_key, config_value FROM SYSTEM_CONFIG");
$config = [];
while ($row = $configStmt->fetch()) {
    $config[$row['config_key']] = $row['config_value'];
}

$maintenance = (int)($config['maintenance_mode'] ?? 0);

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1><i class="bi bi-shield-lock"></i> Super Admin Dashboard</h1>
        <p>System-wide controls and monitoring.</p>
    </div>
</div>

<?php if ($maintenance): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> <strong>Maintenance Mode is ACTIVE</strong> — Regular users cannot access the system.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="tt-stats-grid mb-4">
    <div class="tt-stat-card">
        <div class="tt-stat-icon blue"><i class="bi bi-people"></i></div>
        <div>
            <div class="tt-stat-value"><?= $userCount ?></div>
            <div class="tt-stat-label">Total Users</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon red"><i class="bi bi-person-x"></i></div>
        <div>
            <div class="tt-stat-value"><?= $inactiveCount ?></div>
            <div class="tt-stat-label">Inactive Users</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon green"><i class="bi bi-clipboard-data"></i></div>
        <div>
            <div class="tt-stat-value"><?= $itemCount ?></div>
            <div class="tt-stat-label">Total Items</div>
        </div>
    </div>
    <div class="tt-stat-card">
        <div class="tt-stat-icon gold"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="tt-stat-value"><?= $auditCount ?></div>
            <div class="tt-stat-label">Audit Events</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-info-circle"></i> System Information</h5>
            </div>
            <div class="tt-card-body">
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Site Name</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($config['site_name'] ?? 'TraceTrack') ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Contact Email</span>
                    <span class="tt-detail-value"><?= htmlspecialchars($config['contact_email'] ?? 'N/A') ?></span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Max Upload Size</span>
                    <span class="tt-detail-value"><?= number_format($config['max_upload_size'] ?? 5242880 / 1024 / 1024, 2) ?> MB</span>
                </div>
                <div class="tt-detail-row">
                    <span class="tt-detail-label">Item Expiry Days</span>
                    <span class="tt-detail-value"><?= $config['item_expiry_days'] ?? 30 ?> days</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-speedometer2"></i> Quick Actions</h5>
            </div>
            <div class="tt-card-body">
                <a href="/views/super_admin/audit_log.php" class="tt-btn-outline-sm w-100 mb-2">
                    <i class="bi bi-journal-text"></i> View Audit Log
                </a>
                <a href="/views/super_admin/system_config.php" class="tt-btn-outline-sm w-100 mb-2">
                    <i class="bi bi-gear"></i> Edit System Settings
                </a>
                <a href="/views/super_admin/maintenance.php" class="tt-btn-outline-sm w-100 mb-2">
                    <i class="bi bi-wrench"></i> Maintenance Mode
                </a>
                <a href="/views/super_admin/password_reset.php" class="tt-btn-outline-sm w-100 mb-2">
                    <i class="bi bi-key"></i> Reset User Passwords
                </a>
                <a href="/views/super_admin/backup.php" class="tt-btn-outline-sm w-100 mb-2">
                    <i class="bi bi-cloud-download"></i> Download Backup
                </a>
                <a href="/views/super_admin/announcement.php" class="tt-btn-outline-sm w-100">
                    <i class="bi bi-megaphone"></i> System Announcement
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.tt-detail-row { display: flex; gap: 1rem; padding: .65rem 0; border-bottom: 1px solid var(--border); align-items: flex-start; }
.tt-detail-row:last-child { border-bottom: none; }
.tt-detail-label { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: var(--text-muted); min-width: 150px; flex-shrink: 0; }
.tt-detail-value { font-size: .92rem; color: var(--text); }
.tt-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
.tt-stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; }
.tt-stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
.tt-stat-icon.blue { background: rgba(21, 101, 192, 0.3); color: #90CAF9; }
.tt-stat-icon.red { background: rgba(198, 40, 40, 0.3); color: #EF9A9A; }
.tt-stat-icon.green { background: rgba(46, 125, 50, 0.3); color: #A5D6A7; }
.tt-stat-icon.gold { background: rgba(232, 168, 56, 0.3); color: #FFE082; }
.tt-stat-value { font-size: 1.8rem; font-weight: 700; color: var(--text); }
.tt-stat-label { font-size: .85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .3px; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
