<?php
declare(strict_types=1);

class EquipmentDelay
{
    private ?int $id = null;
    private int $equipmentId = 0;
    private string $delayStart = '';
    private int $delayDuration = 0;
    private ?string $reason = null;
    private int $loggedBy = 0;
    private ?string $createdAt = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getEquipmentId(): int { return $this->equipmentId; }
    public function setEquipmentId(int $equipmentId): void { $this->equipmentId = $equipmentId; }

    public function getDelayStart(): string { return $this->delayStart; }
    public function setDelayStart(string $delayStart): void { $this->delayStart = $delayStart; }

    public function getDelayDuration(): int { return $this->delayDuration; }
    public function setDelayDuration(int $delayDuration): void { $this->delayDuration = $delayDuration; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): void { $this->reason = $reason; }

    public function getLoggedBy(): int { return $this->loggedBy; }
    public function setLoggedBy(int $loggedBy): void { $this->loggedBy = $loggedBy; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }
}

