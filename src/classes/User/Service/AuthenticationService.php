<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/UserSessionService.php';

/**
 * Class AuthenticationService
 *
 * Handles user authentication workflows.
 *
 * This service is responsible for validating login credentials,
 * enforcing account status rules, updating authentication-related
 * user state, and delegating session persistence to the session layer.
 *
 * Responsibilities:
 * - Authenticate users by email and password
 * - Validate active account status during login
 * - Update last login timestamp on successful authentication
 * - Delegate session creation and destruction
 *
 * Non-Responsibilities:
 * - No direct database access
 * - No raw SQL
 * - No general user CRUD workflows
 * - No role assignment logic
 *
 * Design Notes:
 * - Depends on UserRepository for retrieval and persistence
 * - Depends on UserSessionService for session state handling
 * - Should keep login/logout concerns isolated from general user services
 */
class AuthenticationService
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

    public function login(string $email, string $password): bool
    {
        $user = $this->userRepository->getActiveByEmail($email);

        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return false;
        }

        $this->userSessionService->storeUserSession($user);
        $this->userRepository->updateLastLogin((int)$user->getId());

        return true;
    }

    public function logout(): bool
    {
        return $this->userSessionService->clearUserSession();
    }
}