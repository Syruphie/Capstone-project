<?php
/**
 * Quick DB connectivity check — uses the same config as the application.
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Testing database connection...<br>";

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connection successful!<br>";

    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $count = $stmt->fetchColumn();
    echo "✅ Users table exists with {$count} users<br>";

    $stmt = $pdo->query("SELECT * FROM users WHERE email = 'admin@globentech.com'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo '✅ Test user found: ' . htmlspecialchars((string) ($user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br>';
    } else {
        echo '❌ Test user NOT found - database may not be imported correctly<br>';
    }
} catch (Throwable $e) {
    echo '❌ Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
