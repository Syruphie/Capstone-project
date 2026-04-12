<?php
declare(strict_types=1);

/**
 * Loads .env (local) and merges process environment (Azure App Settings, CI) into $_ENV.
 * Process env wins over .env for the same key so production overrides local files.
 */
$projectRoot = dirname(__DIR__);

if (file_exists($projectRoot . '/vendor/autoload.php')) {
    require_once $projectRoot . '/vendor/autoload.php';
}

if (class_exists(Dotenv\Dotenv::class) && file_exists($projectRoot . '/.env')) {
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

$envKeys = [
    'DB_HOST',
    'DB_USER',
    'DB_PASS',
    'DB_NAME',
    'APP_NAME',
    'BASE_URL',
    'MAIL_USE_SMTP',
    'MAIL_SMTP_HOST',
    'MAIL_SMTP_PORT',
    'MAIL_SMTP_USER',
    'MAIL_SMTP_PASS',
    'MAIL_SMTP_ENCRYPTION',
    'SUPPORT_EMAIL',
    'STRIPE_PUBLIC_KEY',
    'STRIPE_SECRET_KEY',
    'STRIPE_WEBHOOK_SECRET',
];

foreach ($envKeys as $key) {
    $val = getenv($key);
    if ($val !== false) {
        $_ENV[$key] = $val;
    }
}

if (!function_exists('app_env')) {
    /**
     * @param non-empty-string $default
     */
    function app_env(string $key, string $default = ''): string
    {
        $v = $_ENV[$key] ?? null;
        if ($v === null || $v === '') {
            return $default;
        }

        return (string) $v;
    }
}

if (!function_exists('app_env_bool')) {
    function app_env_bool(string $key, bool $default): bool
    {
        $raw = $_ENV[$key] ?? null;
        if ($raw === null || $raw === '') {
            return $default;
        }

        $filtered = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filtered ?? $default;
    }
}
