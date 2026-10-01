<?php
/**
 * Agent Auth Middleware
 */

require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/response.php';

if (!isLoggedIn() || !isAgent()) {
    error('Agent access required', 403);
}