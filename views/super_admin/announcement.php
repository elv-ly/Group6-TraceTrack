<?php
$title = "Global Announcement";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

// Get current announcement
$stmt = $db->prepare("SELECT config_value FROM SYSTEM_CONFIG WHERE config_key = 'global_announcement'");
$stmt->execute();
$announcement = $stmt->fetchColumn() ?? '';

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Global Announcement</h1>
        <p>Send a system-wide announcement to all users.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-megaphone"></i> Announcement Message</h5>
    </div>
    <div class="tt-card-body">
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle"></i> This message will be displayed to all users on their dashboard.
        </div>

        <div class="tt-form-group">
            <label>Message</label>
            <textarea id="announcementText" class="tt-input" rows="5" placeholder="Enter your announcement message..."><?= htmlspecialchars($announcement) ?></textarea>
            <small class="text-muted">Leave empty to clear announcement</small>
        </div>

        <div class="tt-form-group">
            <button type="button" id="saveBtn" class="tt-btn-primary">
                <i class="bi bi-check-lg"></i> Save Announcement
            </button>
            <?php if ($announcement): ?>
            <button type="button" id="clearBtn" class="tt-btn-outline-sm ms-2" style="color: #EF9A9A; border-color: #EF9A9A;">
                <i class="bi bi-trash"></i> Clear
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('saveBtn').addEventListener('click', savAnnouncement);
document.getElementById('clearBtn')?.addEventListener('click', clearAnnouncement);

async function savAnnouncement() {
    const message = document.getElementById('announcementText').value.trim();

    const data = new FormData();
    data.append('action', 'set');
    data.append('message', message);
    data.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

    try {
        const response = await fetch('/controllers/super_admin/announcement.php', { method: 'POST', body: data });
        const result = await response.json();
        if (result.status) {
            Swal.fire('Success!', result.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error!', result.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error!', err.message, 'error');
    }
}

async function clearAnnouncement() {
    const confirm = await Swal.fire({
        title: 'Clear Announcement?',
        text: 'This will remove the announcement from all users.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Clear'
    });

    if (confirm.isConfirmed) {
        document.getElementById('announcementText').value = '';
        savAnnouncement();
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
