<?php
/**
 * Get User Details (Admin)
 * GET: ?id=X
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireAdmin();

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) error('User ID required');

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, full_name, avatar, bio, balance,
               total_spent, total_deposit, agent_id, role, status, 
               email_verified, phone_verified, last_login, ip_address, created_at
        FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) error('User not found', 404);

    // Stats
    $user['total_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = $userId")->fetchColumn();
    $user['total_capsules'] = 0;

    // Recent orders
    $stmt = $pdo->prepare("
        SELECT id, order_number, total_amount, status, payment_status, created_at
        FROM orders WHERE user_id = ?
        ORDER BY created_at DESC LIMIT 10
    ");
    $stmt->execute([$userId]);
    $user['orders'] = $stmt->fetchAll();

    // Transactions
    $stmt = $pdo->prepare("
        SELECT id, type, amount, description, created_at
        FROM transactions WHERE user_id = ?
        ORDER BY created_at DESC LIMIT 10
    ");
    $stmt->execute([$userId]);
    $user['transactions'] = $stmt->fetchAll();

    success('User loaded', ['user' => $user]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}