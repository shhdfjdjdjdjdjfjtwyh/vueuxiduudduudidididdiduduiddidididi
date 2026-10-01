<?php
/**
 * Verify wallet deposit payment
 * POST: amount, razorpay_payment_id, razorpay_order_id, razorpay_signature
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/logger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$amount            = (float)($input['amount'] ?? 0);
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpayOrderId   = $input['razorpay_order_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';

if (!$amount || !$razorpayPaymentId || !$razorpaySignature) {
    error('Missing details');
}

try {
    // Verify signature
    $expected = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);

    if (!hash_equals($expected, $razorpaySignature)) {
        error('Invalid signature', 401);
    }

    $pdo = db();
    $pdo->beginTransaction();

    // Get current balance
    $stmt = $pdo->prepare("SELECT balance, total_deposit FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$myId]);
    $user = $stmt->fetch();

    $newBalance = (float)$user['balance'] + $amount;
    $newDeposit = (float)$user['total_deposit'] + $amount;

    // Update balance
    $pdo->prepare("
        UPDATE users 
        SET balance = ?, total_deposit = ?
        WHERE id = ?
    ")->execute([$newBalance, $newDeposit, $myId]);

    // Transaction
    $pdo->prepare("
        INSERT INTO transactions (
            user_id, type, amount, balance_before, balance_after,
            payment_method, payment_id, description
        ) VALUES (?, 'deposit', ?, ?, ?, 'razorpay', ?, ?)
    ")->execute([
        $myId,
        $amount,
        $user['balance'],
        $newBalance,
        $razorpayPaymentId,
        "Wallet deposit via Razorpay"
    ]);

    // Notification
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message)
        VALUES (?, 'wallet', 'Wallet Credited', ?)
    ")->execute([
        $myId,
        "₹" . number_format($amount, 2) . " added to your wallet"
    ]);

    $pdo->commit();

    logActivity($myId, 'wallet_deposit', "Added ₹$amount");

    success('Wallet credited successfully', [
        'new_balance' => $newBalance
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}