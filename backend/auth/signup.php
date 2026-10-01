<?php
/**
 * User Signup
 * POST: username, email, password, full_name, phone (optional)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) error('Invalid request body');

// Validate
$v = new Validator($input);
$v->required('username', 'Username')
  ->required('email', 'Email')
  ->required('password', 'Password')
  ->min('username', 3)
  ->max('username', 30)
  ->email('email')
  ->min('password', 6);

if ($v->fails()) error($v->firstError(), 422);

$username  = clean($input['username']);
$email     = clean($input['email']);
$password  = $input['password'];
$full_name = clean($input['full_name'] ?? '');
$phone     = clean($input['phone'] ?? '');

if (!validateUsername($username)) {
    error('Username can only contain letters, numbers, and underscores', 422);
}

try {
    $pdo = db();

    // Check existing
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) error('Username or email already registered', 409);

    // Check phone if provided
    if ($phone) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) error('Phone number already registered', 409);
    }

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, phone, status, email_verified)
        VALUES (?, ?, ?, ?, ?, 'active', 0)
    ");
    $stmt->execute([
        $username,
        $email,
        hashPassword($password),
        $full_name ?: $username,
        $phone ?: null
    ]);

    $userId = $pdo->lastInsertId();

    // Fetch user
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, phone, avatar, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Set session
    setUserSession($user);

    // Log activity
    logActivity($userId, 'signup', 'New user registered');

    success('Account created successfully!', ['user' => $user]);

} catch (Exception $e) {
    error('Signup failed: ' . $e->getMessage(), 500);
}