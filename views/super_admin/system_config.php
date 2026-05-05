<?php
$title = "System Configuration";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

// Get current config
$configStmt = $db->query("SELECT config_key, config_value FROM SYSTEM_CONFIG");
$config = [];
while ($row = $configStmt->fetch()) {
    $config[$row['config_key']] = $row['config_value'];
}

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>System Configuration</h1>
        <p>Manage system-wide settings and parameters.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-gear"></i> Settings</h5>
    </div>
    <div class="tt-card-body">
        <form id="configForm">
            <div class="tt-form-group">
                <label>Site Name</label>
                <input type="text" id="site_name" class="tt-input" value="<?= htmlspecialchars($config['site_name'] ?? 'TraceTrack') ?>" required>
            </div>

            <div class="tt-form-group">
                <label>Contact Email</label>
                <input type="email" id="contact_email" class="tt-input" value="<?= htmlspecialchars($config['contact_email'] ?? '') ?>" required>
            </div>

            <div class="tt-form-group">
                <label>Max Upload Size (bytes)</label>
                <input type="number" id="max_upload_size" class="tt-input" value="<?= $config['max_upload_size'] ?? 5242880 ?>" min="1000000">
                <small class="text-muted">Current: <?= number_format(($config['max_upload_size'] ?? 5242880) / 1024 / 1024, 2) ?> MB</small>
            </div>

            <div class="tt-form-group">
                <label>Item Expiry Days</label>
                <input type="number" id="item_expiry_days" class="tt-input" value="<?= $config['item_expiry_days'] ?? 30 ?>" min="1">
                <small class="text-muted">Items older than this will be archived/deleted</small>
            </div>

            <div class="tt-form-group">
                <button type="submit" class="tt-btn-primary">
                    <i class="bi bi-check-lg"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('configForm').addEventListener('submit', async e => {
    e.preventDefault();
    const data = new FormData();
    data.append('action', 'update');
    data.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    data.append('site_name', document.getElementById('site_name').value);
    data.append('contact_email', document.getElementById('contact_email').value);
    data.append('max_upload_size', document.getElementById('max_upload_size').value);
    data.append('item_expiry_days', document.getElementById('item_expiry_days').value);

    try {
        const response = await fetch('/controllers/super_admin/system_config.php', { method: 'POST', body: data });
        const result = await response.json();
        if (result.status) {
            Swal.fire('Success!', result.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error!', result.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error!', err.message, 'error');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
