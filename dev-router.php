<?php
declare(strict_types=1);

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$root = __DIR__;

// Let the built-in server handle existing files (css/js/api.php/etc.) directly.
$directFile = $root . $uriPath;
if ($uriPath !== '/' && is_file($directFile)) {
    return false;
}

if ($uriPath === '/favicon.ico') {
    http_response_code(204);
    exit;
}

$pageRoot = $root . '/public/pages';

if ($uriPath === '/' || $uriPath === '') {
    require $pageRoot . '/index.php';
    return true;
}

if (preg_match('#^/([A-Za-z0-9\-]+\.php)$#', $uriPath, $matches) === 1) {
    $pageFile = $pageRoot . '/' . $matches[1];
    if (is_file($pageFile)) {
        require $pageFile;
        return true;
    }
}

http_response_code(404);
echo 'Not Found';

