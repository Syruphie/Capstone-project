<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Entity/User.php';
require_once __DIR__ . '/../Support/UserRole.php';
require_once __DIR__ . '/../Support/PasswordVerifier.php';

/**
 * Class UserService
 *
 * Coordinates core user-related business operations.
 *
 * This service acts as the main orchestration layer for general user
 * lifecycle operations that do not belong specifically to authentication,
 * password management, session handling, or authorization.
 *
 * Responsibilities:
 * - Register new users through repository coordination
 * - Retrieve users for profile and admin workflows
 * - Update user profile information
 * - Activate or deactivate users
 * - Coordinate validation and persistence across user operations
 *
 * Non-Responsibilities:
 * - No direct database access
 * - No raw SQL
 * - No session management
 * - No password hashing/verification logic
 * - No role-based authorization decisions beyond delegation
 *
 * Design Notes:
 * - Depends on repositories and support classes to complete workflows
 * - Should contain orchestration and business rules only
 * - Should remain small and focused on general user lifecycle actions
 */
class UserService
{
    private UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function register(
        string $fullName,
        string $email,
        string $password,
        ?string $phone,
        ?string $companyName,
        ?string $address,
        string $role = UserRole::CUSTOMER
    ): bool
    {
        if ($this->userRepository->getByEmail($email)) {
            return false;
        }

        if (!UserRole::isValid($role)) {
            return false;
        }

        if (!PasswordVerifier::isValidPassword($password)) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $user = new User(
            null,
            $fullName,
            $email,
            $passwordHash,
            $phone,
            $companyName,
            $address,
            $role,
            true
        );

        return $this->userRepository->createUser($user);
    }

    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->getById($userId);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $user = $this->userRepository->getById($userId);

        if ($user === null) {
            return false;
        }

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'full_name':
                    $user->setFullName($value);
                    break;
                case 'phone':
                    $user->setPhone($value);
                    break;
                case 'company_name':
                    $user->setCompanyName($value);
                    break;
                case 'address':
                    $user->setAddress($value);
                    break;
                default:
                    throw new InvalidArgumentException("Invalid key $key");
            }
        }

        return $this->userRepository->updateProfile($user);
    }

    /**
     * @return User[]
     */
    public function getAllUsers(?string $role = null, ?string $search = null, ?bool $isActive = null): array
    {
        return $this->userRepository->findAll($role, $search, $isActive);
    }

    public function activateUser(int $userId): bool
    {
        return $this->userRepository->updateActiveStatus($userId, true);
    }

    public function deactivateUser(int $userId): bool
    {
        return $this->userRepository->updateActiveStatus($userId, false);
    }
}