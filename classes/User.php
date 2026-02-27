<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private $db;

    // Properties
    private $id;
    private $fullName;
    private $email;
    private $passwordHash;
    private $phone;
    private $companyName;
    private $address;
    private $role;
    private $isActive;
    private $createdAt;
    private $updatedAt;
    private $lastLogin;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Authentication Methods
    public function login($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];

            // Update last login
            $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            return true;
        }
        return false;
    }

    public function register($fullName, $email, $password, $phone, $companyName, $address, $role = 'customer')
    {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false; // Email already exists
        }

        // Hash password and insert user
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (full_name, email, password_hash, phone, company_name, address, role) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$fullName, $email, $passwordHash, $phone, $companyName, $address, $role]);
    }


    public function logout()
    {
        session_destroy();
        return true;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_name']) && isset($_SESSION['user_role']);
    }

    // User Management Methods
    // public function getUserById($userId)
    // {
    //     $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
    //     $stmt->execute([$userId]);
    //     return $stmt->fetch();
    // }
    public function getUserById($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userId, $fullName, $email, $companyName = '')
    {
        $stmt = $this->db->prepare("
        UPDATE users
        SET full_name = ?, email = ?, company_name = ?
        WHERE id = ?
    ");
        return $stmt->execute([$fullName, $email, $companyName, $userId]);
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Get current password hash
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($currentPassword, $user['password_hash'])) {
            return 'INVALID_CURRENT';
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$newHash, $userId]) ? true : false;
    }
    public function updateUser($userId, $data)
    {
        // Method signature for updating user information
    }

    public function deleteUser($userId)
    {
        // Method signature for deleting/deactivating a user
    }

    public function getAllUsers($role = null)
    {
        // Method signature for retrieving all users, optionally filtered by role
    }

    public function activateUser($userId)
    {
        // Method signature for activating a user account
    }

    public function deactivateUser($userId)
    {
        // Method signature for deactivating a user account
    }

    // public function changePassword($userId, $oldPassword, $newPassword)
    // {
    //     // Method signature for changing user password
    // }

    public function resetPassword($email)
    {
        // Method signature for password reset functionality
    }

    // Role Management Methods
    public function hasRole($role)
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    public function assignRole($userId, $role)
    {
        // Method signature for assigning a role to a user
    }

    public function getRole()
    {
        return $_SESSION['user_role'] ?? null;
    }

    // Getters and Setters
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }
}
