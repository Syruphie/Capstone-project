<?php
/**
 * POST: Reorder queue. Body: { "queue_id": 1, "new_position": 3 }
 * Auth: admin or technician.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Queue.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
if (!in_array($user->getRole(), ['administrator', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$queueId = isset($input['queue_id']) ? (int) $input['queue_id'] : 0;
$newPosition = isset($input['new_position']) ? (int) $input['new_position'] : 0;

if ($queueId < 1 || $newPosition < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid queue_id or new_position']);
    exit;
}

try {
    $queue = new Queue();
    $ok = $queue->updatePosition($queueId, $newPosition);
    if (!$ok) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Reorder failed']);
        exit;
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
