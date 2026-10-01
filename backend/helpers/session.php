<?php
/**
 * Session Management
 */

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_path', '/');
        ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);
        ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7);
        
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 60 * 60 * 24 * 7,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        
        session_name('SHOPVAULT_SID');
        session_start();
    }
}

function setUserSession($user) {
    startSession();
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'] ?? 'user';
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    session_write_close();
    session_start();
}

function setAdminSession($user) {
    startSession();
    $_SESSION['admin_id']   = (int)$user['id'];
    $_SESSION['admin_role'] = 'admin';
    $_SESSION['logged_in']  = true;
    
    session_write_close();
    session_start();
}

function setAgentSession($agent, $user) {
    startSession();
    $_SESSION['agent_id']   = (int)$agent['id'];
    $_SESSION['agent_user_id'] = (int)$user['id'];
    $_SESSION['agent_code'] = $agent['agent_code'];
    $_SESSION['role']       = 'agent';
    $_SESSION['logged_in']  = true;
    
    session_write_close();
    session_start();
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    startSession();
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function isAgent() {
    startSession();
    return isset($_SESSION['agent_id']) && !empty($_SESSION['agent_id']);
}

function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

function getCurrentAgentId() {
    startSession();
    return $_SESSION['agent_id'] ?? null;
}

function destroySession() {
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'] ?? '',
            $params['secure'] ?? false, $params['httponly'] ?? false);
    }
    session_destroy();
}

function requireLogin() {
    if (!isLoggedIn()) {
        error('Please login first', 401);
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        error('Admin access required', 403);
    }
}

function requireAgent() {
    if (!isLoggedIn() || !isAgent()) {
        error('Agent access required', 403);
    }
}