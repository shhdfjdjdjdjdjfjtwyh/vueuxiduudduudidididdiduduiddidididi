<?php
/**
 * Get All Users (Admin)
 * GET: ?page=1&search=&status=&role=
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$role = $_GET['role'] ?? 'user';

try {
    $pdo = db();

    $where = "WHERE role = ?";
    $params = [$role];

    if ($search !== '') {
        $where .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ? OR phone LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like);
    }

    if ($status !== '') {
        $where .= " AND status = ?";
        $params[] = $status;
    }

    // Total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Users
    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, full_name, avatar, balance, 
               status, email_verified, last_login, created_at,
               (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders,
               (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id = users.id AND payment_status = 'paid') as total_spent
        FROM users
        $where
        ORDER BY created_at DESC
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
        'page'  => $page,
        'pages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}