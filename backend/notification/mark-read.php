<?php
/**
 * Mark Notification as Read
 * POST: id (optional - if empty, mark all as read)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();
$id = (int)($input['id'] ?? 0);

try {
    $pdo = db();

    if ($id) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $myId]);
    } else {
        // Mark all
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")
            ->execute([$myId]);
    }

    success('Marked as read');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}