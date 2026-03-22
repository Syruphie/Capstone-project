<?php
declare(strict_types=1);

/**
 * Class Sample
 *
 * Domain entity representing a laboratory sample associated with an order.
 *
 * Responsibilities:
 * - Encapsulate sample state and attributes
 * - Provide getters and setters for controlled access to properties
 * - Represent a single row from the `samples` table
 *
 * Non-Responsibilities:
 * - No database interaction
 * - No business logic or workflow rules
 * - No validation enforcement
 *
 * Design Notes:
 * - Initialized with sensible defaults
 * - Hydrated via SampleMapper or service layer
 * - Used across services and repository as the core domain model
 */
class Sample
{
    private ?int $id = null;
    private int $orderId;
    private int $orderTypeId;
    private float $unitCost;
    private string $sampleType;
    private string $compoundName;
    private float $quantity;
    private string $unit;
    private int $preparationTime;
    private ?int $testingTime;
    private string $status;
    private ?string $results;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct()
    {
        $this->orderId = 0;
        $this->orderTypeId = 0;
        $this->unitCost = 0.0;
        $this->sampleType = SampleType::ORE;
        $this->compoundName = '';
        $this->quantity = 0.0;
        $this->unit = '';
        $this->preparationTime = 0;
        $this->testingTime = null;
        $this->status = SampleStatus::PENDING;
        $this->results = null;
        $this->createdAt = null;
        $this->updatedAt = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderTypeId(): int
    {
        return $this->orderTypeId;
    }

    public function setOrderTypeId(int $orderTypeId): void
    {
        $this->orderTypeId = $orderTypeId;
    }

    public function getUnitCost(): float
    {
        return $this->unitCost;
    }

    public function setUnitCost(float $unitCost): void
    {
        $this->unitCost = $unitCost;
    }

    public function getSampleType(): string
    {
        return $this->sampleType;
    }

    public function setSampleType(string $sampleType): void
    {
        $this->sampleType = $sampleType;
    }

    public function getCompoundName(): string
    {
        return $this->compoundName;
    }

    public function setCompoundName(string $compoundName): void
    {
        $this->compoundName = $compoundName;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getPreparationTime(): int
    {
        return $this->preparationTime;
    }

    public function setPreparationTime(int $preparationTime): void
    {
        $this->preparationTime = $preparationTime;
    }

    public function getTestingTime(): ?int
    {
        return $this->testingTime;
    }

    public function setTestingTime(?int $testingTime): void
    {
        $this->testingTime = $testingTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getResults(): ?string
    {
        return $this->results;
    }

    public function setResults(?string $results): void
    {
        $this->results = $results;
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
}