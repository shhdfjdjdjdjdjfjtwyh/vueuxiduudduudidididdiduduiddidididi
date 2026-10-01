<?php
/**
 * Get Order Details
 * GET: ?id=X
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();
$orderId = (int)($_GET['id'] ?? 0);

if (!$orderId) error('Order ID required');

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT * FROM orders WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $myId]);
    $order = $stmt->fetch();

    if (!$order) error('Order not found', 404);

    // Get items
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order['items'] = $stmt->fetchAll();

    $order['total_amount'] = (float)$order['total_amount'];

    success('Order loaded', ['order' => $order]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}