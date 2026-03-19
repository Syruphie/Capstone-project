<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/User.php';

/**
 * Class UserSessionService
 *
 * Handles session state for authenticated users.
 *
 * This service is responsible for reading and writing user-related
 * session data so that session concerns remain isolated from domain
 * entities, repositories, and broader business services.
 *
 * Responsibilities:
 * - Store authenticated user session state
 * - Clear session state during logout
 * - Report whether a user is currently authenticated
 * - Provide access to current session user information
 * - Provide role checks based on session state
 *
 * Non-Responsibilities:
 * - No direct database access
 * - No raw SQL
 * - No authentication credential validation
 * - No general user CRUD workflows
 *
 * Design Notes:
 * - Centralizes all access to $_SESSION for user auth state
 * - Helps prevent session logic from spreading through controllers/services
 */
class UserSessionService
{
    public function storeUserSession(User $user): void
    {
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['user_name'] = $user->getFullName();
    }

    public function clearUserSession(): bool
    {
        $_SESSION = [];
        session_destroy();
        return true;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role']);
    }

    public function hasRole(string $role): bool
    {
        return $this->getCurrentUserRole() === $role;
    }

    public function getCurrentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public function getCurrentUserRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public function getCurrentUserName(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }
}