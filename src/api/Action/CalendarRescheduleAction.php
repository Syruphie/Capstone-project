<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class CalendarRescheduleAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $user = new FrontendUser();
        if (!in_array($user->getRole(), ['administrator', 'technician'], true)) {
            JsonResponse::send(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['success' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $queueId = isset($input['queue_id']) ? (int) $input['queue_id'] : 0;
        $start = isset($input['scheduled_start']) ? trim($input['scheduled_start']) : '';
        $end = isset($input['scheduled_end']) ? trim($input['scheduled_end']) : '';
        $note = isset($input['message']) ? trim($input['message']) : '';

        if ($queueId < 1 || $start === '' || $end === '') {
            JsonResponse::send(['success' => false, 'error' => 'Missing queue_id, scheduled_start, or scheduled_end'], 400);
            return;
        }

        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if ($startTs === false || $endTs === false || $endTs <= $startTs) {
            JsonResponse::send(['success' => false, 'error' => 'Invalid dates'], 400);
            return;
        }

        try {
            $queue = new FrontendQueue();
            $order = new FrontendOrder();
            $email = new FrontendEmail();
            $entry = $queue->getQueueById($queueId);
            if (!$entry) {
                JsonResponse::send(['success' => false, 'error' => 'Queue entry not found'], 404);
                return;
            }
            $orderId = (int) $entry['order_id'];

            $newStartStr = date('Y-m-d H:i:s', $startTs);
            $newEndStr = date('Y-m-d H:i:s', $endTs);

            $queue->updateSchedule($queueId, $newStartStr, $newEndStr);
            $order->updateEstimatedCompletion($orderId, $newEndStr);

            // Notify customer about schedule change (best-effort)
            $orderData = $order->getOrderWithCustomer($orderId);
            if ($orderData && !empty($orderData['customer_email'])) {
                $email->sendOrderScheduleUpdate(
                    $orderData['customer_email'],
                    $orderData['customer_name'],
                    $orderData['order_number'],
                    date('Y-m-d H:i', $startTs),
                    date('Y-m-d H:i', $endTs),
                    $note
                );
            }

            JsonResponse::send(['success' => true]);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

