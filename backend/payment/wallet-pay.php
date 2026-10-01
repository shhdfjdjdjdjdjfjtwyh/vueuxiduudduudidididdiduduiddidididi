<?php
/**
 * Pay for order using Wallet balance
 * POST: order_id
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();
$orderId = (int)($input['order_id'] ?? 0);

if (!$orderId) error('Order ID required');

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
    $stmt->execute([$orderId, $myId]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        error('Order not found', 404);
    }

    // Check wallet balance (with lock)
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$myId]);
    $balance = (float)$stmt->fetchColumn();

    if ($balance < $order['total_amount']) {
        $pdo->rollBack();
        error('Insufficient wallet balance. Available: ₹' . $balance);
    }

    $newBalance = $balance - $order['total_amount'];

    // Deduct from wallet
    $pdo->prepare("
        UPDATE users 
        SET balance = ?, total_spent = total_spent + ?
        WHERE id = ?
    ")->execute([$newBalance, $order['total_amount'], $myId]);

    // Update order
    $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'paid',
            transaction_id = ?,
            paid_at = NOW(),
            status = 'processing'
        WHERE id = ?
    ")->execute(['WALLET-' . time(), $orderId]);

    // Transaction log
    $pdo->prepare("
        INSERT INTO transactions (
            user_id, order_id, type, amount, balance_before, balance_after, 
            payment_method, description
        ) VALUES (?, ?, 'purchase', ?, ?, ?, 'wallet', ?)
    ")->execute([
        $myId,
        $orderId,
        -$order['total_amount'],
        $balance,
        $newBalance,
        "Order #{$order['order_number']} paid via wallet"
    ]);

    // Agent commission
    if ($order['agent_id']) {
        $stmt = $pdo->prepare("SELECT commission_rate FROM agents WHERE id = ?");
        $stmt->execute([$order['agent_id']]);
        $commissionRate = (float)$stmt->fetchColumn();

        if ($commissionRate > 0) {
            $commission = round($order['total_amount'] * $commissionRate / 100, 2);

            $pdo->prepare("
                UPDATE agents 
                SET wallet_balance = wallet_balance + ?,
                    total_earned = total_earned + ?
                WHERE id = ?
            ")->execute([$commission, $commission, $order['agent_id']]);

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

    // Notification
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'order', 'Order Confirmed', ?, ?)
    ")->execute([
        $myId,
        "Your order #{$order['order_number']} has been confirmed!",
        "/my-orders"
    ]);

    $pdo->commit();

    success('Order placed successfully', [
        'order_number' => $order['order_number'],
        'new_balance'  => $newBalance
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Payment failed: ' . $e->getMessage(), 500);
}