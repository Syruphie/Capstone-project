<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

require_once __DIR__ . '/../../classes/User/Service/UserService.php';
require_once __DIR__ . '/../../classes/User/Repository/UserRepository.php';

printSection('UserService');

$repository = makeUserRepository();
$service = new UserService($repository);

$registered = $service->register(
    'Service User',
    'service_user@example.com',
    'Password1!',
    '4037771111',
    'Service Co',
    '111 Service St',
    'customer'
);
assertTrue($registered, 'register succeeds for valid user');
printPass('register succeeds for valid user');

$createdUser = $repository->getByEmail('service_user@example.com');
assertNotNull($createdUser, 'register persists user');
printPass('register persists user');

assertSame('Service User', $createdUser->getFullName(), 'register stores correct full name');
printPass('register stores correct full name');

$duplicate = $service->register(
    'Duplicate User',
    'service_user@example.com',
    'Password1!',
    null,
    null,
    null,
    'customer'
);
assertFalse($duplicate, 'register fails for duplicate email');
printPass('register fails for duplicate email');

$invalidRole = $service->register(
    'Bad Role User',
    'bad_role_user@example.com',
    'Password1!',
    null,
    null,
    null,
    'super_admin'
);
assertFalse($invalidRole, 'register fails for invalid role');
printPass('register fails for invalid role');

$weakPassword = $service->register(
    'Weak Password User',
    'weak_password_user@example.com',
    'weak',
    null,
    null,
    null,
    'customer'
);
assertFalse($weakPassword, 'register fails for weak password');
printPass('register fails for weak password');

$getUser = $service->getUserById((int)$createdUser->getId());
assertNotNull($getUser, 'getUserById returns created user');
printPass('getUserById returns created user');

$updated = $service->updateProfile((int)$createdUser->getId(), [
    'full_name' => 'Service User Updated',
    'phone' => '5872223333',
    'company_name' => 'Updated Co',
    'address' => '222 Updated St',
]);
assertTrue($updated, 'updateProfile updates allowed fields');
printPass('updateProfile updates allowed fields');

$updatedUser = $repository->getById((int)$createdUser->getId());
assertSame('Service User Updated', $updatedUser->getFullName(), 'updateProfile updates full name');
printPass('updateProfile updates full name');

assertSame('5872223333', $updatedUser->getPhone(), 'updateProfile updates phone');
printPass('updateProfile updates phone');

assertSame('Updated Co', $updatedUser->getCompanyName(), 'updateProfile updates company name');
printPass('updateProfile updates company name');

assertSame('222 Updated St', $updatedUser->getAddress(), 'updateProfile updates address');
printPass('updateProfile updates address');

$allUsers = $service->getAllUsers();
assertTrue(count($allUsers) >= 1, 'getAllUsers returns users');
printPass('getAllUsers returns users');

$deactivated = $service->deactivateUser((int)$createdUser->getId());
assertTrue($deactivated, 'deactivateUser deactivates user');
printPass('deactivateUser deactivates user');

$inactiveUser = $repository->getById((int)$createdUser->getId());
assertFalse($inactiveUser->isActive(), 'deactivateUser persists inactive status');
printPass('deactivateUser persists inactive status');

$activated = $service->activateUser((int)$createdUser->getId());
assertTrue($activated, 'activateUser activates user');
printPass('activateUser activates user');

$reactivatedUser = $repository->getById((int)$createdUser->getId());
assertTrue($reactivatedUser->isActive(), 'activateUser persists active status');
printPass('activateUser persists active status');

echo "\nUserService tests passed.\n";