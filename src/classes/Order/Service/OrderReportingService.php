<?php
declare(strict_types=1);

/**
 * Class OrderReportingService
 *
 * Handles order reporting and statistics use cases.
 *
 * Responsibilities:
 * - Retrieve order statistics
 * - Retrieve revenue by period
 * - Retrieve orders for reports/exports
 *
 * Non-Responsibilities:
 * - No direct SQL
 * - No approval/rejection workflows
 * - No payment processing
 * - No history-specific filtering logic
 */

require_once __DIR__ . '/../Repository/OrderRepository.php';
require_once __DIR__ . '/../../Support/DateRangeValidator.php';

class OrderReportingService
{
    private OrderRepository $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function getOrderStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $from = $startDate;
        $to = $endDate;
        if ($startDate === null || $startDate === '') {
            $from = '1900-01-01';
        }
        if ($endDate === null || $endDate === '') {
            $to = date('Y-m-d');
        }
        DateRangeValidator::validate($from, $to);
        return $this->repository->getStatistics($from, $to);
    }

    /**
     * @throws Exception
     */
    public function getRevenueByPeriod(string $startDate, string $endDate): array
    {
        $from = $startDate;
        $to = $endDate;
        if ($startDate === '') {
            $from = '1900-01-01';
        }
        if ($endDate === '') {
            $to = date('Y-m-d');
        }
        DateRangeValidator::validate($from, $to);
        return $this->repository->getRevenueByPeriod($from, $to);
    }

    /**
     * @throws Exception
     */
    public function getOrdersForReport(string $startDate, string $endDate): array
    {
        $from = $startDate;
        $to = $endDate;
        if ($startDate === '') {
            $from = '1900-01-01';
        }
        if ($endDate === '') {
            $to = date('Y-m-d');
        }
        DateRangeValidator::validate($from, $to);
        return $this->repository->getOrdersForReport($from, $to);
    }
}