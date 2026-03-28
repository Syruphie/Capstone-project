<?php
declare(strict_types=1);

/**
 * Class Invoice
 *
 * Domain entity representing an invoice generated for a successful payment.
 *
 * Responsibilities:
 * - Encapsulate invoice state and attributes
 * - Represent a single row from the `invoices` table
 * - Provide getters and setters for invoice data
 *
 * Non-Responsibilities:
 * - No PDF/email generation
 * - No database persistence logic
 * - No payment provider interaction
 *
 * Design Notes:
 * - Used for invoice persistence and retrieval
 * - Hydrated via InvoiceMapper or service layer
 */
class Invoice
{
    private ?int $id = null;
    private int $paymentId;
    private int $orderId;
    private int $customerId;
    private string $invoiceNumber;
    private float $amount;
    private string $currency;
    private ?string $issuedAt;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct()
    {
        $this->paymentId = 0;
        $this->orderId = 0;
        $this->customerId = 0;
        $this->invoiceNumber = '';
        $this->amount = 0.0;
        $this->currency = 'cad';
        $this->issuedAt = null;
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

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function setPaymentId(int $paymentId): void
    {
        $this->paymentId = $paymentId;
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

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
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

    public function getIssuedAt(): ?string
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(?string $issuedAt): void
    {
        $this->issuedAt = $issuedAt;
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