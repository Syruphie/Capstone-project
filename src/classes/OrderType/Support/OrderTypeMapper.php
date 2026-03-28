<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/OrderType.php';
require_once __DIR__ . '/OrderTypeSampleType.php';

class OrderTypeMapper
{
    public function mapRowToEntity(array $row): OrderType
    {
        $entity = new OrderType();
        $entity->setId(isset($row['id']) ? (int)$row['id'] : null);
        $entity->setName((string)($row['name'] ?? ''));
        $entity->setDescription((string)($row['description'] ?? ''));
        $entity->setSampleType(OrderTypeSampleType::normalize((string)($row['sample_type'] ?? OrderTypeSampleType::ORE)));
        $entity->setCost((float)($row['cost'] ?? 0));
        $entity->setIsActive((bool)($row['is_active'] ?? true));
        $entity->setCreatedAt($row['created_at'] ?? null);
        $entity->setUpdatedAt($row['updated_at'] ?? null);

        return $entity;
    }
}

