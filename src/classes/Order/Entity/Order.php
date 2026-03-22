<?php
declare(strict_types=1);

/**
 * Class Order
 *
 * Represents a single order entity in the system.
 *
 * This class is responsible only for storing and exposing
 * order-related state. It should not contain database queries,
 * workflow logic, reporting logic, or orchestration.
 *
 * Responsibilities:
 * - Represent one order record
 * - Expose getters and setters for order fields
 *
 * Non-Responsibilities:
 * - No direct database access
 * - No workflow/business process orchestration
 * - No reporting/statistics queries
 * - No payment handling
 * - No timeline/event handling
 */
class Order
{
    private ?int $id = null;
    private int $customerId;
    private string $orderNumber;
    private string $status;
    private string $priority;
    private float $totalCost;
    private ?string $estimatedCompletion;
    private ?int $approvedBy;
    private ?string $approvedAt;
    private ?string $rejectionReason;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $completedAt;

    public function __construct()
    {
        $this->status = 'draft';
        $this->priority = 'standard';
        $this->totalCost = 0.0;
        $this->estimatedCompletion = null;
        $this->approvedBy = null;
        $this->approvedAt = null;
        $this->rejectionReason = null;
        $this->createdAt = null;
        $this->updatedAt = null;
        $this->completedAt = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
    }

    public function getTotalCost(): float
    {
        return $this->totalCost;
    }

    public function setTotalCost(float $totalCost): void
    {
        $this->totalCost = $totalCost;
    }

    public function getEstimatedCompletion(): ?string
    {
        return $this->estimatedCompletion;
    }

    public function setEstimatedCompletion(?string $estimatedCompletion): void
    {
        $this->estimatedCompletion = $estimatedCompletion;
    }

    public function getApprovedBy(): ?int
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?int $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }

    public function getApprovedAt(): ?string
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?string $approvedAt): void
    {
        $this->approvedAt = $approvedAt;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): void
    {
        $this->rejectionReason = $rejectionReason;
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

    public function getCompletedAt(): ?string
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?string $completedAt): void
    {
        $this->completedAt = $completedAt;
    }
}