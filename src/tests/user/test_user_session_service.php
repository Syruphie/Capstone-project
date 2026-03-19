<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

require_once __DIR__ . '/../../classes/User/Service/UserSessionService.php';
require_once __DIR__ . '/../../classes/User/Entity/User.php';

printSection('UserSessionService');

$_SESSION = [];

$service = new UserSessionService();
$user = new User(
    123,
    'Session User',
    'session_user@example.com',
    '',
    null,
    null,
    null,
    'customer',
    true
);

$service->storeUserSession($user);

assertTrue($service->isLoggedIn(), 'isLoggedIn returns true after storing session');
printPass('isLoggedIn returns true after storing session');

assertSame(123, $service->getCurrentUserId(), 'getCurrentUserId returns stored id');
printPass('getCurrentUserId returns stored id');

assertSame('customer', $service->getCurrentUserRole(), 'getCurrentUserRole returns stored role');
printPass('getCurrentUserRole returns stored role');

assertSame('Session User', $service->getCurrentUserName(), 'getCurrentUserName returns stored name');
printPass('getCurrentUserName returns stored name');

assertTrue($service->hasRole('customer'), 'hasRole returns true for matching role');
printPass('hasRole returns true for matching role');

assertFalse($service->hasRole('administrator'), 'hasRole returns false for non-matching role');
printPass('hasRole returns false for non-matching role');

$_SESSION = [];

assertFalse($service->isLoggedIn(), 'isLoggedIn returns false when session is empty');
printPass('isLoggedIn returns false when session is empty');

assertNull($service->getCurrentUserId(), 'getCurrentUserId returns null when session is empty');
printPass('getCurrentUserId returns null when session is empty');

assertNull($service->getCurrentUserRole(), 'getCurrentUserRole returns null when session is empty');
printPass('getCurrentUserRole returns null when session is empty');

assertNull($service->getCurrentUserName(), 'getCurrentUserName returns null when session is empty');
printPass('getCurrentUserName returns null when session is empty');

echo "\nUserSessionService tests passed.\n";