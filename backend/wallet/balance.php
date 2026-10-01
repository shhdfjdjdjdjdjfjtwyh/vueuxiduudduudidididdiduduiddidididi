<?php
/**
 * Get Wallet Balance
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT balance, total_spent, total_deposit FROM users WHERE id = ?");
    $stmt->execute([$myId]);
    $data = $stmt->fetch();

    success('Balance loaded', [
        'balance'       => (float)$data['balance'],
        'total_spent'   => (float)$data['total_spent'],
        'total_deposit' => (float)$data['total_deposit']
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}