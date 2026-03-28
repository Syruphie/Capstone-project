<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/OrderTypeMapper.php';
require_once __DIR__ . '/../Support/OrderTypeSampleType.php';

class OrderTypeRepository
{
    private PDO $db;
    private OrderTypeMapper $mapper;

    public function __construct(PDO $db, ?OrderTypeMapper $mapper = null)
    {
        $this->db = $db;
        $this->mapper = $mapper ?? new OrderTypeMapper();
    }

    public function getAll(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM order_types';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }

        $sql .= ' ORDER BY name ASC';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_types WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getEntityById(int $id): ?OrderType
    {
        $row = $this->getById($id);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function create(string $name, string $description, string $sampleType, float $cost): int
    {
        $sampleType = OrderTypeSampleType::normalize($sampleType);

        $stmt = $this->db->prepare(
            'INSERT INTO order_types (name, description, sample_type, cost) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([trim($name), trim($description), $sampleType, $cost]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'description', 'sample_type', 'cost', 'is_active'];
        $sets = [];
        $params = [];

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $sets[] = "{$key} = ?";

            if ($key === 'is_active') {
                $params[] = $data[$key] ? 1 : 0;
                continue;
            }

            if ($key === 'cost') {
                $params[] = (float)$data[$key];
                continue;
            }

            if ($key === 'sample_type') {
                $params[] = OrderTypeSampleType::normalize((string)$data[$key]);
                continue;
            }

            $params[] = $data[$key];
        }

        if ($sets === []) {
            return false;
        }

        $params[] = $id;
        $stmt = $this->db->prepare('UPDATE order_types SET ' . implode(', ', $sets) . ' WHERE id = ?');

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM order_types WHERE id = ?');

        return $stmt->execute([$id]);
    }
}

