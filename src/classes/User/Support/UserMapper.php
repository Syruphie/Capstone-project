<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/User.php';

/**
 * Class UserMapper
 *
 * Maps user database rows to User entities and vice versa.
 *
 * This class is responsible for translating between raw persistence-layer
 * data structures and the domain entity representation used by the
 * application.
 *
 * Responsibilities:
 * - Convert database rows into User entities
 * - Convert User entities into persistence-ready arrays when needed
 * - Normalize field mapping between database column names and entity properties
 *
 * Non-Responsibilities:
 * - No SQL execution
 * - No business logic
 * - No validation beyond basic transformation needs
 *
 * Design Notes:
 * - Keeps mapping logic out of repositories and services
 * - Supports a clean separation between storage shape and domain shape
 */
class UserMapper
{
    public function mapRowToEntity(array $row): User
    {
        return new User(
            isset($row['id']) ? (int)$row['id'] : null,
            (string)($row['full_name'] ?? ''),
            (string)($row['email'] ?? ''),
            (string)($row['password_hash'] ?? ''),
            isset($row['phone']) ? (string)$row['phone'] : null,
            isset($row['company_name']) ? (string)$row['company_name'] : null,
            isset($row['address']) ? (string)$row['address'] : null,
            (string)($row['role'] ?? 'customer'),
            isset($row['is_active']) ? (bool)$row['is_active'] : true,
            isset($row['created_at']) ? (string)$row['created_at'] : null,
            isset($row['updated_at']) ? (string)$row['updated_at'] : null,
            isset($row['last_login']) ? (string)$row['last_login'] : null
        );
    }

    public function mapEntityToRow(User $user): array
    {
        return [
            'id' => $user->getId(),
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'phone' => $user->getPhone(),
            'company_name' => $user->getCompanyName(),
            'address' => $user->getAddress(),
            'role' => $user->getRole(),
            'is_active' => $user->isActive() ? 1 : 0,
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
            'last_login' => $user->getLastLogin(),
        ];
    }
}