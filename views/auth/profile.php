<?php
$title = "My Profile";
require_once __DIR__ . '/../../autoload.php';
requireLogin();

$me = sessionUser(); // This must be called after requireLogin()

// Ensure $me is not null (should never be null because requireLogin() redirects if not logged in)
if (!$me['id']) {
    header('Location: /views/auth/login.php');
    exit;
}

$user = new User($db);
$user->user_id = $me['id']; // IMPORTANT: set the user_id property
$info = $user->readOne();   // now readOne() will use the correct ID

// If $info is false or null (e.g., user deleted), redirect
if (!$info) {
    $_SESSION['error'] = "User profile not found.";
    header('Location: /views/dashboard/index.php');
    exit;
}

ob_start();
?>

<div class="tt-page-header">
    <div>
        <h1>My Profile</h1>
        <p>Manage your account information and password.</p>
    </div>
</div>

<?= csrf_field() ?>

<div class="row g-4">

    <!-- Profile Info -->
    <div class="col-md-6">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-person-circle"></i> Account Information</h5>
            </div>
            <div class="tt-card-body">
                <div class="text-center mb-3">
                    <div class="tt-avatar mx-auto" style="width:64px;height:64px;font-size:1.6rem;">
                        <?= strtoupper(substr($me['name'], 0, 1)) ?>
                    </div>
                    <div class="mt-2 fw-bold"><?= htmlspecialchars($me['name']) ?></div>
                    <div style="color:var(--text-muted);font-size:.85rem;"><?= ucfirst($me['role']) ?></div>
                </div>

                <div class="tt-form-group">
                    <label>Full Name</label>
                    <input type="text" id="profileName" class="tt-input"
                           value="<?= htmlspecialchars($info['full_name'] ?? '') ?>">
                </div>
                <div class="tt-form-group">
                    <label>Email Address</label>
                    <input type="email" class="tt-input"
                           value="<?= htmlspecialchars($info['email'] ?? '') ?>" disabled
                           style="opacity:.6; cursor:not-allowed;">
                    <small style="color:var(--text-muted);">Email cannot be changed.</small>
                </div>
                <div class="tt-form-group">
                    <label>ID Number</label>
                    <input type="text" class="tt-input"
                           value="<?= htmlspecialchars($info['id_number'] ?? '') ?>" disabled
                           style="opacity:.6; cursor:not-allowed;">
                </div>
                <div class="tt-form-group">
                    <label>Contact Number</label>
                    <input type="text" id="profileContact" class="tt-input"
                           value="<?= htmlspecialchars($info['contact'] ?? '') ?>">
                </div>
                <button class="tt-btn-primary" id="saveProfile">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-md-6">
        <div class="tt-card">
            <div class="tt-card-header">
                <h5><i class="bi bi-shield-lock"></i> Change Password</h5>
            </div>
            <div class="tt-card-body">
                <div class="tt-form-group">
                    <label>Current Password</label>
                    <div class="tt-input-wrap">
                        <input type="password" id="currentPass" class="tt-input" placeholder="Enter current password">
                        <button type="button" class="tt-eye-btn" onclick="togglePass('currentPass',this)"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="tt-form-group">
                    <label>New Password</label>
                    <div class="tt-input-wrap">
                        <input type="password" id="newPass" class="tt-input" placeholder="Min. 8 characters">
                        <button type="button" class="tt-eye-btn" onclick="togglePass('newPass',this)"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="tt-form-group">
                    <label>Confirm New Password</label>
                    <div class="tt-input-wrap">
                        <input type="password" id="confirmPass" class="tt-input" placeholder="Repeat new password">
                        <button type="button" class="tt-eye-btn" onclick="togglePass('confirmPass',this)"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button class="tt-btn-primary" id="savePassword">
                    <i class="bi bi-key"></i> Change Password
                </button>
            </div>
        </div>
    </div>

</div>

<script src="/views/auth/profile-js.js"></script>
<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>