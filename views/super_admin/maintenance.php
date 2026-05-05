<?php
$title = "Maintenance Mode";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

// Get current maintenance status
$stmt = $db->prepare("SELECT config_value FROM SYSTEM_CONFIG WHERE config_key = 'maintenance_mode'");
$stmt->execute();
$maintenance = (int)($stmt->fetchColumn() ?? 0);

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Maintenance Mode</h1>
        <p>Enable or disable maintenance mode to restrict system access.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-wrench"></i> Maintenance Mode Control</h5>
    </div>
    <div class="tt-card-body">
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle"></i> When maintenance mode is <strong>ON</strong>, regular users cannot access the system. Only super admins can log in.
        </div>

        <div style="padding: 2rem; background: var(--card-bg); border-radius: 10px; border: 1px solid var(--border); text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">
                <?php if ($maintenance): ?>
                <span style="color: #FFC107;"><i class="bi bi-exclamation-triangle"></i></span>
                <?php else: ?>
                <span style="color: #A5D6A7;"><i class="bi bi-check-circle"></i></span>
                <?php endif; ?>
            </div>

            <h3 style="margin-bottom: 1rem;">
                Maintenance Mode is <strong><?= $maintenance ? 'ON' : 'OFF' ?></strong>
            </h3>

            <button type="button" id="toggleBtn" class="tt-btn-primary" style="background: <?= $maintenance ? '#EF9A9A' : '#A5D6A7' ?>;">
                <i class="bi bi-<?= $maintenance ? 'power-off' : 'play' ?>"></i>
                <?= $maintenance ? 'Turn OFF' : 'Turn ON' ?>
            </button>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleBtn').addEventListener('click', async () => {
    const confirm = await Swal.fire({
        title: 'Confirm',
        text: 'This will <?= $maintenance ? 'disable' : 'enable' ?> maintenance mode for all users except super admins.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Proceed'
    });

    if (!confirm.isConfirmed) return;

    const data = new FormData();
    data.append('enable', <?= $maintenance ? '0' : '1' ?>);
    data.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

    try {
        const response = await fetch('/controllers/super_admin/maintenance.php', { method: 'POST', body: data });
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
