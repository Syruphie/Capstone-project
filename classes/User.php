<?php
require_once __DIR__ . '/../config/database.php';

class User {
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

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Authentication Methods
    public function login($email, $password) {
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

    public function register($fullName, $email, $password, $phone, $companyName, $address, $role = 'customer') {
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

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_name']) && isset($_SESSION['user_role']);
    }

    // User Management Methods
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function updateUser($userId, $data) {
        $allowed = ['full_name', 'phone', 'company_name', 'address'];
        $sets = [];
        $params = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $sets[] = "{$key} = ?";
                $params[] = $data[$key];
            }
        }
        if (empty($sets)) return false;
        $params[] = $userId;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function deleteUser($userId) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Get all users with optional filters.
     * @param string|null $role Filter by role (customer, technician, administrator)
     * @param string|null $search Search in full_name and email (LIKE %search%)
     * @param bool|null $isActive Filter by status (true=active, false=inactive, null=all)
     */
    public function getAllUsers($role = null, $search = null, $isActive = null) {
        $sql = "SELECT id, full_name, email, phone, company_name, role, is_active, last_login, created_at FROM users WHERE 1=1";
        $params = [];
        if ($role !== null && $role !== '') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        if ($search !== null && trim($search) !== '') {
            $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
            $term = '%' . trim($search) . '%';
            $params[] = $term;
            $params[] = $term;
        }
        if ($isActive !== null) {
            $sql .= " AND is_active = ?";
            $params[] = $isActive ? 1 : 0;
        }
        $sql .= " ORDER BY full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function activateUser($userId) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function deactivateUser($userId) {
        return $this->deleteUser($userId);
    }

    public function changePassword($userId, $oldPassword, $newPassword) {
        // Method signature for changing user password
    }

    public function resetPassword($email) {
        // Method signature for password reset functionality
    }

    // Role Management Methods
    public function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    public function assignRole($userId, $role) {
        $allowed = ['customer', 'technician', 'administrator'];
        if (!in_array($role, $allowed, true)) return false;
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $userId]);
    }

    public function getRole() {
        return $_SESSION['user_role'] ?? null;
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function getCompanyName() {
        return $this->companyName;
    }

    public function setCompanyName($companyName) {
        $this->companyName = $companyName;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function getIsActive() {
        return $this->isActive;
    }

    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }
}
