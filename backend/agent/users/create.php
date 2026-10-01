<?php
/**
 * Create User (Agent)
 * POST: username, email, password, full_name, phone, initial_balance
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$agentId = getCurrentAgentId();

$username = clean($input['username'] ?? '');
$email = clean($input['email'] ?? '');
$password = $input['password'] ?? '';
$fullName = clean($input['full_name'] ?? '');
$phone = clean($input['phone'] ?? '');
$initialBalance = (float)($input['initial_balance'] ?? 0);

if (!$username || !$email || !$password) error('Required fields missing');
if (!validateEmail($email)) error('Invalid email');
if (strlen($password) < 6) error('Password 6+ chars');

try {
    $pdo = db();

    // Check agent permissions
    $stmt = $pdo->prepare("SELECT can_create_users FROM agents WHERE id = ?");
    $stmt->execute([$agentId]);
    if (!$stmt->fetchColumn()) error('Not authorized to create users', 403);

    $pdo->beginTransaction();

    // Check existing
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        error('Username or email already exists');
    }

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, phone, agent_id, balance, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'user', 'active')
    ");
    $stmt->execute([
        $username, $email, hashPassword($password), $fullName ?: $username,
        $phone ?: null, $agentId, $initialBalance
    ]);

    $userId = $pdo->lastInsertId();

    // If initial balance, log it
    if ($initialBalance > 0) {
        $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, balance_after, description)
            VALUES (?, 'admin_credit', ?, ?, 'Initial balance by agent')
        ")->execute([$userId, $initialBalance, $initialBalance]);
    }

    // Update agent user count
    $pdo->prepare("UPDATE agents SET total_users = total_users + 1 WHERE id = ?")
        ->execute([$agentId]);

    $pdo->commit();

    logActivity($agentId, 'agent_create_user', "Created user: $username", 'agent');

    success('User created successfully', [
        'user_id' => $userId,
        'username' => $username
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}