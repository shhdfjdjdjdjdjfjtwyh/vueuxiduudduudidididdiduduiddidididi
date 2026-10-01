<?php
/**
 * Verify Razorpay Payment
 * POST: order_id, razorpay_payment_id, razorpay_order_id, razorpay_signature
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/payment.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$orderId          = (int)($input['order_id'] ?? 0);
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpayOrderId   = $input['razorpay_order_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';

if (!$orderId || !$razorpayPaymentId || !$razorpaySignature) {
    error('Missing payment details');
}

try {
    $pdo = db();

    // Verify signature
    $expectedSignature = hash_hmac(
        'sha256',
        $razorpayOrderId . '|' . $razorpayPaymentId,
        RAZORPAY_KEY_SECRET
    );

    if (!hash_equals($expectedSignature, $razorpaySignature)) {
        // Log failed attempt
        $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")
            ->execute([$orderId]);
        error('Payment signature verification failed', 401);
    }

    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $myId]);
    $order = $stmt->fetch();

    if (!$order) error('Order not found', 404);
    if ($order['payment_status'] === 'paid') {
        success('Payment already verified');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Update order
    $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'paid',
            transaction_id = ?,
            paid_at = NOW(),
            status = 'processing',
            processed_at = NOW()
        WHERE id = ?
    ")->execute([$razorpayPaymentId, $orderId]);

    // Update user stats
    $pdo->prepare("
        UPDATE users 
        SET total_spent = total_spent + ?
        WHERE id = ?
    ")->execute([$order['total_amount'], $myId]);

    // Log transaction
    $pdo->prepare("
        INSERT INTO transactions (user_id, order_id, type, amount, payment_method, payment_id, description)
        VALUES (?, ?, 'purchase', ?, 'razorpay', ?, ?)
    ")->execute([
        $myId,
        $orderId,
        $order['total_amount'],
        $razorpayPaymentId,
        "Order #{$order['order_number']}"
    ]);

    // Agent commission (if order was placed via agent)
    if ($order['agent_id']) {
        $stmt = $pdo->prepare("SELECT commission_rate FROM agents WHERE id = ?");
        $stmt->execute([$order['agent_id']]);
        $commissionRate = (float)$stmt->fetchColumn();

        if ($commissionRate > 0) {
            $commission = round($order['total_amount'] * $commissionRate / 100, 2);

            // Add to agent wallet
            $pdo->prepare("
                UPDATE agents 
                SET wallet_balance = wallet_balance + ?,
                    total_earned = total_earned + ?
                WHERE id = ?
            ")->execute([$commission, $commission, $order['agent_id']]);

            // Commission transaction
            $pdo->prepare("
                INSERT INTO transactions (agent_id, order_id, type, amount, description)
                VALUES (?, ?, 'commission', ?, ?)
            ")->execute([
                $order['agent_id'],
                $orderId,
                $commission,
                "Commission from order #{$order['order_number']}"
            ]);
        }
    }

    // Send notification
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'order', 'Order Confirmed', ?, ?)
    ")->execute([
        $myId,
        "Your order #{$order['order_number']} has been confirmed!",
        "/my-orders"
    ]);

    $pdo->commit();

    logActivity($myId, 'payment_success', "Order #{$order['order_number']} paid via Razorpay");

    success('Payment verified successfully', [
        'order_number' => $order['order_number']
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Verification failed: ' . $e->getMessage(), 500);
}