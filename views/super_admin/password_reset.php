<?php
$title = "Reset User Passwords";
require_once __DIR__ . '/../../autoload.php';
requireSuperAdmin();

// Get all users
$stmt = $db->query("SELECT user_id, full_name, email, role FROM USERS ORDER BY full_name");
$users = $stmt->fetchAll();

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>Reset User Passwords</h1>
        <p>Securely reset user passwords. Users can set new passwords on first login.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-key"></i> Select User</h5>
    </div>
    <div class="tt-card-body">
        <div class="tt-form-group">
            <label>User to Reset</label>
            <select id="userSelect" class="tt-input" required>
                <option value="">-- Select a user --</option>
                <?php foreach ($users as $user): ?>
                <option value="<?= $user['user_id'] ?>">
                    <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>) — <?= ucfirst($user['role']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="tt-form-group">
            <label>New Password</label>
            <input type="password" id="newPassword" class="tt-input" placeholder="Enter new password (min 8 chars)" required>
            <small class="text-muted">Password must be at least 8 characters</small>
        </div>

        <div class="tt-form-group">
            <label>Confirm Password</label>
            <input type="password" id="confirmPassword" class="tt-input" placeholder="Confirm password" required>
        </div>

        <button type="button" id="resetBtn" class="tt-btn-primary">
            <i class="bi bi-check-lg"></i> Reset Password
        </button>
    </div>
</div>

<script>
document.getElementById('resetBtn').addEventListener('click', async () => {
    const userId = document.getElementById('userSelect').value;
    const newPwd = document.getElementById('newPassword').value;
    const confirmPwd = document.getElementById('confirmPassword').value;

    if (!userId) {
        Swal.fire('Error!', 'Please select a user', 'error');
        return;
    }

    if (newPwd.length < 8) {
        Swal.fire('Error!', 'Password must be at least 8 characters', 'error');
        return;
    }

    if (newPwd !== confirmPwd) {
        Swal.fire('Error!', 'Passwords do not match', 'error');
        return;
    }

    const confirm = await Swal.fire({
        title: 'Confirm Reset',
        text: 'This will set a new password for the selected user. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reset'
    });

    if (!confirm.isConfirmed) return;

    const data = new FormData();
    data.append('user_id', userId);
    data.append('new_password', newPwd);
    data.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

    try {
        const response = await fetch('/controllers/super_admin/password_reset.php', { method: 'POST', body: data });
        const result = await response.json();
        if (result.status) {
            Swal.fire('Success!', result.message, 'success').then(() => {
                document.getElementById('userSelect').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            });
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
