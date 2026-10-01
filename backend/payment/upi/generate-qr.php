<?php
/**
 * Generate UPI QR Code for order
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

    // Generate UPI link
    $upiParams = [
        'pa' => UPI_ID,                               // Payee address
        'pn' => UPI_NAME,                             // Payee name
        'am' => number_format($order['total_amount'], 2, '.', ''),
        'cu' => 'INR',
        'tn' => 'Order ' . $order['order_number'],
        'tr' => $order['order_number']
    ];

    $upiLink = 'upi://pay?' . http_build_query($upiParams);

    // QR code URL (using qrserver.com - free API)
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($upiLink);

    success('UPI QR generated', [
        'upi_link'     => $upiLink,
        'qr_code_url'  => $qrUrl,
        'amount'       => (float)$order['total_amount'],
        'order_number' => $order['order_number']
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}