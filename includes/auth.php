<?php
// includes/auth.php
// Session and role helpers. This is intentionally lightweight because most helpers live in config.php.

if (!defined('APP_NAME')) {
	require_once __DIR__ . '/../config.php';
}

require_once __DIR__ . '/db.php';

// Check if user is logged in
function isLoggedIn() {
	return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Require authentication, redirect to login if not
function requireAuth() {
	if (!isLoggedIn()) {
		header('Location: /eclaude2/auth/login.php');
		exit;
	}

	// Session timeout handled in config.php requireAuth if present
	if (defined('SESSION_TIMEOUT') && isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
		session_unset();
		session_destroy();
		header('Location: /eclaude2/auth/login.php?timeout=1');
		exit;
	}

	$_SESSION['last_activity'] = time();
}

// Simple role guard
function requireRole($roles) {
	if (!isLoggedIn()) {
		requireAuth();
	}

	if (is_string($roles)) {
		$roles = [$roles];
	}

	if (!in_array($_SESSION['role'], $roles)) {
		header('Location: /eclaude2/index.php');
		exit;
	}
}

// Minimal login helper used by auth/login.php (returns user array or false)
function attemptLogin($usernameOrEmail, $password) {
	global $db;

	$stmt = $db->prepare("SELECT id, uname, upassword, role, email FROM users WHERE uname = ? OR email = ? LIMIT 1");
	$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
	$user = $stmt->fetch();

	if ($user && password_verify($password, $user['upassword'])) {
		return $user;
	}

	return false;
}

?>
