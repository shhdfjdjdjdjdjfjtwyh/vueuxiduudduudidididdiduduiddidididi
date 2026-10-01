<?php
/**
 * Reset Password
 * POST: token, password
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$token    = clean($input['token'] ?? '');
$password = $input['password'] ?? '';

if (empty($token)) error('Invalid token');
if (strlen($password) < 6) error('Password must be at least 6 characters');

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE reset_token = ? AND reset_expires > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) error('Invalid or expired reset link', 401);

    // Update password
    $pdo->prepare("
        UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?
    ")->execute([hashPassword($password), $user['id']]);

    logActivity($user['id'], 'password_reset', 'Password reset via email');

    success('Password reset successful. Please login.');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}