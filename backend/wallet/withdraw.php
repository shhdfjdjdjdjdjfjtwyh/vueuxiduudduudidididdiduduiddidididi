<?php
/**
 * Request Withdrawal
 * POST: amount, upi_id / bank_details
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$amount = (float)($input['amount'] ?? 0);
$upiId  = clean($input['upi_id'] ?? '');

if ($amount < MIN_WALLET_WITHDRAW) error('Minimum withdrawal is ₹' . MIN_WALLET_WITHDRAW);
if (empty($upiId)) error('UPI ID required');
if (!preg_match('/^[\w.-]+@[\w]+$/', $upiId)) error('Invalid UPI ID');

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Lock user row
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$myId]);
    $balance = (float)$stmt->fetchColumn();

    if ($balance < $amount) {
        $pdo->rollBack();
        error('Insufficient balance');
    }

    // Calculate fee
    $fee = round($amount * WALLET_WITHDRAW_FEE_PERCENT / 100, 2);
    $netAmount = $amount - $fee;
    $newBalance = $balance - $amount;

    // Deduct balance
    $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")
        ->execute([$newBalance, $myId]);

    // Transaction record
    $pdo->prepare("
        INSERT INTO transactions (
            user_id, type, amount, balance_before, balance_after,
            payment_method, description, status
        ) VALUES (?, 'withdrawal', ?, ?, ?, 'upi', ?, 'pending')
    ")->execute([
        $myId,
        -$amount,
        $balance,
        $newBalance,
        "Withdrawal to $upiId (Fee: ₹$fee)"
    ]);

    $pdo->commit();

    success('Withdrawal request submitted. It will be processed within 3-5 business days.', [
        'amount'     => $amount,
        'fee'        => $fee,
        'net_amount' => $netAmount,
        'new_balance' => $newBalance
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}