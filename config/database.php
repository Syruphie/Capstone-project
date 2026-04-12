<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap_env.php';

// Database — override with DB_* Application Settings / .env in Azure or production.
define('DB_HOST', app_env('DB_HOST', 'localhost'));
define('DB_USER', app_env('DB_USER', 'root'));
define('DB_PASS', app_env('DB_PASS', ''));
define('DB_NAME', app_env('DB_NAME', 'globentech_db'));

// Application — BASE_URL must be https://your-app.azurewebsites.net (or custom domain) in production.
define('APP_NAME', app_env('APP_NAME', 'GlobenTech'));
define('BASE_URL', app_env('BASE_URL', 'http://localhost:8000'));

// Mail — use MAIL_USE_SMTP=false only if PHP mail() is configured to relay (uncommon on Azure).
define('MAIL_USE_SMTP', app_env_bool('MAIL_USE_SMTP', true));
define('MAIL_SMTP_HOST', app_env('MAIL_SMTP_HOST', '127.0.0.1'));
define('MAIL_SMTP_PORT', (int) app_env('MAIL_SMTP_PORT', '1025'));
define('MAIL_SMTP_USER', app_env('MAIL_SMTP_USER', ''));
define('MAIL_SMTP_PASS', app_env('MAIL_SMTP_PASS', ''));
/** Empty, tls, or ssl */
define('MAIL_SMTP_ENCRYPTION', app_env('MAIL_SMTP_ENCRYPTION', ''));
define('SUPPORT_EMAIL', app_env('SUPPORT_EMAIL', 'support@globentech.com'));

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
