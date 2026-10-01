<?php
/**
 * Agent Earnings Summary
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

$agentId = getCurrentAgentId();

try {
    $pdo = db();

    // Wallet balance
    $stmt = $pdo->prepare("SELECT wallet_balance, total_earned, commission_rate FROM agents WHERE id = ?");
    $stmt->execute([$agentId]);
    $agent = $stmt->fetch();

    $summary = [
        'wallet_balance' => (float)$agent['wallet_balance'],
        'total_earned' => (float)$agent['total_earned'],
        'commission_rate' => (float)$agent['commission_rate'],
        'today' => 0,
        'week' => 0,
        'month' => 0
    ];

    // Today
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM transactions 
        WHERE agent_id = ? AND type = 'commission' AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$agentId]);
    $summary['today'] = (float)$stmt->fetchColumn();

    // Week
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM transactions 
        WHERE agent_id = ? AND type = 'commission' 
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$agentId]);
    $summary['week'] = (float)$stmt->fetchColumn();

    // Month
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) FROM transactions 
        WHERE agent_id = ? AND type = 'commission' 
          AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $stmt->execute([$agentId]);
    $summary['month'] = (float)$stmt->fetchColumn();

    success('Earnings summary', ['summary' => $summary]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}