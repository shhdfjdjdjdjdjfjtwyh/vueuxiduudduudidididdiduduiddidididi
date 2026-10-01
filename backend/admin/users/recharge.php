<?php
/**
 * Recharge User Balance (Admin)
 * POST: user_id, amount, action (add|deduct), reason
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$adminId = getCurrentUserId();

$userId = (int)($input['user_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);
$action = $input['action'] ?? 'add';
$reason = clean($input['reason'] ?? 'Admin adjustment');

if (!$userId || $amount <= 0) error('User ID and valid amount required');
if (!in_array($action, ['add', 'deduct'])) error('Invalid action');

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Lock user
    $stmt = $pdo->prepare("SELECT balance, username FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $pdo->rollBack();
        error('User not found', 404);
    }

    $before = (float)$user['balance'];

    if ($action === 'deduct' && $before < $amount) {
        $pdo->rollBack();
        error('User has insufficient balance');
    }

    $after = $action === 'add' ? $before + $amount : $before - $amount;
    $signedAmount = $action === 'add' ? $amount : -$amount;

    // Update balance
    $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")
        ->execute([$after, $userId]);

    // Transaction log
    $pdo->prepare("
        INSERT INTO transactions (
            user_id, type, amount, balance_before, balance_after,
            payment_method, description, status
        ) VALUES (?, ?, ?, ?, ?, 'admin', ?, 'success')
    ")->execute([
        $userId,
        $action === 'add' ? 'admin_credit' : 'admin_debit',
        $signedAmount,
        $before,
        $after,
        $reason
    ]);

    // Notification
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message)
        VALUES (?, 'wallet', ?, ?)
    ")->execute([
        $userId,
        $action === 'add' ? 'Balance Added' : 'Balance Deducted',
        "₹" . number_format($amount, 2) . " has been " . ($action === 'add' ? 'added to' : 'deducted from') . " your wallet"
    ]);

    $pdo->commit();

    logActivity($adminId, 'admin_recharge', "$action ₹$amount to {$user['username']}", 'admin');

    success("Balance {$action}ed successfully", [
        'new_balance' => $after
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}