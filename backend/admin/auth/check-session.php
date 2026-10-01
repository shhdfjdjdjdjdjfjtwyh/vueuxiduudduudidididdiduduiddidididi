<?php
/**
 * Check Admin Session
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

startSession();

if (!isLoggedIn() || !isAdmin()) {
    error('Not authenticated as admin', 401);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, avatar, role FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    $admin = $stmt->fetch();

    if (!$admin) {
        destroySession();
        error('Admin not found', 401);
    }

    success('Admin session active', ['admin' => $admin]);

} catch (Exception $e) {
    error('Failed', 500);
}