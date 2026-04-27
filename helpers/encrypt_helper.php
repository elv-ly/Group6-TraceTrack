<?php

// Secret key for encryption - keep this safe!
define('APP_KEY', 'TraceTrack@SLSU#2026$SecureKey!XZ');

// Encrypt an ID (used for hiding real IDs in URLs)
function encryptId($id) {
    $key = hash('sha256', APP_KEY);           // Convert secret key to 256-bit
    $iv  = random_bytes(16);                  // Random initialization vector
    $encrypted = openssl_encrypt($id, "AES-256-CBC", $key, 0, $iv);
    return base64_encode($iv . $encrypted);   // Combine IV + encrypted data
}

// Decrypt an encrypted ID back to original
function decryptId($data) {
    $key  = hash('sha256', APP_KEY);
    $data = base64_decode($data);
    $iv   = substr($data, 0, 16);             // Extract IV (first 16 bytes)
    $encrypted = substr($data, 16);           // Extract actual encrypted data
    return openssl_decrypt($encrypted, "AES-256-CBC", $key, 0, $iv);
}
