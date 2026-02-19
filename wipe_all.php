<?php
require_once __DIR__ . '/Auth.php';

// Load config
$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    die('Configuration file not found. Please create auth/config.php from auth/config.example.php');
}
$config = require $configFile;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth($config);
$error = null; // Always initialize $error

// ...existing code...

$auth->wipeAllUsersAndSessions();
echo "All users and sessions wiped. You may now test a clean login/registration.";
