<?php
declare(strict_types=1);

class Equipment
{
    private ?int $id = null;
    private string $name = '';
    private string $equipmentType = '';
    private int $processingTimePerSample = 0;
    private int $warmupTime = 0;
    private int $breakInterval = 0;
    private int $breakDuration = 0;
    private int $dailyCapacity = 0;
    private bool $isAvailable = true;
    private ?string $lastMaintenance = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getEquipmentType(): string { return $this->equipmentType; }
    public function setEquipmentType(string $equipmentType): void { $this->equipmentType = $equipmentType; }

    public function getProcessingTimePerSample(): int { return $this->processingTimePerSample; }
    public function setProcessingTimePerSample(int $processingTimePerSample): void { $this->processingTimePerSample = $processingTimePerSample; }

    public function getWarmupTime(): int { return $this->warmupTime; }
    public function setWarmupTime(int $warmupTime): void { $this->warmupTime = $warmupTime; }

    public function getBreakInterval(): int { return $this->breakInterval; }
    public function setBreakInterval(int $breakInterval): void { $this->breakInterval = $breakInterval; }

    public function getBreakDuration(): int { return $this->breakDuration; }
    public function setBreakDuration(int $breakDuration): void { $this->breakDuration = $breakDuration; }

    public function getDailyCapacity(): int { return $this->dailyCapacity; }
    public function setDailyCapacity(int $dailyCapacity): void { $this->dailyCapacity = $dailyCapacity; }

    public function isAvailable(): bool { return $this->isAvailable; }
    public function setIsAvailable(bool $isAvailable): void { $this->isAvailable = $isAvailable; }

    public function getLastMaintenance(): ?string { return $this->lastMaintenance; }
    public function setLastMaintenance(?string $lastMaintenance): void { $this->lastMaintenance = $lastMaintenance; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function setUpdatedAt(?string $updatedAt): void { $this->updatedAt = $updatedAt; }
}

