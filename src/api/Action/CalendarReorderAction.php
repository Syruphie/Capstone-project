<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class CalendarReorderAction
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
        $newPosition = isset($input['new_position']) ? (int) $input['new_position'] : 0;

        if ($queueId < 1 || $newPosition < 1) {
            JsonResponse::send(['success' => false, 'error' => 'Invalid queue_id or new_position'], 400);
            return;
        }

        try {
            $queue = new FrontendQueue();
            $ok = $queue->updatePosition($queueId, $newPosition);
            if (!$ok) {
                JsonResponse::send(['success' => false, 'error' => 'Reorder failed'], 400);
                return;
            }
            JsonResponse::send(['success' => true]);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

