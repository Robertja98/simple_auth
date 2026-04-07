<?php
/**
 * Authentication Middleware
 * 
 * Include this file at the top of any protected page
 * Usage: require_once __DIR__ . '/middleware.php';
 */

require_once __DIR__ . '/Auth.php';

// Load configuration
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    die('Authentication configuration not found. Please set up auth/config.php');
}

$config = require $configFile;

// Initialize auth
$auth = new Auth($config);

// Check if user is authenticated
if (!$auth->isAuthenticated()) {
    echo '<div style="background:#fee;border:2px solid #c33;padding:10px;margin:10px 0;">';
    echo '<strong>Access Denied:</strong> You must be logged in to view this page.<br>';
    echo '<b>DEBUG (middleware.php):</b><br>';
    echo 'Session: <pre>' . print_r($_SESSION, true) . '</pre>';
    echo 'session_status: ' . session_status() . ' (1=none, 2=active, 3=disabled)<br>';
    echo 'session_id: ' . session_id() . '<br>';
    echo 'Cookies: <pre>' . print_r($_COOKIE, true) . '</pre>';
    // Step-by-step check
    if (!isset($_SESSION['user_id'])) {
        echo '<b>Reason:</b> $_SESSION["user_id"] is not set.<br>';
    } else if (!isset($_SESSION['session_token'])) {
        echo '<b>Reason:</b> $_SESSION["session_token"] is not set.<br>';
    } else {
        // Try to fetch session from DB
        $token = trim((string)$_SESSION['session_token']);
        $uid = (int)$_SESSION['user_id'];
        $sessionRow = (new SessionDataStore())->fetchOne($token, $uid);
        echo '<b>DB sessionRow:</b> <pre>' . print_r($sessionRow, true) . '</pre>';
        if (!$sessionRow) {
            echo '<b>Reason:</b> No matching session found in database for this token/user.<br>';
        } else if (isset($sessionRow['expires_at']) && strtotime($sessionRow['expires_at']) <= time()) {
            echo '<b>Reason:</b> Session found but expired.<br>';
        } else {
            echo '<b>Reason:</b> Unknown authentication failure.<br>';
        }
    }
    echo '</div>';
    exit;
}

// Make auth and current user available globally
$GLOBALS['auth'] = $auth;
$GLOBALS['current_user'] = $auth->getCurrentUser();

/**
 * Helper function to get current authenticated user
 */
function auth_current_user() {
    return $GLOBALS['current_user'] ?? null;
}

/**
 * Helper function to check if user is authenticated
 */
function auth_check() {
    return isset($GLOBALS['current_user']) && $GLOBALS['current_user'] !== null;
}

/**
 * Helper function to get auth instance
 */
function auth() {
    return $GLOBALS['auth'] ?? null;
}
