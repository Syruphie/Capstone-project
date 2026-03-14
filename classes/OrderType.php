<?php
require_once __DIR__ . '/../config/database.php';

class OrderType {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM order_types";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM order_types WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $stmt->fetch();
    }

    public function create($name, $description, $sampleType, $cost) {
        $sampleType = in_array($sampleType, ['ore', 'liquid'], true) ? $sampleType : 'ore';
        $stmt = $this->db->prepare(
            "INSERT INTO order_types (name, description, sample_type, cost) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([trim($name), trim($description), $sampleType, (float) $cost]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $allowed = ['name', 'description', 'sample_type', 'cost', 'is_active'];
        $sets = [];
        $params = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $sets[] = "{$key} = ?";
                if ($key === 'is_active') {
                    $params[] = $data[$key] ? 1 : 0;
                } elseif ($key === 'cost') {
                    $params[] = (float) $data[$key];
                } elseif ($key === 'sample_type') {
                    $params[] = in_array($data[$key], ['ore', 'liquid'], true) ? $data[$key] : 'ore';
                } else {
                    $params[] = $data[$key];
                }
            }
        }
        if (empty($sets)) return false;
        $params[] = (int) $id;
        $stmt = $this->db->prepare("UPDATE order_types SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM order_types WHERE id = ?");
        return $stmt->execute([(int) $id]);
    }
}
