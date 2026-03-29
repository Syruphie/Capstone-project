<?php
declare(strict_types=1);

/**
 * POST actions for the admin panel. Behavior preserved from legacy admin.php.
 */
function admin_process_post(
    FrontendUser $user,
    FrontendOrder $order,
    FrontendEquipment $equipment,
    FrontendQueue $queue,
    FrontendSample $sample,
    FrontendEmail $email,
    string $userRole,
    int $userId
): string {
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $message;
    }

    if (isset($_POST['approve_order'])) {
        $orderId = intval($_POST['order_id']);
        $orderData = $order->getOrderWithCustomer($orderId);

        if ($order->approveOrder($orderId, $userId)) {
            $message = 'Order approved successfully';

            $samples = $sample->getSamplesByOrder($orderId);
            $equipmentList = $equipment->getAllEquipment(true);
            $priority = $orderData['priority'] ?? 'standard';

            if (!empty($equipmentList)) {
                $eq = $equipmentList[0];
                $eqId = (int) $eq['id'];
                $processingPer = (int) $eq['processing_time_per_sample'];
                $prepMins = 0;
                foreach ($samples as $s) {
                    $prepMins += (int) ($s['preparation_time'] ?? 0);
                }
                $testingMins = count($samples) * ($processingPer ?: 5);
                $durationMins = $prepMins + $testingMins;

                $lastEnd = $queue->getLastScheduledEnd($eqId);
                $base = $lastEnd ? strtotime($lastEnd) : time();
                $start = date('Y-m-d H:i:s', $base);
                $end = date('Y-m-d H:i:s', $base + $durationMins * 60);

                $queue->addToQueueScheduled($orderId, $eqId, $priority, $start, $end);
                $order->updateOrderStatus($orderId, 'payment_pending');
                $order->updateEstimatedCompletion($orderId, $end);
                $message .= ' - Queued and scheduled';
            }

            if ($orderData && !empty($orderData['customer_email'])) {
                $emailSent = $email->sendOrderApprovalNotification(
                    $orderData['customer_email'],
                    $orderData['customer_name'],
                    $orderData['order_number']
                );
                if ($emailSent) {
                    $message .= ' - Email notification sent to customer';
                }
            }
        }
    } elseif (isset($_POST['reject_order'])) {
        $orderId = intval($_POST['order_id']);
        $reason = trim($_POST['rejection_reason'] ?? 'No reason provided');
        $orderData = $order->getOrderWithCustomer($orderId);

        if ($order->rejectOrder($orderId, $reason)) {
            $message = 'Order rejected';

            if ($orderData && !empty($orderData['customer_email'])) {
                $emailSent = $email->sendOrderRejectionNotification(
                    $orderData['customer_email'],
                    $orderData['customer_name'],
                    $orderData['order_number'],
                    $reason
                );
                if ($emailSent) {
                    $message .= ' - Email notification sent to customer';
                }
            }
        }
    } elseif (isset($_POST['change_role']) && $userRole === 'administrator') {
        $targetUserId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $newRole = isset($_POST['role']) ? trim($_POST['role']) : '';
        if ($targetUserId && in_array($newRole, ['customer', 'technician', 'administrator'], true)) {
            if ($user->assignRole($targetUserId, $newRole)) {
                $message = 'User role updated.';
            } else {
                $message = 'Failed to update role.';
            }
        }
    }

    return $message;
}
