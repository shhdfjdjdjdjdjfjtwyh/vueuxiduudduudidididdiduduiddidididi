<?php
/**
 * Razorpay Webhook Handler
 * Catches payment.captured, payment.failed events
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/payment.php';

// Read raw input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verify webhook signature
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
$expectedSignature = hash_hmac('sha256', $input, RAZORPAY_WEBHOOK_SECRET);

if (!hash_equals($expectedSignature, $webhookSignature)) {
    http_response_code(401);
    exit('Invalid signature');
}

if (!$data || !isset($data['event'])) {
    http_response_code(400);
    exit('Invalid payload');
}

try {
    $pdo = db();

    $event = $data['event'];
    $payment = $data['payload']['payment']['entity'] ?? [];

    $razorpayOrderId = $payment['order_id'] ?? '';
    $razorpayPaymentId = $payment['id'] ?? '';

    // Find our order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE payment_id = ? LIMIT 1");
    $stmt->execute([$razorpayOrderId]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        exit('Order not found');
    }

    switch ($event) {
        case 'payment.captured':
            if ($order['payment_status'] !== 'paid') {
                $pdo->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid', 
                        transaction_id = ?,
                        paid_at = NOW(),
                        status = 'processing'
                    WHERE id = ?
                ")->execute([$razorpayPaymentId, $order['id']]);
            }
            break;

        case 'payment.failed':
            $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")
                ->execute([$order['id']]);
            break;
    }

    // Log webhook
    error_log("Razorpay webhook: $event for order #{$order['order_number']}");

    http_response_code(200);
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}