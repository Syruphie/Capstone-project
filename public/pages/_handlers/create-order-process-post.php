<?php
declare(strict_types=1);

/**
 * Process create-order POST. Same behavior as legacy inline block in create-order.php.
 *
 * @param array<int, array<string, mixed>> $orderTypes
 * @return array{error: string, success: string, priority: ?string, unit: ?string, orderNote: ?string}
 */
function create_order_process_post(int $userId, array $orderTypes): array
{
    $error = '';
    $success = '';
    $priority = null;
    $unit = null;
    $orderNote = null;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_order'])) {
        return compact('error', 'success', 'priority', 'unit', 'orderNote');
    }

    $priority = htmlspecialchars($_POST['priority'] ?? 'standard');
    $orderTypeId = isset($_POST['order_type_id']) ? (int) $_POST['order_type_id'] : 0;
    $compoundName = htmlspecialchars(trim($_POST['compound_name'] ?? ''));
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit = htmlspecialchars(trim($_POST['unit'] ?? ''));
    $orderNoteRaw = trim((string) ($_POST['order_note'] ?? ''));
    $orderNote = $orderNoteRaw;

    if (empty($orderTypes)) {
        $error = 'No analysis types are currently available. Please contact support.';
        return compact('error', 'success', 'priority', 'unit', 'orderNote');
    }

    if (!$orderTypeId || empty($compoundName) || $quantity <= 0 || empty($unit)) {
        $error = 'Please fill in all required fields and select an analysis type from the catalogue.';
        return compact('error', 'success', 'priority', 'unit', 'orderNote');
    }

    try {
        $order = new FrontendOrder();
        $sample = new FrontendSample();

        $persistedOrderNote = $orderNoteRaw === '' ? null : $orderNoteRaw;
        $orderId = $order->createOrder($userId, $priority, $persistedOrderNote);

        if ($orderId) {
            $sampleId = $sample->addSample($orderId, $orderTypeId, $compoundName, $quantity, $unit);

            if ($sampleId) {
                $success = 'Order submitted successfully! Order ID: ' . $orderId;
            } else {
                $error = 'Failed to add sample to order. Please ensure the selected analysis type is still available.';
            }
        } else {
            $error = 'Failed to create order';
        }
    } catch (Throwable $e) {
        $error = 'Unable to submit order: ' . $e->getMessage();
    }

    return compact('error', 'success', 'priority', 'unit', 'orderNote');
}
