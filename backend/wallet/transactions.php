<?php
/**
 * Get wallet transactions
 * GET: ?page=1&type=deposit|purchase|commission
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$type = $_GET['type'] ?? '';

try {
    $pdo = db();

    $where = "WHERE user_id = ?";
    $params = [$myId];

    if ($type) {
        $where .= " AND type = ?";
        $params[] = $type;
    }

    // Total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Transactions
    $stmt = $pdo->prepare("
        SELECT * FROM transactions
        $where
        ORDER BY created_at DESC
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