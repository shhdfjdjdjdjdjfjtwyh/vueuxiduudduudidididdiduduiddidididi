<?php
/**
 * Stripe Payment Intent (for international cards)
 * POST: order_id
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

if (!$orderId) error('Order ID required');

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
    $stmt->execute([$orderId, $myId]);
    $order = $stmt->fetch();

    if (!$order) error('Order not found', 404);

    // Create Stripe PaymentIntent via API
    $amountInCents = (int)($order['total_amount'] * 100);

    $payload = http_build_query([
        'amount'   => $amountInCents,
        'currency' => 'inr',
        'automatic_payment_methods[enabled]' => 'true',
        'metadata[order_id]' => $orderId,
        'metadata[order_number]' => $order['order_number']
    ]);

    $ch = curl_init('https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET,
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $intent = json_decode($response, true);

    if (!isset($intent['client_secret'])) {
        error('Stripe intent creation failed', 500);
    }

    success('Payment intent created', [
        'client_secret' => $intent['client_secret'],
        'publishable_key' => STRIPE_KEY
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}