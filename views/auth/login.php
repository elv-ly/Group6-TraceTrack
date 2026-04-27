<?php
// Page title for the layout
$title = "Sign In";

// Load required dependencies
require_once __DIR__ . '/../../autoload.php';

// Redirect to dashboard if already logged in
requireGuest();

// Start output buffering
ob_start();
?>

<!-- Login page HTML content -->
<div class="tt-auth-wrap">
    <!-- Left Panel: Branding & Features -->
    <div class="tt-auth-brand">
        <div class="tt-auth-brand-inner">
            <div class="tt-brand-logo">Trace<span>Track</span></div>
            <p class="tt-brand-tag">Campus Lost &amp; Found System</p>
            <p class="tt-brand-campus">SLSU Main Campus</p>

            <div class="tt-features">
                <div class="tt-feature">
                    <i class="bi bi-search"></i>
                    <div>
                        <strong>Report Lost Items</strong>
                        <p>Post what you lost and get notified when found</p>
                    </div>
                </div>
                <div class="tt-feature">
                    <i class="bi bi-box-seam"></i>
                    <div>
                        <strong>Report Found Items</strong>
                        <p>Help return items by reporting what you found</p>
                    </div>
                </div>
                <div class="tt-feature">
                    <i class="bi bi-bell"></i>
                    <div>
                        <strong>Real-Time Notifications</strong>
                        <p>Stay updated on claims and returns instantly</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="tt-auth-form-side">
        <div class="tt-auth-card">
            <h2>Welcome Back</h2>
            <p class="tt-auth-sub">Sign in to your TraceTrack account</p>

            <!-- Login form submits to login controller -->
            <form action="/controllers/auth/login.php" method="POST" id="loginForm">
                <?= csrf_field() ?> <!-- CSRF protection token -->

                <div class="tt-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="tt-input" placeholder="you@slsu.edu.ph" required autofocus>
                </div>

                <div class="tt-form-group">
                    <label>Password</label>
                    <div class="tt-input-wrap">
                        <input type="password" name="password" id="loginPassword" class="tt-input" placeholder="Your password" required>
                        <!-- Toggle password visibility button -->
                        <button type="button" class="tt-eye-btn" onclick="togglePass('loginPassword', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="tt-btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            <p class="tt-auth-footer">
                Don't have an account? <a href="/views/auth/register.php">Register here</a>
            </p>
        </div>
    </div>
</div>

<!-- JavaScript for password visibility toggling -->
<script>
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
</script>

<?php
// Capture buffered content and include layout template
$content = ob_get_clean();
include __DIR__ . '/../auth_layout.php';
?>
