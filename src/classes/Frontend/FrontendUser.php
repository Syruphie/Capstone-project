<?php
declare(strict_types=1);

require_once __DIR__ . '/../User/Repository/UserRepository.php';
require_once __DIR__ . '/../User/Service/AuthenticationService.php';
require_once __DIR__ . '/../User/Service/UserService.php';
require_once __DIR__ . '/../User/Service/PasswordService.php';
require_once __DIR__ . '/../User/Service/UserRoleService.php';
require_once __DIR__ . '/../User/Service/UserSessionService.php';

class FrontendUser
{
    private UserRepository $repo;
    private AuthenticationService $auth;
    private UserService $userService;
    private PasswordService $passwordService;
    private UserRoleService $roleService;
    private UserSessionService $sessionService;

    public function __construct()
    {
        $this->repo = new UserRepository(Database::getInstance()->getConnection());
        $this->sessionService = new UserSessionService();
        $this->auth = new AuthenticationService($this->repo, $this->sessionService);
        $this->userService = new UserService($this->repo);
        $this->passwordService = new PasswordService($this->repo);
        $this->roleService = new UserRoleService($this->repo, $this->sessionService);
    }

    public function isLoggedIn(): bool
    {
        return $this->sessionService->isLoggedIn();
    }

    public function getRole(): string
    {
        return (string)($this->sessionService->getCurrentUserRole() ?? '');
    }

    public function login(string $email, string $password): bool
    {
        return $this->auth->login($email, $password);
    }

    public function logout(): bool
    {
        return $this->auth->logout();
    }

    public function register(string $fullName, string $email, string $password, ?string $phone = null, ?string $companyName = null, ?string $address = null): bool
    {
        return $this->userService->register($fullName, $email, $password, $phone, $companyName, $address);
    }

    public function getUserById(int $userId): ?array
    {
        $user = $this->userService->getUserById($userId);
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->getId(),
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'company_name' => $user->getCompanyName(),
            'address' => $user->getAddress(),
            'role' => $user->getRole(),
            'is_active' => $user->isActive(),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
            'last_login' => $user->getLastLogin(),
        ];
    }

    public function updateUser(int $userId, array $data): bool
    {
        return $this->userService->updateProfile($userId, $data);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        return $this->passwordService->changePassword($userId, $currentPassword, $newPassword);
    }

    public function deactivateUser(int $userId): bool
    {
        return $this->userService->deactivateUser($userId);
    }

    public function activateUser(int $userId): bool
    {
        return $this->userService->activateUser($userId);
    }

    public function assignRole(int $userId, string $newRole): bool
    {
        return $this->roleService->assignRole($userId, $newRole);
    }

    public function getAllUsers(?string $role = null, ?string $search = null, ?bool $isActive = null): array
    {
        $users = $this->userService->getAllUsers($role, $search, $isActive);

        return array_map(static function (User $user): array {
            return [
                'id' => $user->getId(),
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'company_name' => $user->getCompanyName(),
                'address' => $user->getAddress(),
                'role' => $user->getRole(),
                'is_active' => $user->isActive(),
                'created_at' => $user->getCreatedAt(),
                'updated_at' => $user->getUpdatedAt(),
                'last_login' => $user->getLastLogin(),
            ];
        }, $users);
    }
}

