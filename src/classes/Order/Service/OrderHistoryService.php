<?php
declare(strict_types=1);

/**
 * Class OrderHistoryService
 *
 * Handles finished-order history retrieval for customers and admins.
 *
 * Responsibilities:
 * - Retrieve customer order history
 * - Retrieve admin order history
 * - Keep history-related query use cases out of the general OrderService
 *
 * Non-Responsibilities:
 * - No direct SQL
 * - No reporting/statistics aggregation
 * - No approval or status workflow logic
 */

require_once __DIR__ . '/../Repository/OrderRepository.php';

class OrderHistoryService
{
    private OrderRepository $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function getOrderHistoryForCustomer(
        int $customerId,
        ?string $searchOrderNumber,
        ?string $searchDateFrom,
        ?string $searchDateTo,
        int $limit = 100
    ): array
    {
        $from = $searchDateFrom;
        $to = $searchDateTo;
        if ($searchDateFrom === null || $searchDateFrom === '') {
            $from = '1900-01-01';
        }
        if ($searchDateTo === null || $searchDateTo === '') {
            $to = date('Y-m-d');
        }
        DateRangeValidator::validate($from, $to);
        $searchOrderNumber = trim($searchOrderNumber ?? '');

        if ($limit <= 0) {
            $limit = 100;
        }

        return $this->repository->getFinishedHistoryForCustomer(
            $customerId,
            $searchOrderNumber,
            $from,
            $to,
            $limit
        );
    }

    /**
     * @throws Exception
     */
    public function getOrderHistoryForAdmin(
        ?string $searchCustomerName,
        ?string $searchOrderNumber,
        ?string $searchDateFrom,
        ?string $searchDateTo,
        int $limit = 200
    ): array
    {
        $from = $searchDateFrom;
        $to = $searchDateTo;
        if ($searchDateFrom === null || $searchDateFrom === '') {
            $from = '1900-01-01';
        }
        if ($searchDateTo === null || $searchDateTo === '') {
            $to = date('Y-m-d');
        }
        DateRangeValidator::validate($from, $to);

        if ($limit <= 0) {
            $limit = 200;
        }
        return $this->repository->getFinishedHistoryForAdmin(
            $searchCustomerName,
            $searchOrderNumber,
            $from,
            $to,
            $limit
        );
    }
}