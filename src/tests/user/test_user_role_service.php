<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

require_once __DIR__ . '/../../classes/User/Service/UserRoleService.php';
require_once __DIR__ . '/../../classes/User/Repository/UserRepository.php';
require_once __DIR__ . '/../../classes/User/Entity/User.php';

printSection('UserRoleService');

$_SESSION = [];

$repository = makeUserRepository();
$sessionService = new UserSessionService();
$service = new UserRoleService($repository, $sessionService);

$user = new User(
    null,
    'Role User',
    'role_user@example.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    null,
    null,
    null,
    'customer',
    true
);

$repository->createUser($user);

$assigned = $service->assignRole((int)$user->getId(), 'technician');
assertTrue($assigned, 'assignRole updates to valid role');
printPass('assignRole updates to valid role');

$updatedUser = $repository->getById((int)$user->getId());
assertSame('technician', $updatedUser->getRole(), 'assignRole persists role change');
printPass('assignRole persists role change');

$invalidRole = $service->assignRole((int)$user->getId(), 'super_admin');
assertFalse($invalidRole, 'assignRole fails for invalid role');
printPass('assignRole fails for invalid role');

$_SESSION['user_role'] = 'administrator';

assertTrue($service->getCurrentRole() === 'administrator', 'hasRole returns true for matching session role');
printPass('hasRole returns true for matching session role');

assertFalse($service->getCurrentRole() === 'customer', 'hasRole returns false for non-matching session role');
printPass('hasRole returns false for non-matching session role');

assertSame('administrator', $service->getCurrentRole(), 'getCurrentRole returns current session role');
printPass('getCurrentRole returns current session role');

echo "\nUserRoleService tests passed.\n";