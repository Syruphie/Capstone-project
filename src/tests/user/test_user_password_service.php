<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

require_once __DIR__ . '/../../classes/User/Service/AuthenticationService.php';
require_once __DIR__ . '/../../classes/User/Repository/UserRepository.php';
require_once __DIR__ . '/../../classes/User/Entity/User.php';

printSection('AuthenticationService');

$_SESSION = [];

$repository = makeUserRepository();
$service = new AuthenticationService($repository, new UserSessionService());

$user = new User(
    null,
    'Auth User',
    'auth_user@example.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    null,
    null,
    null,
    'customer',
    true
);

$repository->createUser($user);

$loginSuccess = $service->login('auth_user@example.com', 'Password1!');
assertTrue($loginSuccess, 'login succeeds with valid credentials');
printPass('login succeeds with valid credentials');

assertTrue(isset($_SESSION['user_id']), 'login stores user_id in session');
printPass('login stores user_id in session');

assertTrue(isset($_SESSION['user_role']), 'login stores user_role in session');
printPass('login stores user_role in session');

assertTrue(isset($_SESSION['user_name']), 'login stores user_name in session');
printPass('login stores user_name in session');

assertSame('customer', $_SESSION['user_role'], 'login stores correct role');
printPass('login stores correct role');

$_SESSION = [];
$loginWrongPassword = $service->login('auth_user@example.com', 'WrongPassword1!');
assertFalse($loginWrongPassword, 'login fails with invalid password');
printPass('login fails with invalid password');

$_SESSION = [];
$loginUnknownUser = $service->login('missing@example.com', 'Password1!');
assertFalse($loginUnknownUser, 'login fails with unknown email');
printPass('login fails with unknown email');

$repository->updateActiveStatus((int)$user->getId(), false);

$_SESSION = [];
$loginInactive = $service->login('auth_user@example.com', 'Password1!');
assertFalse($loginInactive, 'login fails for inactive user');
printPass('login fails for inactive user');

$_SESSION = ['user_id' => 999, 'user_role' => 'customer', 'user_name' => 'Temp'];
$logout = $service->logout();
assertTrue($logout, 'logout returns true');
printPass('logout returns true');

echo "\nAuthenticationService tests passed.\n";