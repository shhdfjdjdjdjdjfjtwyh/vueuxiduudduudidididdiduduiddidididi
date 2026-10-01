<?php
/**
 * Ban/Unban User
 * POST: user_id, action (ban|unban)
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$adminId = getCurrentUserId();

$userId = (int)($input['user_id'] ?? 0);
$action = $input['action'] ?? 'ban';

if (!$userId) error('User ID required');
if ($userId === $adminId) error('Cannot ban yourself');

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) error('User not found', 404);
    if ($user['role'] === 'admin') error('Cannot ban another admin');

    $newStatus = $action === 'ban' ? 'banned' : 'active';

    $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")
        ->execute([$newStatus, $userId]);

    logActivity($adminId, "admin_$action", "{$action} user {$user['username']}", 'admin');

    success("User {$action}ned successfully");

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}