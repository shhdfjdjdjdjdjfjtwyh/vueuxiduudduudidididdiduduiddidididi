<?php
/**
 * Security Helpers
 */

function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone) === 1;
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) === 1;
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

function generateOrderNumber() {
    return 'SV' . date('Ymd') . rand(1000, 9999);
}

function generateTicketNumber() {
    return 'TKT' . date('Ymd') . rand(100, 999);
}

function generateAgentCode() {
    return 'AGT' . strtoupper(substr(md5(uniqid()), 0, 8));
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}