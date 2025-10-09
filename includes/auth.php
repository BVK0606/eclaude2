<?php
// includes/auth.php
// Simple authentication and role helpers

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config.php';
}

require_once __DIR__ . '/db.php';
session_start(); // Ensure session is started

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Require login, redirect to login page if not
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit;
    }

    // Optional session timeout
    if (defined('SESSION_TIMEOUT') && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            header('Location: ../auth/login.php?timeout=1');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

// Check if user has required role(s)
function requireRole($roles) {
    if (!isLoggedIn()) {
        requireAuth();
    }

    if (is_string($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['role'], $roles)) {
        header('Location: ../index.php');
        exit;
    }
}

// Attempt login and return user data or false
function attemptLogin($usernameOrEmail, $password) {
    global $db;

    $stmt = $db->prepare("SELECT id, uname, upassword, role, email FROM users WHERE uname = ? OR email = ? LIMIT 1");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['upassword'])) {
        // Save session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['uname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['last_activity'] = time();
        return $user;
    }

    return false;
}
?>