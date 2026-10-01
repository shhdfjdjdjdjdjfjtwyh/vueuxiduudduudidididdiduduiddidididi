<?php
/**
 * Create Agent (Admin)
 * POST: username, email, password, phone, business_name, commission_rate, parent_agent_id
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$adminId = getCurrentUserId();

$username = clean($input['username'] ?? '');
$email = clean($input['email'] ?? '');
$password = $input['password'] ?? '';
$phone = clean($input['phone'] ?? '');
$businessName = clean($input['business_name'] ?? '');
$commission = (float)($input['commission_rate'] ?? 5);
$parentAgentId = (int)($input['parent_agent_id'] ?? 0);

if (!$username || !$email || !$password) error('Username, email, password required');
if (!validateEmail($email)) error('Invalid email');
if (strlen($password) < 6) error('Password 6+ chars');

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Check existing
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        error('Username or email already registered');
    }

    // Create user account for agent
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, phone, role, status)
        VALUES (?, ?, ?, ?, ?, 'agent', 'active')
    ");
    $stmt->execute([$username, $email, hashPassword($password), $businessName ?: $username, $phone ?: null]);

    $userId = $pdo->lastInsertId();
    $agentCode = 'AGT' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Create agent record
    $stmt = $pdo->prepare("
        INSERT INTO agents (user_id, agent_code, business_name, commission_rate, parent_agent_id, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$userId, $agentCode, $businessName, $commission, $parentAgentId ?: null]);

    $agentId = $pdo->lastInsertId();

    $pdo->commit();

    logActivity($adminId, 'admin_create_agent', "Created agent: $username", 'admin');

    success('Agent created', [
        'agent_id' => $agentId,
        'agent_code' => $agentCode
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}