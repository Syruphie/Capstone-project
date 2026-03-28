<?php
declare(strict_types=1);

/**
 * Class Payment
 *
 * Domain entity representing a payment attempt or completed payment
 * associated with an order and customer.
 *
 * Responsibilities:
 * - Encapsulate payment state and attributes
 * - Represent a single row from the `payments` table
 * - Provide getters and setters for payment data
 *
 * Non-Responsibilities:
 * - No Stripe API interaction
 * - No database persistence logic
 * - No webhook handling
 * - No order status synchronization
 *
 * Design Notes:
 * - Hydrated via PaymentMapper or service layer
 * - Used as the core domain model for payment persistence and status tracking
 */
class Payment
{
    private ?int $id = null;
    private int $orderId;
    private int $customerId;
    private float $amount;
    private string $currency;
    private string $provider;
    private ?string $providerPaymentIntentId;
    private string $status;
    private ?string $paidAt;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct()
    {
        $this->orderId = 0;
        $this->customerId = 0;
        $this->amount = 0.0;
        $this->currency = 'cad';
        $this->provider = PaymentProvider::STRIPE;
        $this->providerPaymentIntentId = null;
        $this->status = PaymentStatus::PENDING;
        $this->paidAt = null;
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

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getProviderPaymentIntentId(): ?string
    {
        return $this->providerPaymentIntentId;
    }

    public function setProviderPaymentIntentId(?string $providerPaymentIntentId): void
    {
        $this->providerPaymentIntentId = $providerPaymentIntentId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPaidAt(): ?string
    {
        return $this->paidAt;
    }

    public function setPaidAt(?string $paidAt): void
    {
        $this->paidAt = $paidAt;
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