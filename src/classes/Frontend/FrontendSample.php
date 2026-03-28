<?php
declare(strict_types=1);

require_once __DIR__ . '/../Sample/Repository/SampleRepository.php';
require_once __DIR__ . '/../Sample/Service/SampleService.php';
require_once __DIR__ . '/../Order/Repository/OrderRepository.php';

class FrontendSample
{
    private SampleRepository $repo;
    private SampleService $service;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->repo = new SampleRepository($db);
        $this->service = new SampleService($this->repo, new OrderRepository($db));
    }

    public function addSample(int $orderId, int $orderTypeId, string $compoundName, float $quantity, string $unit): int
    {
        return $this->service->addSample($orderId, $orderTypeId, $compoundName, $quantity, $unit);
    }

    public function getSamplesByOrder(int $orderId): array
    {
        return $this->repo->getByOrderId($orderId);
    }
}

