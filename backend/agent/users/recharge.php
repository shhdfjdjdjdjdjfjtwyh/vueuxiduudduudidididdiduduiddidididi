<?php
/**
 * Recharge Agent's User
 * POST: user_id, amount, action (add|deduct), reason
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$agentId = getCurrentAgentId();

$userId = (int)($input['user_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);
$action = $input['action'] ?? 'add';
$reason = clean($input['reason'] ?? 'Agent adjustment');

if (!$userId || $amount <= 0) error('User ID and amount required');
if (!in_array($action, ['add', 'deduct'])) error('Invalid action');

try {
    $pdo = db();

    // Check agent permission
    $stmt = $pdo->prepare("SELECT can_recharge FROM agents WHERE id = ?");
    $stmt->execute([$agentId]);
    if (!$stmt->fetchColumn()) error('Not authorized', 403);

    $pdo->beginTransaction();

    // Verify user belongs to this agent
    $stmt = $pdo->prepare("SELECT balance, username FROM users WHERE id = ? AND agent_id = ? FOR UPDATE");
    $stmt->execute([$userId, $agentId]);
    $user = $stmt->fetch();

    if (!$user) {
        $pdo->rollBack();
        error('User not found or not yours', 404);
    }

    $before = (float)$user['balance'];

    if ($action === 'deduct' && $before < $amount) {
        $pdo->rollBack();
        error('Insufficient user balance');
    }

    $after = $action === 'add' ? $before + $amount : $before - $amount;
    $signedAmount = $action === 'add' ? $amount : -$amount;

    // Update balance
    $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")
        ->execute([$after, $userId]);

    // Transaction
    $pdo->prepare("
        INSERT INTO transactions (
            user_id, agent_id, type, amount, balance_before, balance_after,
            payment_method, description, status
        ) VALUES (?, ?, ?, ?, ?, ?, 'agent', ?, 'success')
    ")->execute([
        $userId, $agentId,
        $action === 'add' ? 'admin_credit' : 'admin_debit',
        $signedAmount, $before, $after, $reason
    ]);

    // Notification
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message)
        VALUES (?, 'wallet', ?, ?)
    ")->execute([
        $userId,
        $action === 'add' ? 'Balance Added' : 'Balance Deducted',
        "₹" . number_format($amount, 2) . " has been " . ($action === 'add' ? 'added' : 'deducted')
    ]);

    $pdo->commit();

    logActivity($agentId, 'agent_recharge', "$action ₹$amount to {$user['username']}", 'agent');

    success("Balance {$action}ed", ['new_balance' => $after]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}