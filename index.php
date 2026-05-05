<?php
require_once 'autoload.php';

if (isLoggedIn()) {
    header('Location: /views/dashboard/index.php');
    exit;
}

$title = "Welcome to TraceTrack";

ob_start();
?>

<div class="tt-welcome-container">
    <div class="tt-welcome-content">
        <div class="tt-welcome-header">
            <div class="tt-welcome-logo">
                <i class="bi bi-search"></i>
            </div>
            <div class="tt-brand-logo">Trace<span>Track</span></div>
            <p class="tt-welcome-subtitle">SLSU Main Campus</p>
        </div>

        <div class="tt-welcome-body">
            <h2>Welcome to TraceTrack</h2>
            <p class="tt-welcome-description">
                Your all-in-one platform for reporting lost and found items on campus. 
                Help the community recover their belongings and make campus life better.
            </p>

            <div class="tt-welcome-features">
                <div class="tt-feature">
                    <div class="tt-feature-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5>Find Items</h5>
                    <p>Browse and search for lost items reported by others</p>
                </div>

                <div class="tt-feature">
                    <div class="tt-feature-icon">
                        <i class="bi bi-clipboard-plus"></i>
                    </div>
                    <h5>Report Items</h5>
                    <p>Report lost or found items to help reunite belongings</p>
                </div>

                <div class="tt-feature">
                    <div class="tt-feature-icon">
                        <i class="bi bi-bell"></i>
                    </div>
                    <h5>Get Notified</h5>
                    <p>Receive updates on your reports and claims</p>
                </div>
            </div>
        </div>

        <div class="tt-welcome-footer">
            <a href="/views/auth/login.php" class="tt-btn tt-btn-primary">
                <span>Get Started</span>
                <i class="bi bi-arrow-right"></i>
            </a>
            <p class="tt-welcome-signup">Don't have an account? <a href="/views/auth/register.php" class="tt-btn-link">Sign up here</a></p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require 'views/auth_layout.php';
?>
