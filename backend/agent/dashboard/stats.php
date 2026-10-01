<?php
/**
 * Agent Dashboard Stats
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

$agentId = getCurrentAgentId();

try {
    $pdo = db();

    $stats = [];

    // Agent info
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE id = ?");
    $stmt->execute([$agentId]);
    $agent = $stmt->fetch();

    $stats['wallet_balance'] = (float)$agent['wallet_balance'];
    $stats['total_earned'] = (float)$agent['total_earned'];
    $stats['commission_rate'] = (float)$agent['commission_rate'];

    // My users count
    $stats['my_users'] = (int)$pdo->query("
        SELECT COUNT(*) FROM users WHERE agent_id = $agentId
    ")->fetchColumn();

    // Today's new users
    $stats['today_users'] = (int)$pdo->query("
        SELECT COUNT(*) FROM users 
        WHERE agent_id = $agentId AND DATE(created_at) = CURDATE()
    ")->fetchColumn();

    // My orders
    $stats['my_orders'] = (int)$pdo->query("
        SELECT COUNT(*) FROM orders WHERE agent_id = $agentId
    ")->fetchColumn();

    // My revenue
    $stats['my_revenue'] = (float)$pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) FROM orders 
        WHERE agent_id = $agentId AND payment_status = 'paid'
    ")->fetchColumn();

    // This month's commission
    $stats['month_commission'] = (float)$pdo->query("
        SELECT COALESCE(SUM(amount), 0) FROM transactions 
        WHERE agent_id = $agentId AND type = 'commission'
          AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ")->fetchColumn();

    // Recent users
    $stmt = $pdo->prepare("
        SELECT id, username, email, full_name, avatar, balance, status, created_at
        FROM users WHERE agent_id = ?
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$agentId]);
    $stats['recent_users'] = $stmt->fetchAll();

    // Recent commission transactions
    $stmt = $pdo->prepare("
        SELECT id, amount, description, created_at
        FROM transactions 
        WHERE agent_id = ? AND type = 'commission'
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$agentId]);
    $stats['recent_commissions'] = $stmt->fetchAll();

    success('Agent stats', ['stats' => $stats]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}