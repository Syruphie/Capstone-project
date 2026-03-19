<?php
declare(strict_types=1);

/**
 * Class User
 *
 * Represents a user domain entity.
 *
 * This class models the core user data used throughout the application.
 * It is a plain domain object and should not contain database access,
 * session handling, authentication workflows, or other infrastructure concerns.
 *
 * Responsibilities:
 * - Represent the state of a user
 * - Encapsulate user-related data in a typed structure
 * - Provide accessors and mutators for entity fields as needed
 *
 * Non-Responsibilities:
 * - No database queries or persistence logic
 * - No password hashing or verification
 * - No session access
 * - No business workflow orchestration
 *
 * Design Notes:
 * - Acts as the canonical in-memory representation of a user
 * - Used by repositories, mappers, and services
 * - Should remain framework-agnostic and persistence-agnostic
 */
class User
{
    private ?int $id = null;
    private string $fullName;
    private string $email;
    private string $passwordHash;
    private ?string $phone;
    private ?string $companyName;
    private ?string $address;
    private string $role;
    private bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $lastLogin;

    public function __construct(
        ?int $id = null,
        string $fullName = '',
        string $email = '',
        string $passwordHash = '',
        ?string $phone = null,
        ?string $companyName = null,
        ?string $address = null,
        string $role = 'customer',
        bool $isActive = true,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $lastLogin = null
    )
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->phone = $phone;
        $this->companyName = $companyName;
        $this->address = $address;
        $this->role = $role;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->lastLogin = $lastLogin;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getLastLogin(): ?string
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?string $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }
}