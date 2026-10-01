<?php
/**
 * Agent Commission Transactions
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

$agentId = getCurrentAgentId();
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

try {
    $pdo = db();

    $total = (int)$pdo->query("
        SELECT COUNT(*) FROM transactions WHERE agent_id = $agentId
    ")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT t.*, o.order_number 
        FROM transactions t
        LEFT JOIN orders o ON o.id = t.order_id
        WHERE t.agent_id = ?
        ORDER BY t.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$agentId]);
    $transactions = $stmt->fetchAll();

    foreach ($transactions as &$t) {
        $t['amount'] = (float)$t['amount'];
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