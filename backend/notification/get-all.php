<?php
/**
 * Get User Notifications
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
        SELECT * FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$myId]);
    $notifications = $stmt->fetchAll();

    foreach ($notifications as &$n) {
        $n['is_read'] = (bool)$n['is_read'];
    }

    // Unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$myId]);
    $unread = (int)$stmt->fetchColumn();

    success('Notifications loaded', [
        'notifications' => $notifications,
        'unread' => $unread
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}