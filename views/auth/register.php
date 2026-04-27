<?php
// Page title for the layout
$title = "Register";

// Load required dependencies
require_once __DIR__ . '/../../autoload.php';

// Redirect to dashboard if already logged in
requireGuest();

// Start output buffering
ob_start();
?>

<!-- Registration page HTML content -->
<div class="tt-auth-wrap">
    <!-- Left Panel: Branding & Features -->
    <div class="tt-auth-brand">
        <div class="tt-auth-brand-inner">
            <div class="tt-brand-logo">Trace<span>Track</span></div>
            <p class="tt-brand-tag">Campus Lost &amp; Found System</p>
            <p class="tt-brand-campus">SLSU Main Campus</p>

            <div class="tt-features">
                <div class="tt-feature">
                    <i class="bi bi-shield-lock"></i>
                    <div>
                        <strong>Secure Registration</strong>
                        <p>Your data is protected with bcrypt encryption</p>
                    </div>
                </div>
                <div class="tt-feature">
                    <i class="bi bi-person-check"></i>
                    <div>
                        <strong>Campus Members Only</strong>
                        <p>Exclusively for SLSU Main Campus community</p>
                    </div>
                </div>
                <div class="tt-feature">
                    <i class="bi bi-arrow-repeat"></i>
                    <div>
                        <strong>Full Tracking</strong>
                        <p>Monitor your reports and claims in one place</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Registration Form -->
    <div class="tt-auth-form-side">
        <div class="tt-auth-card">
            <h2>Create Account</h2>
            <p class="tt-auth-sub">Join TraceTrack — SLSU Main Campus</p>

            <!-- Registration form submits to register controller -->
            <form action="/controllers/auth/register.php" method="POST" id="registerForm">
                <?= csrf_field() ?> <!-- CSRF protection token -->

                <div class="tt-form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="tt-input" placeholder="e.g. Juan dela Cruz" required>
                </div>

                <div class="tt-form-row">
                    <div class="tt-form-group">
                        <label>ID Number</label>
                        <input type="text" name="id_number" class="tt-input" placeholder="e.g. 2021-00123" required>
                    </div>
                    <div class="tt-form-group">
                        <label>Role</label>
                        <select name="role" class="tt-input">
                            <option value="student">Student</option>
                            <option value="faculty">Faculty / Staff</option>
                        </select>
                    </div>
                </div>

                <div class="tt-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="tt-input" placeholder="you@slsu.edu.ph" required>
                </div>

                <div class="tt-form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" class="tt-input" placeholder="09XXXXXXXXX" required>
                </div>

                <div class="tt-form-row">
                    <div class="tt-form-group">
                        <label>Password</label>
                        <div class="tt-input-wrap">
                            <input type="password" name="password" id="regPassword" class="tt-input" placeholder="Min. 8 chars" required>
                            <!-- Password visibility toggle -->
                            <button type="button" class="tt-eye-btn" onclick="togglePass('regPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="tt-form-group">
                        <label>Confirm Password</label>
                        <div class="tt-input-wrap">
                            <input type="password" name="confirm" id="regConfirm" class="tt-input" placeholder="Repeat password" required>
                            <!-- Confirm password visibility toggle -->
                            <button type="button" class="tt-eye-btn" onclick="togglePass('regConfirm', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="tt-btn-primary">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            </form>

            <p class="tt-auth-footer">
                Already have an account? <a href="/views/auth/login.php">Sign in here</a>
            </p>
        </div>
    </div>
</div>

<!-- JavaScript for password visibility and client-side validation -->
<script>
// Toggle password field visibility
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Client-side password match validation before submission
document.getElementById('registerForm').addEventListener('submit', function (e) {
    const pw  = document.getElementById('regPassword').value;
    const cpw = document.getElementById('regConfirm').value;
    if (pw !== cpw) {
        e.preventDefault(); // Stop form submission
        Swal.fire({ 
            title: 'Error!', 
            text: 'Passwords do not match.', 
            icon: 'error', 
            confirmButtonColor: '#1565C0' 
        });
    }
});
</script>

<?php
// Capture buffered content and include layout template
$content = ob_get_clean();
include __DIR__ . '/../auth_layout.php';
?>
