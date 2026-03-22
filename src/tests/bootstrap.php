<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/* Queue */
require_once __DIR__ . '/../classes/Queue/Entity/QueueEntry.php';
require_once __DIR__ . '/../classes/Queue/Support/QueueType.php';
require_once __DIR__ . '/../classes/Queue/Support/QueueMapper.php';
require_once __DIR__ . '/../classes/Queue/Repository/QueueRepository.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueuePositionService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueSchedulingService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueProcessingService.php';
require_once __DIR__ . '/../classes/Queue/Service/QueueStatisticsService.php';

/* User */
require_once __DIR__ . '/../classes/User/Entity/User.php';
require_once __DIR__ . '/../classes/User/Support/UserRole.php';
require_once __DIR__ . '/../classes/User/Support/UserMapper.php';
require_once __DIR__ . '/../classes/User/Support/PasswordVerifier.php';
require_once __DIR__ . '/../classes/User/Repository/UserRepository.php';
require_once __DIR__ . '/../classes/User/Service/UserService.php';
require_once __DIR__ . '/../classes/User/Service/AuthenticationService.php';
require_once __DIR__ . '/../classes/User/Service/PasswordService.php';
require_once __DIR__ . '/../classes/User/Service/UserRoleService.php';
require_once __DIR__ . '/../classes/User/Service/UserSessionService.php';

/* Support */
require_once __DIR__ . '/../classes/Support/DateRangeValidator.php';

/* Order */
require_once __DIR__ . '/../classes/Order/Entity/Order.php';
require_once __DIR__ . '/../classes/Order/Support/OrderStatus.php';
require_once __DIR__ . '/../classes/Order/Support/OrderPriority.php';
require_once __DIR__ . '/../classes/Order/Support/OrderMapper.php';
require_once __DIR__ . '/../classes/Order/Support/ValidateOrderStatus.php';
require_once __DIR__ . '/../classes/Order/Repository/OrderRepository.php';
require_once __DIR__ . '/../classes/Order/Service/OrderService.php';
require_once __DIR__ . '/../classes/Order/Service/OrderApprovalService.php';
require_once __DIR__ . '/../classes/Order/Service/OrderHistoryService.php';
require_once __DIR__ . '/../classes/Order/Service/OrderReportingService.php';

/* Sample */
require_once __DIR__ . '/../classes/Sample/Entity/Sample.php';
require_once __DIR__ . '/../classes/Sample/Support/SampleStatus.php';
require_once __DIR__ . '/../classes/Sample/Support/SampleType.php';
require_once __DIR__ . '/../classes/Sample/Support/SampleMapper.php';
require_once __DIR__ . '/../classes/Sample/Support/ValidateSampleStatus.php';
require_once __DIR__ . '/../classes/Sample/Support/ValidateSampleType.php';
require_once __DIR__ . '/../classes/Sample/Repository/SampleRepository.php';
require_once __DIR__ . '/../classes/Sample/Service/SampleService.php';
require_once __DIR__ . '/../classes/Sample/Service/SamplePreparationService.php';
require_once __DIR__ . '/../classes/Sample/Service/SampleTestingService.php';
require_once __DIR__ . '/../classes/Sample/Service/SampleTrackingService.php';
require_once __DIR__ . '/../classes/Sample/Service/SampleReportingService.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function getTestDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = '127.0.0.1';
    $db = 'capstone_test';
    $user = 'root';
    $pass = 'Patricks8148!';
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

function makeUserRepository(): UserRepository
{
    return new UserRepository(getTestDb());
}

function makeOrderRepository(): OrderRepository
{
    return new OrderRepository(getTestDb());
}

function makeSampleRepository(): SampleRepository
{
    return new SampleRepository(getTestDb());
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

function assertNull(mixed $value, string $message): void
{
    if ($value !== null) {
        throw new RuntimeException(
            "Assertion failed: {$message}\nActual: " . var_export($value, true)
        );
    }
}

function assertCountSame(int $expected, array $actual, string $message): void
{
    assertSame($expected, count($actual), $message);
}

function printPass(string $message): void
{
    echo "[PASS] {$message}\n";
}

function printSection(string $title): void
{
    echo "\n=== {$title} ===\n";
}