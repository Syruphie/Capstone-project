<?php
declare(strict_types=1);

require_once __DIR__ . '/../OrderType/Service/OrderTypeService.php';
require_once __DIR__ . '/../OrderType/Repository/OrderTypeRepository.php';

class FrontendOrderType
{
    private OrderTypeService $service;

    public function __construct()
    {
        $this->service = new OrderTypeService(new OrderTypeRepository(Database::getInstance()->getConnection()));
    }

    public function getAll(bool $activeOnly = false): array
    {
        return $this->service->getAll($activeOnly);
    }

    public function getById(int $id): ?array
    {
        return $this->service->getById($id);
    }

    public function create(string $name, string $description, string $sampleType, float $cost): int
    {
        return $this->service->create($name, $description, $sampleType, $cost);
    }

    public function update(int $id, array $data): bool
    {
        return $this->service->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->service->delete($id);
    }
}

