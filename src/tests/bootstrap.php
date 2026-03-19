<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../classes/Queue/Entity/QueueEntry.php';
require_once __DIR__ . '/../classes/Queue/Support/QueueType.php';
require_once __DIR__ . '/../classes/Queue/Support/QueueMapper.php';
require_once __DIR__ . '/../classes/Queue/Repository/QueueRepository.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueuePositionService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueSchedulingService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueProcessingService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueStatisticsService.php';
require_once __DIR__ . '/../classes/Support/DateRangeValidator.php';

function getTestDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = '127.0.0.1';
    $db = 'capstone_test';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function makeQueueRepository(): QueueRepository
{
    return new QueueRepository(getTestDb());
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException("Assertion failed: {$message}");
    }
}

function assertFalse(bool $condition, string $message): void
{
    assertTrue(!$condition, $message);
}

function assertSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            "Assertion failed: {$message}\nExpected: " . var_export($expected, true) .
            "\nActual: " . var_export($actual, true)
        );
    }
}

function assertNotNull(mixed $value, string $message): void
{
    if ($value === null) {
        throw new RuntimeException("Assertion failed: {$message}");
    }
}

function printPass(string $message): void
{
    echo "[PASS] {$message}\n";
}

function printSection(string $title): void
{
    echo "\n=== {$title} ===\n";
}