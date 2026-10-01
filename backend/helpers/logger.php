<?php
/**
 * Activity Logger
 */

function logActivity($userId, $action, $details = null, $userType = 'user') {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, user_type, action, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $userType,
            $action,
            is_array($details) ? json_encode($details) : $details,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log('Activity log failed: ' . $e->getMessage());
    }
}