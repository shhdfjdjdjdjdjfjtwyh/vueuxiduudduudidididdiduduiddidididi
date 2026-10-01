<?php
/**
 * Google OAuth Login
 * POST: credential (Google ID token)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$credential = $input['credential'] ?? '';

if (empty($credential)) error('Missing Google credential');

// Verify Google token
$verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $credential;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$google = json_decode($response, true);

if (!$google || !isset($google['email'])) {
    error('Invalid Google token', 401);
}

if (isset($google['aud']) && $google['aud'] !== GOOGLE_CLIENT_ID) {
    error('Invalid Google client', 401);
}

$email     = $google['email'];
$name      = $google['name'] ?? '';
$googleId  = $google['sub'];
$picture   = $google['picture'] ?? '';

try {
    $pdo = db();

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR google_id = ? LIMIT 1");
    $stmt->execute([$email, $googleId]);
    $user = $stmt->fetch();

    if ($user) {
        // Existing user - login
        if ($user['status'] === 'banned') error('Account banned', 403);

        // Update google_id if not set
        if (!$user['google_id']) {
            $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?")
                ->execute([$googleId, $user['id']]);
        }

        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
            ->execute([$user['id']]);

        unset($user['password']);
        setUserSession($user);
        
    } else {
        // New user - signup
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
        
        // Ensure unique username
        $baseUsername = $username;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if (!$stmt->fetch()) break;
            $username = $baseUsername . $counter++;
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, google_id, avatar, status, email_verified)
            VALUES (?, ?, ?, ?, ?, ?, 'active', 1)
        ");
        $stmt->execute([
            $username,
            $email,
            hashPassword(bin2hex(random_bytes(16))), // Random password
            $name ?: $username,
            $googleId,
            $picture
        ]);

        $userId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        unset($user['password']);
        
        setUserSession($user);
        logActivity($userId, 'signup_google', 'Signed up via Google');
    }

    success('Google login successful!', ['user' => $user]);

} catch (Exception $e) {
    error('Google login failed: ' . $e->getMessage(), 500);
}