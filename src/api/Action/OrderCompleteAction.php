<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class OrderCompleteAction
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

        $queueId = isset($_POST['queue_id']) ? (int) $_POST['queue_id'] : 0;
        $note = isset($_POST['message']) ? trim($_POST['message']) : '';

        if ($queueId < 1) {
            JsonResponse::send(['success' => false, 'error' => 'Missing or invalid queue_id'], 400);
            return;
        }

        // Handle optional attachment
        $attachmentPath = null;
        $attachmentName = null;
        if (!empty($_FILES['attachment']) && isset($_FILES['attachment']['error']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $attachmentPath = $_FILES['attachment']['tmp_name'];
            $attachmentName = $_FILES['attachment']['name'];
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

            // Mark queue entry as completed (actual_end)
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("UPDATE queue SET actual_end = NOW() WHERE id = ?");
            $stmt->execute([$queueId]);

            // Update order status to results_available and set completed_at
            $order->updateOrderStatus($orderId, 'results_available');
            $pdo->prepare("UPDATE orders SET completed_at = NOW() WHERE id = ?")->execute([$orderId]);

            // Notify customer about completion (best-effort)
            $orderData = $order->getOrderWithCustomer($orderId);
            if ($orderData && !empty($orderData['customer_email'])) {
                $email->sendOrderCompletedNotification(
                    $orderData['customer_email'],
                    $orderData['customer_name'],
                    $orderData['order_number'],
                    $note,
                    $attachmentPath,
                    $attachmentName
                );
            }

            JsonResponse::send(['success' => true]);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

