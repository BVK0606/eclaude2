<?php
// Start session
if (session_status() == PHP_SESSION_NONE) session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'edutrace');

// App constants
define('APP_NAME', 'Edutrace');
define('SESSION_TIMEOUT', 3600); // 1 hour

// MySQLi connection (you can use $conn in simple files)
$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) die("Database connection failed: " . mysqli_connect_error());

// PDO Database class
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Require login
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: auth/login.php');
        exit;
    }
    // Simple timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: auth/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// Restrict page by role
function requireRole($allowedRoles) {
    requireAuth();
    if (is_string($allowedRoles)) $allowedRoles = [$allowedRoles];
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: ../index.php');
        exit;
    }
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Simple alert
function showAlert($message, $type = 'info') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>