<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'globentech_db');

// Application configuration
define('APP_NAME', 'GlobenTech');
define('BASE_URL', 'http://localhost:8000');
define('ASSET_VERSION', '20260401c');

// Mail configuration (for Mailpit/similar local SMTP)
define('MAIL_USE_SMTP', true);
define('MAIL_SMTP_HOST', '127.0.0.1');
define('MAIL_SMTP_PORT', 1025);
// General support inbox for contact-us messages
define('SUPPORT_EMAIL', 'support@globentech.com');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Database connection class
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
