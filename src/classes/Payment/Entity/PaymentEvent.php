<?php
declare(strict_types=1);

/**
 * Class PaymentEvent
 *
 * Domain entity representing a processed payment provider webhook/event.
 *
 * Responsibilities:
 * - Encapsulate provider event metadata
 * - Represent a single row from the `payment_events` table
 * - Support idempotency and webhook tracking
 *
 * Non-Responsibilities:
 * - No webhook verification logic
 * - No database persistence logic
 * - No business workflow orchestration
 *
 * Design Notes:
 * - Used to prevent duplicate event processing
 * - Hydrated via service layer or mapper if needed later
 */
class PaymentEvent
{
    private ?int $id = null;
    private string $provider;
    private string $providerEventId;
    private string $eventType;
    private ?string $processedAt;
    private ?string $createdAt;

    public function __construct()
    {
        $this->provider = PaymentProvider::STRIPE;
        $this->providerEventId = '';
        $this->eventType = '';
        $this->processedAt = null;
        $this->createdAt = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getProviderEventId(): string
    {
        return $this->providerEventId;
    }

    public function setProviderEventId(string $providerEventId): void
    {
        $this->providerEventId = $providerEventId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getProcessedAt(): ?string
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?string $processedAt): void
    {
        $this->processedAt = $processedAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}