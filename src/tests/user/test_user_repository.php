<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

require_once __DIR__ . '/../../classes/User/Repository/UserRepository.php';
require_once __DIR__ . '/../../classes/User/Entity/User.php';

printSection('UserRepository');

$repository = makeUserRepository();

$user = new User(
    null,
    'Justice Test',
    'justice_repo@example.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    '4031234567',
    'Test Company',
    '123 Test St',
    'customer',
    true
);

$created = $repository->createUser($user);
assertTrue($created, 'createUser inserts a user');
printPass('createUser inserts a user');

assertNotNull($user->getId(), 'createUser sets inserted user id');
printPass('createUser sets inserted user id');

$fetched = $repository->getById((int)$user->getId());
assertNotNull($fetched, 'getById returns inserted user');
printPass('getById returns inserted user');

assertSame('Justice Test', $fetched->getFullName(), 'getById returns correct full name');
printPass('getById returns correct full name');

assertSame('justice_repo@example.com', $fetched->getEmail(), 'getById returns correct email');
printPass('getById returns correct email');

$fetchedByEmail = $repository->getByEmail('justice_repo@example.com');
assertNotNull($fetchedByEmail, 'getByEmail returns inserted user');
printPass('getByEmail returns inserted user');

assertSame((int)$user->getId(), $fetchedByEmail->getId(), 'getByEmail returns matching user');
printPass('getByEmail returns matching user');

$activeUser = $repository->getActiveByEmail('justice_repo@example.com');
assertNotNull($activeUser, 'getActiveByEmail returns active user');
printPass('getActiveByEmail returns active user');

$updatedPassword = $repository->updatePasswordHash((int)$user->getId(), password_hash('NewPassword1!', PASSWORD_DEFAULT));
assertTrue($updatedPassword, 'updatePasswordHash updates password hash');
printPass('updatePasswordHash updates password hash');

$updatedRole = $repository->updateRole((int)$user->getId(), 'technician');
assertTrue($updatedRole, 'updateRole updates user role');
printPass('updateRole updates user role');

$updatedStatusFalse = $repository->updateActiveStatus((int)$user->getId(), false);
assertTrue($updatedStatusFalse, 'updateActiveStatus can deactivate user');
printPass('updateActiveStatus can deactivate user');

$inactiveUser = $repository->getActiveByEmail('justice_repo@example.com');
assertNull($inactiveUser, 'getActiveByEmail returns null for inactive user');
printPass('getActiveByEmail returns null for inactive user');

$updatedStatusTrue = $repository->updateActiveStatus((int)$user->getId(), true);
assertTrue($updatedStatusTrue, 'updateActiveStatus can reactivate user');
printPass('updateActiveStatus can reactivate user');

$updatedLastLogin = $repository->updateLastLogin((int)$user->getId());
assertTrue($updatedLastLogin, 'updateLastLogin updates last_login');
printPass('updateLastLogin updates last_login');

$profileUpdated = $repository->updateProfile(new User(
    (int)$user->getId(),
    'Justice Updated',
    'justice_repo@example.com',
    $fetchedByEmail->getPasswordHash(),
    '5879990000',
    'Updated Company',
    '999 Updated Ave',
    'technician',
    true,
    $fetchedByEmail->getCreatedAt(),
    $fetchedByEmail->getUpdatedAt(),
    $fetchedByEmail->getLastLogin()
));
assertTrue($profileUpdated, 'updateProfile updates profile fields');
printPass('updateProfile updates profile fields');

$updatedUser = $repository->getById((int)$user->getId());
assertSame('Justice Updated', $updatedUser->getFullName(), 'updateProfile updates full name');
printPass('updateProfile updates full name');

assertSame('5879990000', $updatedUser->getPhone(), 'updateProfile updates phone');
printPass('updateProfile updates phone');

assertSame('Updated Company', $updatedUser->getCompanyName(), 'updateProfile updates company name');
printPass('updateProfile updates company name');

assertSame('999 Updated Ave', $updatedUser->getAddress(), 'updateProfile updates address');
printPass('updateProfile updates address');

$allUsers = $repository->findAll();
assertTrue(count($allUsers) >= 1, 'findAll returns at least one user');
printPass('findAll returns at least one user');

$roleFiltered = $repository->findAll('technician');
assertTrue(count($roleFiltered) >= 1, 'findAll filters by role');
printPass('findAll filters by role');

$searchFiltered = $repository->findAll(null, 'Justice Updated');
assertTrue(count($searchFiltered) >= 1, 'findAll filters by search');
printPass('findAll filters by search');

$statusFiltered = $repository->findAll(null, null, true);
assertTrue(count($statusFiltered) >= 1, 'findAll filters by active status');
printPass('findAll filters by active status');

echo "\nUserRepository tests passed.\n";