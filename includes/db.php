<?php
// includes/db.php
// Provides a Database wrapper and a $db PDO connection for includes.

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config.php';
}

// If Database class exists (from config.php) use it, otherwise create a minimal one
if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $connection;

        private function __construct() {
            try {
                $this->connection = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                    DB_USERNAME,
                    DB_PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                // For development show error; in production consider logging and a generic message
                die('Database connection failed: ' . $e->getMessage());
            }
        }

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function getConnection() {
            return $this->connection;
        }
    }
}

// Expose $db variable for includes
$db = Database::getInstance()->getConnection();

?>
