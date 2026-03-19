<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/User.php';
require_once __DIR__ . '/../Support/UserMapper.php';
require_once __DIR__ . '/../../Support/EmailValidator.php';
require_once __DIR__ . '/../Entity/User.php';
require_once __DIR__ . '/../../../config/database.php';

/**
 * Class UserRepository
 *
 * Handles all direct database interactions for users.
 *
 * This class is responsible for all persistence and retrieval logic
 * related to the `users` table and its associated data. It acts as the
 * single source of truth for database access for user-related operations.
 *
 * Responsibilities:
 * - Insert, update, and soft delete users
 * - Retrieve users by ID, email, role, or other filters
 * - Execute all read queries for user management views and administration
 * - Provide query methods for authentication, profile management, and reporting
 * - Execute persistence operations such as password updates, role updates, and status updates
 *
 * Non-Responsibilities:
 * - No business logic (e.g., authentication rules, authorization decisions)
 * - No orchestration of multistep workflows
 * - No password hashing or verification
 * - No session management
 *
 * Design Notes:
 * - Acts as the centralized data access layer for user operations
 * - May contain both simple queries and more complex filtered queries
 * - Should remain focused on SQL execution and data retrieval/persistence
 * - Can be reused across multiple services and controllers
 */
class UserRepository
{
    private PDO $db;
    private UserMapper $mapper;

    public function __construct(?PDO $db = null, ?UserMapper $mapper = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->mapper = $mapper ?? new UserMapper();
    }

    public function getById(int $userId): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapper->mapRowToEntity($row);
    }

    public function getByEmail(string $email): ?User
    {
        EmailValidator::validateEmail($email);
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapper->mapRowToEntity($row);
    }

    public function getActiveByEmail(string $email): ?User
    {
        EmailValidator::validateEmail($email);
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapper->mapRowToEntity($row);
    }

    public function createUser(User $user): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash, phone, company_name, address, role, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        $result = $stmt->execute([
            $user->getFullName(),
            $user->getEmail(),
            $user->getPasswordHash(),
            $user->getPhone(),
            $user->getCompanyName(),
            $user->getAddress(),
            $user->getRole(),
        ]);

        if ($result) {
            $user->setId((int)$this->db->lastInsertId());
        }

        return $result;
    }

    public function updateProfile(User $user): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET full_name = ?, phone = ?, company_name = ?, address = ? WHERE id = ?');
        return $stmt->execute([$user->getFullName(), $user->getPhone(), $user->getCompanyName(), $user->getAddress(), $user->getId()]);
    }

    public function updatePasswordHash(int $userId, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$passwordHash, $userId]);
    }

    public function updateRole(int $userId, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
        return $stmt->execute([$role, $userId]);
    }

    public function updateActiveStatus(int $userId, bool $isActive): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
        return $stmt->execute([$isActive ? 1 : 0, $userId]);
    }

    public function updateLastLogin(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    /**
     * @return User[]
     */
    public function findAll(?string $role = null, ?string $search = null, ?bool $isActive = null): array
    {
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        if ($role !== null && $role !== '') {
            $sql .= ' AND role = ?';
            $params[] = $role;
        }

        if ($search !== null && trim($search) !== '') {
            $sql .= ' AND (full_name LIKE ? OR email LIKE ?)';
            $term = '%' . trim($search) . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if ($isActive !== null) {
            $sql .= ' AND is_active = ?';
            $params[] = $isActive ? 1 : 0;
        }

        $sql .= ' ORDER BY full_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->mapper->mapRowToEntity($row);
        }

        return $users;
    }
}