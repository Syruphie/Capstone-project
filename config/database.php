<?php
declare(strict_types=1);

// Database configuration (Laragon default: root / no password — change DB_PASS if your MySQL root has a password)
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'globentech_db';

// Application configuration
const APP_NAME = 'GlobenTech';
const BASE_URL = 'http://localhost:8000';

// Mail configuration
const MAIL_USE_SMTP = true;
const MAIL_SMTP_HOST = '127.0.0.1';
const MAIL_SMTP_PORT = 1025;
const SUPPORT_EMAIL = 'support@globentech.com';

/**
 * Class Database
 *
 * Provides access to the application database connection.
 *
 * Responsibilities:
 * - Lazily create a single PDO connection for the application
 * - Centralize PDO configuration
 * - Expose the active database connection
 *
 * Non-Responsibilities:
 * - No session bootstrapping
 * - No request/application initialization
 * - No business logic
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed.', 0, $e);
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}