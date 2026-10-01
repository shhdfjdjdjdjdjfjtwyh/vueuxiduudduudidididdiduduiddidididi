<?php
/**
 * Check Agent Session
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

startSession();

if (!isLoggedIn() || !isAgent()) {
    error('Agent not authenticated', 401);
}

$agentId = getCurrentAgentId();

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT a.*, u.username, u.email, u.full_name, u.avatar, u.phone
        FROM agents a
        JOIN users u ON u.id = a.user_id
        WHERE a.id = ? AND a.status = 'active'
    ");
    $stmt->execute([$agentId]);
    $agent = $stmt->fetch();

    if (!$agent) {
        destroySession();
        error('Agent not found', 401);
    }

    success('Agent session active', ['agent' => $agent]);

} catch (Exception $e) {
    error('Failed', 500);
}