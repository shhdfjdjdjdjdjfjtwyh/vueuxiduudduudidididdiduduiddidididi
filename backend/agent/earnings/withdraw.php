<?php
/**
 * Agent Withdrawal Request
 * POST: amount, upi_id
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$agentId = getCurrentAgentId();

$amount = (float)($input['amount'] ?? 0);
$upiId = clean($input['upi_id'] ?? '');

if ($amount < 500) error('Minimum withdrawal ₹500');
if (!preg_match('/^[\w.-]+@[\w]+$/', $upiId)) error('Invalid UPI ID');

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Lock agent
    $stmt = $pdo->prepare("SELECT wallet_balance FROM agents WHERE id = ? FOR UPDATE");
    $stmt->execute([$agentId]);
    $balance = (float)$stmt->fetchColumn();

    if ($balance < $amount) {
        $pdo->rollBack();
        error('Insufficient balance');
    }

    $fee = round($amount * 2 / 100, 2);
    $net = $amount - $fee;
    $newBalance = $balance - $amount;

    // Deduct
    $pdo->prepare("UPDATE agents SET wallet_balance = ? WHERE id = ?")
        ->execute([$newBalance, $agentId]);

    // Transaction
    $pdo->prepare("
        INSERT INTO transactions (agent_id, type, amount, description, status)
        VALUES (?, 'withdrawal', ?, ?, 'pending')
    ")->execute([
        $agentId, -$amount,
        "Withdrawal to $upiId (Fee: ₹$fee)"
    ]);

    $pdo->commit();

    success('Withdrawal requested', [
        'net_amount' => $net,
        'new_balance' => $newBalance
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}