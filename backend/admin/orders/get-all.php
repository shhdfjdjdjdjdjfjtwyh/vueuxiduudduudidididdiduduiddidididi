<?php
/**
 * Get All Orders (Admin)
 * GET: ?page=1&status=&payment_status=&search=
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';
$search = trim($_GET['search'] ?? '');

try {
    $pdo = db();

    $where = "WHERE 1=1";
    $params = [];

    if ($status) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }

    if ($paymentStatus) {
        $where .= " AND o.payment_status = ?";
        $params[] = $paymentStatus;
    }

    if ($search) {
        $where .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like);
    }

    // Total
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM orders o 
        JOIN users u ON u.id = o.user_id 
        $where
    ");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Orders
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email, u.full_name
        FROM orders o
        JOIN users u ON u.id = o.user_id
        $where
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    foreach ($orders as &$o) {
        $o['total_amount'] = (float)$o['total_amount'];
    }

    success('Orders loaded', [
        'orders' => $orders,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}