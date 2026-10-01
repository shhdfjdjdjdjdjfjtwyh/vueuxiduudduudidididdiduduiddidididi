<?php
/**
 * Admin Login
 * POST: email, password
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$email = clean($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) error('Email and password required');

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin) error('Invalid admin credentials', 401);
    if ($admin['status'] !== 'active') error('Admin account inactive', 403);
    if (!verifyPassword($password, $admin['password'])) error('Invalid credentials', 401);

    unset($admin['password']);

    // Set admin session
    setAdminSession($admin);

    $pdo->prepare("UPDATE users SET last_login = NOW(), ip_address = ? WHERE id = ?")
        ->execute([getClientIP(), $admin['id']]);

    logActivity($admin['id'], 'admin_login', 'Admin logged in', 'admin');

    success('Admin logged in', ['admin' => $admin]);

} catch (Exception $e) {
    error('Login failed: ' . $e->getMessage(), 500);
}