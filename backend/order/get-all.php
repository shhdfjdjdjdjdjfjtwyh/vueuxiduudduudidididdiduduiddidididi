<?php
/**
 * Get User Orders
 * GET: ?page=1&status=
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? '';

try {
    $pdo = db();

    $where = "WHERE user_id = ?";
    $params = [$myId];

    if ($status) {
        $where .= " AND status = ?";
        $params[] = $status;
    }

    // Total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Orders
    $stmt = $pdo->prepare("
        SELECT id, order_number, total_items, total_amount, 
               payment_method, payment_status, status,
               created_at, delivered_at
        FROM orders
        $where
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Get items for each
    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("
            SELECT id, product_id, product_name, product_image, quantity, price, total
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
        $order['total_amount'] = (float)$order['total_amount'];
    }

    success('Orders loaded', [
        'orders' => $orders,
        'total'  => $total,
        'page'   => $page,
        'pages'  => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}