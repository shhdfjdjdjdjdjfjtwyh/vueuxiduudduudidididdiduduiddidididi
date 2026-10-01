<?php
/**
 * Get Agent's Orders
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

$agentId = getCurrentAgentId();
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? '';

try {
    $pdo = db();

    $where = "WHERE o.agent_id = ?";
    $params = [$agentId];

    if ($status) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.full_name, u.email
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