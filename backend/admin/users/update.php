<?php
/**
 * Update User Details (Admin)
 * POST: user_id, full_name, email, phone, status, password (optional)
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

$userId    = (int)($input['user_id'] ?? 0);
$fullName  = clean($input['full_name'] ?? '');
$email     = clean($input['email'] ?? '');
$phone     = clean($input['phone'] ?? '');
$status    = $input['status'] ?? 'active';
$password  = $input['password'] ?? '';

if (!$userId) error('User ID required');
if (!empty($email) && !validateEmail($email)) error('Invalid email');
if (!empty($phone) && !validatePhone($phone)) error('Invalid phone');
if (!in_array($status, ['active', 'banned', 'pending'])) error('Invalid status');

try {
    $pdo = db();

    // Check unique email/phone
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) error('Email already exists');
    }

    if ($phone) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $userId]);
        if ($stmt->fetch()) error('Phone already exists');
    }

    // Build update
    $updates = ["full_name = ?", "status = ?"];
    $params = [$fullName, $status];

    if ($email) {
        $updates[] = "email = ?";
        $params[] = $email;
    }
    if ($phone) {
        $updates[] = "phone = ?";
        $params[] = $phone;
    }
    if (!empty($password)) {
        if (strlen($password) < 6) error('Password must be 6+ chars');
        $updates[] = "password = ?";
        $params[] = hashPassword($password);
    }

    $params[] = $userId;

    $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?")
        ->execute($params);

    logActivity($adminId, 'admin_update_user', "Updated user #$userId", 'admin');

    success('User updated successfully');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}