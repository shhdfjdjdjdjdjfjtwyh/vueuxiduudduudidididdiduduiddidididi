<?php
/**
 * Create deposit order (Razorpay)
 * POST: amount
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();
$amount = (float)($input['amount'] ?? 0);

if ($amount < MIN_WALLET_DEPOSIT) error('Minimum deposit is ₹' . MIN_WALLET_DEPOSIT);
if ($amount > MAX_WALLET_DEPOSIT) error('Maximum deposit is ₹' . MAX_WALLET_DEPOSIT);

try {
    // Create Razorpay order
    $amountInPaise = (int)($amount * 100);
    $auth = base64_encode(RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);

    $payload = json_encode([
        'amount'   => $amountInPaise,
        'currency' => 'INR',
        'receipt'  => 'DEP' . time(),
        'notes'    => [
            'user_id' => $myId,
            'type'    => 'wallet_deposit'
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

    if ($httpCode !== 200) error('Failed to create deposit order', 500);

    $razorpayOrder = json_decode($response, true);

    success('Deposit order created', [
        'key'               => RAZORPAY_KEY_ID,
        'amount'            => $amountInPaise,
        'currency'          => 'INR',
        'razorpay_order_id' => $razorpayOrder['id']
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}