<?php

define('APP_KEY', 'TraceTrack@SLSU#2026$SecureKey!XZ');

function encryptId($id) {
    $key = hash('sha256', APP_KEY);
    $iv  = random_bytes(16);
    $encrypted = openssl_encrypt($id, "AES-256-CBC", $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptId($data) {
    if (!$data) {
        return null;
    }
    $key  = hash('sha256', APP_KEY);
    $data = base64_decode($data);
    $iv   = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, "AES-256-CBC", $key, 0, $iv);
}
