<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repository/OrderTypeRepository.php';
require_once __DIR__ . '/../../../../config/database.php';

class OrderTypeService
{
    private OrderTypeRepository $repository;

    public function __construct(?OrderTypeRepository $repository = null)
    {
        $this->repository = $repository ?? new OrderTypeRepository(Database::getInstance()->getConnection());
    }

    public function getAll(bool $activeOnly = false): array
    {
        return $this->repository->getAll($activeOnly);
    }

    public function getById(int $id): ?array
    {
        return $this->repository->getById($id);
    }

    public function create(string $name, string $description, string $sampleType, float $cost): int
    {
        return $this->repository->create($name, $description, $sampleType, $cost);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}


