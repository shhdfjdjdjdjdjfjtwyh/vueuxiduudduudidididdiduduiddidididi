<?php
/**
 * User Login
 * POST: identifier (username/email/phone), password
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) error('Invalid request body');

$identifier = clean($input['identifier'] ?? '');
$password   = $input['password'] ?? '';

if (empty($identifier) || empty($password)) {
    error('Please enter username/email and password');
}

try {
    $pdo = db();

    // Find by username, email, or phone
    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, password, full_name, avatar, role, status
        FROM users
        WHERE username = ? OR email = ? OR phone = ?
        LIMIT 1
    ");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user) error('Invalid credentials', 401);

    if ($user['status'] === 'banned') {
        error('Your account has been banned. Contact support.', 403);
    }

    if ($user['status'] === 'pending') {
        error('Your account is pending verification.', 403);
    }

    if (!verifyPassword($password, $user['password'])) {
        error('Invalid credentials', 401);
    }

    // Update last login
    $pdo->prepare("UPDATE users SET last_login = NOW(), ip_address = ? WHERE id = ?")
        ->execute([getClientIP(), $user['id']]);

    // Remove password
    unset($user['password']);

    // Set session
    setUserSession($user);

    // Log
    logActivity($user['id'], 'login', 'User logged in');

    success('Login successful!', ['user' => $user]);

} catch (Exception $e) {
    error('Login failed: ' . $e->getMessage(), 500);
}