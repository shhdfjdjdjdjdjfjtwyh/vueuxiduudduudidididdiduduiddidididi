<?php
/**
 * Update Profile
 * POST: full_name, phone, bio, avatar (file)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/file-upload.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$myId = getCurrentUserId();

$fullName = clean($_POST['full_name'] ?? '');
$phone = clean($_POST['phone'] ?? '');
$bio = clean($_POST['bio'] ?? '');

try {
    $pdo = db();

    // Check phone unique
    if ($phone) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $myId]);
        if ($stmt->fetch()) error('Phone already registered');
    }

    // Avatar upload
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploader = new FileUpload(USER_PATH);
        $avatar = $uploader->upload($_FILES['avatar'], 'avatar');
        if (!$avatar) error('Avatar upload failed: ' . $uploader->firstError());
    }

    // Build query
    $updates = ["full_name = ?", "bio = ?"];
    $params = [$fullName, $bio];

    if ($phone) {
        $updates[] = "phone = ?";
        $params[] = $phone;
    }

    if ($avatar) {
        // Delete old avatar
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$myId]);
        $old = $stmt->fetchColumn();
        if ($old && file_exists(USER_PATH . $old)) @unlink(USER_PATH . $old);

        $updates[] = "avatar = ?";
        $params[] = $avatar;
    }

    $params[] = $myId;

    $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?")
        ->execute($params);

    // Fetch updated
    $stmt = $pdo->prepare("SELECT id, username, email, phone, full_name, avatar, bio, balance FROM users WHERE id = ?");
    $stmt->execute([$myId]);
    $user = $stmt->fetch();
    $user['balance'] = (float)$user['balance'];

    success('Profile updated', ['user' => $user]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}