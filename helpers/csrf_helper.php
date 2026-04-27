<?php

// Get current CSRF token from session
function csrf_token() {
    return $_SESSION['csrf_token'];
}

// Generate hidden input field with CSRF token for forms
function csrf_field() {
    return '<input type="hidden" name="csrf_token" id="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// Verify submitted CSRF token matches session token
function csrf_check() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && $_POST['csrf_token'] === $_SESSION['csrf_token'];
}
