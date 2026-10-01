<?php
/**
 * Logout
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

$userId = getCurrentUserId();

if ($userId) {
    try {
        logActivity($userId, 'logout', 'User logged out');
    } catch (Exception $e) {}
}

destroySession();
success('Logged out successfully');