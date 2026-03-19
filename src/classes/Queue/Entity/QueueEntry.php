<?php
declare(strict_types = 1);

/**
 * Class QueueEntry
 *
 * Represents a single queue record in the system.
 *
 * This class is a data container (Entity/DTO) that models the structure
 * of a row in the `queue` table. It does not contain business logic
 * or database access logic.
 *
 * Responsibilities:
 * - Store queue-related data (order, equipment, position, schedule, etc.)
 * - Provide access to queue entry state via properties/getters
 *
 * Non-Responsibilities:
 * - No database queries
 * - No queue manipulation logic (movement, scheduling, processing)
 *
 * Usage:
 * - Created by services when adding/updating queue entries
 * - Returned by repository methods (if using object mapping)
 */

class QueueEntry
{
    private ?int $id;
    private int $orderId;
    private int $equipmentId;
    private int $position;
    private ?string $scheduledStart;
    private ?string $scheduledEnd;
    private ?string $actualStart;
    private ?string $actualEnd;
    private string $queueType;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $orderId,
        int $equipmentId,
        int $position,
        string $queueType,
        ?string $scheduledStart = null,
        ?string $scheduledEnd = null,
        ?string $actualStart = null,
        ?string $actualEnd = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->equipmentId = $equipmentId;
        $this->position = $position;
        $this->queueType = $queueType;
        $this->scheduledStart = $scheduledStart;
        $this->scheduledEnd = $scheduledEnd;
        $this->actualStart = $actualStart;
        $this->actualEnd = $actualEnd;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getOrderId(): int { return $this->orderId; }
    public function getEquipmentId(): int { return $this->equipmentId; }
    public function getPosition(): int { return $this->position; }
    public function getQueueType(): string { return $this->queueType; }
    public function getScheduledStart(): ?string { return $this->scheduledStart; }
    public function getScheduledEnd(): ?string { return $this->scheduledEnd; }
    public function getActualStart(): ?string { return $this->actualStart; }
    public function getActualEnd(): ?string { return $this->actualEnd; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setPosition(int $position): void { $this->position = $position; }
    public function setScheduledStart(?string $scheduledStart): void { $this->scheduledStart = $scheduledStart; }
    public function setScheduledEnd(?string $scheduledEnd): void { $this->scheduledEnd = $scheduledEnd; }
    public function setActualStart(?string $actualStart): void { $this->actualStart = $actualStart; }
    public function setActualEnd(?string $actualEnd): void { $this->actualEnd = $actualEnd; }
}