<?php
/**
 * Check Session
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

startSession();

if (!isLoggedIn()) {
    error('Not logged in', 401);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, full_name, avatar, role, status, 
               balance, email_verified, created_at
        FROM users WHERE id = ? AND status != 'banned'
    ");
    $stmt->execute([getCurrentUserId()]);
    $user = $stmt->fetch();

    if (!$user) {
        destroySession();
        error('User not found', 401);
    }

    success('Session active', ['user' => $user]);

} catch (Exception $e) {
    error('Session check failed', 500);
}