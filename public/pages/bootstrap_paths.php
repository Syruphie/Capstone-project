<?php
declare(strict_types=1);

/**
 * Single entry for project-root resolution from any routable page under public/pages/
 * (including nested folders). Include this first, then use CAPSTONE_PROJECT_ROOT for
 * includes/, src/, etc.
 */
if (!defined('CAPSTONE_PROJECT_ROOT')) {
    define('CAPSTONE_PROJECT_ROOT', dirname(__DIR__, 2));
}

/** Shared layout + page handlers live under public/pages (not includes/). */
if (!defined('PAGE_PARTIALS')) {
    define('PAGE_PARTIALS', CAPSTONE_PROJECT_ROOT . '/public/pages/_partials');
}
if (!defined('PAGE_HANDLERS')) {
    define('PAGE_HANDLERS', CAPSTONE_PROJECT_ROOT . '/public/pages/_handlers');
}
if (!defined('PAGE_DATA')) {
    define('PAGE_DATA', CAPSTONE_PROJECT_ROOT . '/public/pages/_data');
}

require_once CAPSTONE_PROJECT_ROOT . '/src/classes/Frontend/bootstrap.php';
