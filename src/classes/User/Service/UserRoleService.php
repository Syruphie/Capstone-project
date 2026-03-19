<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Support/UserRole.php';
require_once __DIR__ . '/UserSessionService.php';

/**
 * Class UserRoleService
 *
 * Handles user role assignment and role-related checks.
 *
 * This service is responsible for validating supported roles,
 * coordinating role updates through the repository, and providing
 * role-aware helper methods for application workflows.
 *
 * Responsibilities:
 * - Assign roles to users
 * - Validate role values against supported role definitions
 * - Provide role helper methods where appropriate
 *
 * Non-Responsibilities:
 * - No direct database access
 * - No raw SQL
 * - No authentication workflows
 * - No general user profile operations
 *
 * Design Notes:
 * - Keeps role concerns isolated from general user management
 * - Encourages use of centralized role definitions instead of magic strings
 */
class UserRoleService
{
    private UserRepository $userRepository;
    private UserSessionService $userSessionService;

    public function __construct(
        ?UserRepository $userRepository = null,
        ?UserSessionService $userSessionService = null
    )
    {
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->userSessionService = $userSessionService ?? new UserSessionService();
    }

    public function assignRole(int $userId, string $role): bool
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            return false;
        }

        if (!UserRole::isValid($role)) {
            return false;
        }

        return $this->userRepository->updateRole($userId, $role);
    }

    public function getCurrentRole(): ?string
    {
        return $this->userSessionService->getCurrentUserRole();
    }
}