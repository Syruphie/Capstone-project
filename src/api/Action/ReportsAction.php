<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class ReportsAction
{
    public function handle(): void
    {
        ApiAuth::requireUser(['administrator']);

        $type = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
        $range = isset($_GET['range']) ? trim((string)$_GET['range']) : 'month';
        $fromParam = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $toParam = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

        [$from, $to] = $this->resolveDateRange($range, $fromParam, $toParam);

        try {
            $order = new FrontendOrder();
            $equipment = new FrontendEquipment();
            $queue = new FrontendQueue();

            switch ($type) {
                case 'orders':
                    JsonResponse::send([
                        'success' => true,
                        'report' => 'orders',
                        'from' => $from,
                        'to' => $to,
                        'statistics' => $order->getOrderStatistics($from, $to),
                        'rows' => $order->getOrdersForReport($from, $to),
                    ]);
                    return;

                case 'revenue':
                    $rev = $order->getRevenueByPeriod($from, $to);
                    JsonResponse::send([
                        'success' => true,
                        'report' => 'revenue',
                        'from' => $from,
                        'to' => $to,
                        'revenue' => (float)($rev['revenue'] ?? 0),
                        'order_count' => (int)($rev['order_count'] ?? 0),
                        'rows' => $order->getOrdersForReport($from, $to),
                    ]);
                    return;

                case 'equipment':
                    JsonResponse::send([
                        'success' => true,
                        'report' => 'equipment',
                        'from' => $from,
                        'to' => $to,
                        'equipment' => $equipment->getAllEquipmentWithStats(),
                    ]);
                    return;

                case 'queue':
                    JsonResponse::send([
                        'success' => true,
                        'report' => 'queue',
                        'from' => $from,
                        'to' => $to,
                        'statistics' => $queue->getQueueStatistics($from, $to),
                        'rows' => $queue->getQueueEntriesForReport($from, $to),
                    ]);
                    return;
            }

            JsonResponse::send(['success' => false, 'error' => 'Invalid report type'], 400);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function resolveDateRange(string $range, string $fromParam, string $toParam): array
    {
        $now = time();

        if ($range === 'custom' && $fromParam !== '' && $toParam !== '') {
            return [date('Y-m-d 00:00:00', strtotime($fromParam)), date('Y-m-d 23:59:59', strtotime($toParam))];
        }

        if ($range === 'day') {
            return [date('Y-m-d 00:00:00', $now), date('Y-m-d 23:59:59', $now)];
        }

        if ($range === 'week') {
            return [date('Y-m-d 00:00:00', strtotime('-1 week', $now)), date('Y-m-d 23:59:59', $now)];
        }

        if ($range === 'year') {
            return [date('Y-m-d 00:00:00', strtotime('-1 year', $now)), date('Y-m-d 23:59:59', $now)];
        }

        return [date('Y-m-d 00:00:00', strtotime('-1 month', $now)), date('Y-m-d 23:59:59', $now)];
    }
}

