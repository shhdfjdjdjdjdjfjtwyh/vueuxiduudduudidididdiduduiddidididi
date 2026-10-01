<?php
/**
 * Get Current User Profile
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, full_name, avatar, bio, 
               balance, total_spent, total_deposit, email_verified, 
               created_at, last_login
        FROM users WHERE id = ?
    ");
    $stmt->execute([$myId]);
    $user = $stmt->fetch();

    if (!$user) error('User not found', 404);

    $user['balance'] = (float)$user['balance'];

    // Stats
    $user['total_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = $myId")->fetchColumn();

    success('Profile loaded', ['user' => $user]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}