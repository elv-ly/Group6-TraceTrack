<?php
$title = "Database Backup";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Database Backup</h1>
        <p>Download a complete database backup for safe storage.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-cloud-download"></i> Create Backup</h5>
    </div>
    <div class="tt-card-body">
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i> Database backups contain all system data including user passwords (hashed). Store securely.
        </div>

        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
            Click the button below to download a complete SQL backup of the TraceTrack database.
        </p>

        <a href="/controllers/super_admin/backup.php" class="tt-btn-primary">
            <i class="bi bi-download"></i> Download Backup Now
        </a>

        <div style="margin-top: 2rem; padding: 1rem; background: rgba(21, 101, 192, 0.1); border-radius: 8px; border-left: 4px solid #1565C0;">
            <h6>Backup Information:</h6>
            <ul style="margin: .5rem 0; padding-left: 1.5rem; color: var(--text-muted); font-size: .9rem;">
                <li>Format: SQL (.sql)</li>
                <li>Contains: All tables, data, and structure</li>
                <li>Generated: <?= date('F d, Y H:i:s') ?></li>
                <li>Recommended: Weekly backups</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
