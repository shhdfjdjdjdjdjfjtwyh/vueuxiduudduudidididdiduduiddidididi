<?php
/**
 * ShopVault - Backend Constants
 */

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'shopvault_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site
define('SITE_URL', 'http://localhost/shopvault');
define('SITE_NAME', 'ShopVault');
define('ADMIN_EMAIL', 'admin@shopvault.in');

// Paths
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');
define('PRODUCT_PATH', UPLOAD_PATH . 'products/');
define('USER_PATH', UPLOAD_PATH . 'users/');
define('AGENT_PATH', UPLOAD_PATH . 'agents/');
define('REVIEW_PATH', UPLOAD_PATH . 'reviews/');

// Upload limits
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);    // 5 MB
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024);   // 50 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Session
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days

// Security
define('HASH_COST', 10);
define('JWT_SECRET', 'CHANGE_THIS_TO_RANDOM_STRING');

// Pagination
define('DEFAULT_LIMIT', 20);
define('MAX_LIMIT', 100);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);