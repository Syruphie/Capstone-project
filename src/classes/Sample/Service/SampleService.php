<?php
declare(strict_types=1);

/**
 * Class SampleService
 *
 * Core orchestration service for sample lifecycle operations.
 *
 * Responsibilities:
 * - Create and delete samples
 * - Coordinate sample creation with order type data
 * - Calculate derived values (e.g., preparation time)
 * - Trigger related domain updates (e.g., order total recalculation)
 *
 * Non-Responsibilities:
 * - No direct SQL queries (delegated to repository)
 * - No preparation/testing workflow transitions (handled by specialized services)
 *
 * Design Notes:
 * - Serves as the primary entry point for sample operations
 * - Coordinates between SampleRepository and OrderRepository
 */

require_once __DIR__ . '/../Entity/Sample.php';
require_once __DIR__ . '/../Repository/SampleRepository.php';
require_once __DIR__ . '/../Support/SampleStatus.php';
require_once __DIR__ . '/../Support/SampleType.php';
require_once __DIR__ . '/../Support/ValidateSampleType.php';
require_once __DIR__ . '/../../Order/Repository/OrderRepository.php';
require_once __DIR__ . '/../Support/SampleMapper.php';
require_once __DIR__ . '/../Support/ValidateSampleStatus.php';

class SampleService
{
    private SampleRepository $sampleRepository;
    private OrderRepository $orderRepository;
    private SampleMapper $mapper;

    public function __construct(
        ?SampleRepository $sampleRepository = null,
        ?OrderRepository $orderRepository = null,
        ?SampleMapper $mapper = null
    )
    {
        $this->sampleRepository = $sampleRepository;
        $this->orderRepository = $orderRepository;
        $this->mapper = $mapper ?? new SampleMapper();
    }

    public function addSample(
        int $orderId,
        int $orderTypeId,
        string $compoundName,
        float $quantity,
        string $unit
    ): int
    {
        $orderType = $this->sampleRepository->getActiveOrderType($orderTypeId);
        if ($orderType === null) {
            throw new InvalidArgumentException('Invalid or inactive order type.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException("Invalid quantity: {$quantity} must be greater than 0");
        }

        $sampleType = (string)$orderType['sample_type'];
        if (!in_array($sampleType, SampleType::all(), true)) {
            throw new InvalidArgumentException("Invalid sample type: {$sampleType}");
        }

        $preparationTime = $this->calculatePreparationTime($sampleType);

        $sample = $this->mapper->mapArrayToEntity([
            'order_id' => $orderId,
            'order_type_id' => $orderTypeId,
            'unit_cost' => (float)$orderType['cost'],
            'sample_type' => $sampleType,
            'compound_name' => $compoundName,
            'quantity' => $quantity,
            'unit' => $unit,
            'preparation_time' => $preparationTime,
            'testing_time' => null,
            'status' => SampleStatus::PENDING,
            'results' => null,
        ]);

        $sampleId = $this->sampleRepository->createSample($sample);
        $this->orderRepository->updateTotalFromSamples($orderId);

        return $sampleId;
    }

    public function getSampleById(int $sampleId): ?Sample
    {
        return $this->sampleRepository->getById($sampleId);
    }

    public function getSamplesByOrder(int $orderId): array
    {
        return $this->sampleRepository->getByOrderId($orderId);
    }

    public function updateSampleStatus(int $sampleId, string $status): bool
    {
        ValidateSampleStatus::validate($status);
        return $this->sampleRepository->updateStatus($sampleId, $status);
    }

    public function deleteSample(int $sampleId): bool
    {
        $sample = $this->sampleRepository->getById($sampleId);
        if ($sample === null) {
            return false;
        }

        $deleted = $this->sampleRepository->deleteById($sampleId);

        if ($deleted) {
            $this->orderRepository->updateTotalFromSamples($sample->getOrderId());
        }

        return $deleted;
    }

    public function calculatePreparationTime(string $sampleType): int
    {
        ValidateSampleType::validate($sampleType);
        return $sampleType === SampleType::ORE ? 30 : 0;
    }
}