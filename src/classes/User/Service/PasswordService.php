<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Support/PasswordVerifier.php';

/**
 * Class PasswordService
 *
 * Handles password-related business workflows.
 *
 * This service is responsible for coordinating password verification,
 * password change operations, and future reset-password flows while
 * keeping password lifecycle concerns out of general user services.
 *
 * Responsibilities:
 * - Change a user's password
 * - Verify existing password before password changes
 * - Hash new passwords before persistence
 * - Provide a clear home for future password reset workflows
 *
 * Non-Responsibilities:
 * - No direct database access
 * - No raw SQL
 * - No session management
 * - No general user CRUD or profile updates
 *
 * Design Notes:
 * - Centralizes password lifecycle concerns
 * - Helps keep authentication and user profile services smaller
 * - Can later collaborate with email/reset token services if needed
 */
class PasswordService
{
    private UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        if ($oldPassword === $newPassword) {
            return false;
        }

        if (!PasswordVerifier::isValidPassword($newPassword)) {
            return false;
        }

        $user = $this->userRepository->getById($userId);

        if ($user === null) {
            return false;
        }

        if (!password_verify($oldPassword, $user->getPasswordHash())) {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = $this->userRepository->updatePasswordHash($userId, $hash);

        return $result;
    }

    public function resetPassword(string $email): void
    {
        // TODO: Placeholder for future reset-password workflow.
    }
}