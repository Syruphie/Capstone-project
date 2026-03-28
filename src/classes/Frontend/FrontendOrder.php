<?php
declare(strict_types=1);

require_once __DIR__ . '/../Order/Repository/OrderRepository.php';
require_once __DIR__ . '/../Order/Service/OrderService.php';
require_once __DIR__ . '/../Order/Service/OrderApprovalService.php';
require_once __DIR__ . '/../Order/Service/OrderHistoryService.php';
require_once __DIR__ . '/../Order/Service/OrderReportingService.php';

class FrontendOrder
{
    private OrderRepository $repo;
    private OrderService $service;
    private OrderApprovalService $approvalService;
    private OrderHistoryService $historyService;
    private OrderReportingService $reportingService;

    public function __construct()
    {
        $this->repo = new OrderRepository(Database::getInstance()->getConnection());
        $this->service = new OrderService($this->repo);
        $this->approvalService = new OrderApprovalService($this->repo);
        $this->historyService = new OrderHistoryService($this->repo);
        $this->reportingService = new OrderReportingService($this->repo);
    }

    public function createOrder(int $customerId, string $priority = 'standard'): int
    {
        $orderId = $this->service->createOrder($customerId, $priority);
        $this->repo->updateOrderStatus($orderId, 'submitted');

        return $orderId;
    }

    public function getOrderById(int $orderId): ?array
    {
        $order = $this->service->getOrderById($orderId);
        if ($order === null) {
            return null;
        }

        return [
            'id' => $order->getId(),
            'customer_id' => $order->getCustomerId(),
            'order_number' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'priority' => $order->getPriority(),
            'total_cost' => $order->getTotalCost(),
            'estimated_completion' => $order->getEstimatedCompletion(),
            'approved_by' => $order->getApprovedBy(),
            'approved_at' => $order->getApprovedAt(),
            'rejection_reason' => $order->getRejectionReason(),
            'created_at' => $order->getCreatedAt(),
            'updated_at' => $order->getUpdatedAt(),
            'completed_at' => $order->getCompletedAt(),
        ];
    }

    public function getOrderWithCustomer(int $orderId): ?array
    {
        return $this->service->getOrderWithCustomer($orderId);
    }

    public function getOrdersByCustomer(int $customerId): array
    {
        return $this->service->getOrdersByCustomer($customerId, 200, 0);
    }

    public function getPendingOrders(): array
    {
        return $this->service->getPendingOrders();
    }

    public function approveOrder(int $orderId, int $approvedBy): bool
    {
        return $this->approvalService->approveOrder($orderId, $approvedBy);
    }

    public function rejectOrder(int $orderId, string $reason): bool
    {
        return $this->approvalService->rejectOrder($orderId, $reason);
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->repo->updateOrderStatus($orderId, $status);
    }

    public function updateEstimatedCompletion(int $orderId, string $estimatedCompletion): bool
    {
        return $this->repo->updateEstimatedCompletion($estimatedCompletion, $orderId);
    }

    public function getOrderHistoryForCustomer(int $customerId, ?string $searchOrderNumber, ?string $searchDateFrom, ?string $searchDateTo): array
    {
        return $this->historyService->getOrderHistoryForCustomer($customerId, $searchOrderNumber, $searchDateFrom, $searchDateTo);
    }

    public function getOrderHistoryForAdmin(?string $searchCustomerName, ?string $searchOrderNumber, ?string $searchDateFrom, ?string $searchDateTo): array
    {
        return $this->historyService->getOrderHistoryForAdmin($searchCustomerName, $searchOrderNumber, $searchDateFrom, $searchDateTo);
    }

    public function getOrderStatistics(string $from, string $to): array
    {
        return $this->reportingService->getOrderStatistics($from, $to);
    }

    public function getRevenueByPeriod(string $from, string $to): array
    {
        return $this->reportingService->getRevenueByPeriod($from, $to);
    }

    public function getOrdersForReport(string $from, string $to): array
    {
        return $this->reportingService->getOrdersForReport($from, $to);
    }
}

