<?php
/**
 * Update Order Status (Admin)
 * POST: order_id, status, tracking_number, courier_name
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$adminId = getCurrentUserId();

$orderId = (int)($input['order_id'] ?? 0);
$status = $input['status'] ?? '';
$tracking = clean($input['tracking_number'] ?? '');
$courier = clean($input['courier_name'] ?? '');

if (!$orderId) error('Order ID required');
if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])) {
    error('Invalid status');
}

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT order_number, user_id FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) error('Order not found', 404);

    // Update fields
    $updates = ["status = ?"];
    $params = [$status];

    if ($status === 'shipped') {
        $updates[] = "shipped_at = NOW()";
        if ($tracking) {
            $updates[] = "tracking_number = ?";
            $params[] = $tracking;
        }
        if ($courier) {
            $updates[] = "courier_name = ?";
            $params[] = $courier;
        }
    }

    if ($status === 'delivered') {
        $updates[] = "delivered_at = NOW()";
    }

    if ($status === 'cancelled') {
        $updates[] = "cancelled_at = NOW()";
    }

    $params[] = $orderId;

    $pdo->prepare("UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?")
        ->execute($params);

    // Notify user
    $statusMsg = [
        'processing' => 'being processed',
        'shipped'    => 'shipped',
        'delivered'  => 'delivered',
        'cancelled'  => 'cancelled'
    ][$status] ?? 'updated';

    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'order', ?, ?, '/my-orders')
    ")->execute([
        $order['user_id'],
        'Order Update',
        "Your order #{$order['order_number']} has been $statusMsg"
    ]);

    logActivity($adminId, 'admin_update_order', "Updated order #$orderId to $status", 'admin');

    success('Order status updated');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}