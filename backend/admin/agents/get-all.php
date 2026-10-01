<?php
/**
 * Get All Agents (Admin)
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $pdo = db();

    $total = (int)$pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT a.*, u.username, u.email, u.full_name, u.avatar, u.phone,
               (SELECT COUNT(*) FROM users WHERE agent_id = a.id) as user_count
        FROM agents a
        JOIN users u ON u.id = a.user_id
        ORDER BY a.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute();
    $agents = $stmt->fetchAll();

    foreach ($agents as &$a) {
        $a['wallet_balance'] = (float)$a['wallet_balance'];
        $a['total_earned'] = (float)$a['total_earned'];
        $a['commission_rate'] = (float)$a['commission_rate'];
        $a['user_count'] = (int)$a['user_count'];
    }

    success('Agents loaded', [
        'agents' => $agents,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}