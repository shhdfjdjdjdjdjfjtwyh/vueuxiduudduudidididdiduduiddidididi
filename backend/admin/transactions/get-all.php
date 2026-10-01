<?php
/**
 * Get All Transactions (Admin)
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;
$type = $_GET['type'] ?? '';
$userId = (int)($_GET['user_id'] ?? 0);

try {
    $pdo = db();

    $where = "WHERE 1=1";
    $params = [];

    if ($type) {
        $where .= " AND t.type = ?";
        $params[] = $type;
    }

    if ($userId) {
        $where .= " AND t.user_id = ?";
        $params[] = $userId;
    }

    // Total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Transactions
    $stmt = $pdo->prepare("
        SELECT t.*, u.username, u.email
        FROM transactions t
        LEFT JOIN users u ON u.id = t.user_id
        $where
        ORDER BY t.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    foreach ($transactions as &$t) {
        $t['amount'] = (float)$t['amount'];
        $t['balance_before'] = $t['balance_before'] ? (float)$t['balance_before'] : null;
        $t['balance_after'] = $t['balance_after'] ? (float)$t['balance_after'] : null;
    }

    success('Transactions loaded', [
        'transactions' => $transactions,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}