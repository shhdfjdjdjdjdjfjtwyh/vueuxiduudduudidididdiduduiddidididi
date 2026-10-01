<?php
/**
 * Get Agent's Users
 * GET: ?page=1&search=
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
$search = trim($_GET['search'] ?? '');

try {
    $pdo = db();

    $where = "WHERE u.agent_id = ?";
    $params = [$agentId];

    if ($search) {
        $where .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like);
    }

    // Total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users u $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Users
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.phone, u.full_name, u.avatar,
               u.balance, u.total_spent, u.status, u.last_login, u.created_at,
               (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders
        FROM users u
        $where
        ORDER BY u.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    foreach ($users as &$u) {
        $u['balance'] = (float)$u['balance'];
        $u['total_spent'] = (float)$u['total_spent'];
        $u['total_orders'] = (int)$u['total_orders'];
    }

    success('Users loaded', [
        'users' => $users,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}