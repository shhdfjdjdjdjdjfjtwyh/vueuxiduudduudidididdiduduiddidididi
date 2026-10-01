<?php
/**
 * Agent Login
 * POST: email/username, password
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$identifier = clean($input['identifier'] ?? '');
$password = $input['password'] ?? '';

if (empty($identifier) || empty($password)) error('Credentials required');

try {
    $pdo = db();

    // Find agent user
    $stmt = $pdo->prepare("
        SELECT u.*, a.id as agent_id, a.agent_code, a.business_name, 
               a.commission_rate, a.wallet_balance, a.total_earned, a.parent_agent_id,
               a.can_create_users, a.can_recharge, a.can_manage_products, a.can_view_orders
        FROM users u
        JOIN agents a ON a.user_id = u.id
        WHERE (u.email = ? OR u.username = ?) AND u.role = 'agent' AND u.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$identifier, $identifier]);
    $agent = $stmt->fetch();

    if (!$agent) error('Invalid agent credentials', 401);
    if (!verifyPassword($password, $agent['password'])) error('Invalid credentials', 401);

    unset($agent['password']);

    // Set agent session
    setAgentSession([
        'id' => $agent['agent_id'],
        'agent_code' => $agent['agent_code']
    ], $agent);

    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
        ->execute([$agent['id']]);

    logActivity($agent['id'], 'agent_login', 'Agent logged in', 'agent');

    success('Agent logged in', ['agent' => $agent]);

} catch (Exception $e) {
    error('Login failed: ' . $e->getMessage(), 500);
}