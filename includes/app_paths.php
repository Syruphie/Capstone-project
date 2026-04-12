<?php
declare(strict_types=1);

/**
 * Web URL path prefix for the application (e.g. "/Capstone-project" or "").
 * Ensures relative href/src/fetch URLs resolve correctly when PHP is executed
 * from /public/pages/*.php or when the site lives in a subdirectory.
 */
function app_web_base(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $script = str_replace('\\', '/', (string) $script);

    // Some Windows stacks set SCRIPT_NAME to a filesystem path; occasionally with a bogus
    // leading slash: "/C:/laragon/.../public/pages/....php". That still "starts with /" but
    // must not be parsed as a URL path or the captured base becomes "C:/laragon/...".
    $scriptLooksLikeUrlPath = str_starts_with($script, '/')
        && !preg_match('#^/[A-Za-z]:/#', $script);

    if ($scriptLooksLikeUrlPath && preg_match('#^(.+)/public/pages/.+\.php$#', $script, $m)) {
        $cached = rtrim($m[1], '/');
        return $cached;
    }

    // Prefer SCRIPT_FILENAME vs DOCUMENT_ROOT when SCRIPT_NAME is a filesystem path or odd
    $filename = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $docRoot = str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($filename !== '' && $docRoot !== '' && strpos($filename, $docRoot) === 0) {
        $relFromDoc = str_replace('\\', '/', substr($filename, strlen($docRoot)));
        if (preg_match('#^(.+)/public/pages/.+\.php$#', $relFromDoc, $m)) {
            $cached = rtrim($m[1], '/');
            return $cached;
        }
        // Web root = document root: path is /public/pages/... (no subfolder). Nginx may still set
        // SCRIPT_NAME to the public URL (/auth/login.php), which would break dirname() below.
        if (preg_match('#^/?public/pages/.+\.php$#', $relFromDoc)) {
            $cached = '';
            return $cached;
        }
    }

    $dir = dirname($script);
    // If SCRIPT_NAME was a Windows file path, dirname() is still on disk (e.g. C:/...).
    // Never use that as a web base.
    if (preg_match('#^[A-Za-z]:#', $dir)) {
        $cached = '';
        return $cached;
    }
    if ($dir === '/' || $dir === '.' || $dir === '') {
        $cached = '';
        return $cached;
    }

    $cached = rtrim($dir, '/');
    return $cached;
}

/**
 * Root-relative URL to a file under the project web root (css/, login.php, etc.).
 * Prefer this for navigation and assets so links work even if &lt;base href&gt; is wrong.
 */
function app_path(string $relative): string
{
    $rel = ltrim(str_replace('\\', '/', $relative), '/');
    $base = app_web_base();

    if ($base === '') {
        return '/' . $rel;
    }

    $base = '/' . ltrim($base, '/');

    return $base . '/' . $rel;
}

/**
 * Directory URL for HTML <base href> (always ends with /).
 */
function app_base_href(): string
{
    $b = app_web_base();
    if ($b === '') {
        return '/';
    }

    return '/' . ltrim($b, '/') . '/';
}
