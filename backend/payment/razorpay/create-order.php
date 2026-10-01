<?php
/**
 * Create Razorpay Order
 * POST: order_id (ShopVault order), amount
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/payment.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$orderId = (int)($input['order_id'] ?? 0);
$amount  = (float)($input['amount'] ?? 0);

if (!$orderId || !$amount) error('Order ID and amount required');

try {
    $pdo = db();

    // Verify order belongs to user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
    $stmt->execute([$orderId, $myId]);
    $order = $stmt->fetch();

    if (!$order) error('Order not found or already paid', 404);

    // Amount validation
    if (abs($order['total_amount'] - $amount) > 1) {
        error('Amount mismatch');
    }

    // Create Razorpay order via API
    $amountInPaise = (int)($amount * 100);
    $receiptId = $order['order_number'];

    $auth = base64_encode(RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);

    $payload = json_encode([
        'amount'   => $amountInPaise,
        'currency' => 'INR',
        'receipt'  => $receiptId,
        'notes'    => [
            'order_id' => $orderId,
            'user_id'  => $myId,
            'order_number' => $order['order_number']
        ]
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . $auth
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error('Razorpay order creation failed: ' . $response, 500);
    }

    $razorpayOrder = json_decode($response, true);

    if (!isset($razorpayOrder['id'])) {
        error('Invalid Razorpay response', 500);
    }

    // Save Razorpay order ID
    $pdo->prepare("UPDATE orders SET payment_id = ? WHERE id = ?")
        ->execute([$razorpayOrder['id'], $orderId]);

    success('Razorpay order created', [
        'key'                => RAZORPAY_KEY_ID,
        'amount'             => $amountInPaise,
        'currency'           => 'INR',
        'razorpay_order_id'  => $razorpayOrder['id'],
        'order_id'           => $orderId,
        'order_number'       => $order['order_number']
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}