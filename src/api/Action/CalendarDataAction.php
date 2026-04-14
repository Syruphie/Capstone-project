<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class CalendarDataAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $user = new FrontendUser();
        $role = $user->getRole();
        if (!in_array($role, ['administrator', 'technician'], true)) {
            JsonResponse::send(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        try {
            $queue = new FrontendQueue();
            $equipment = new FrontendEquipment();

            $queueEntries = $queue->getCalendarData();
            $equipmentList = $equipment->getAllEquipment();

            $from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d 00:00:00');
            $to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d 23:59:59', strtotime('+7 days'));

            $utilization = [];
            foreach ($equipmentList as $eq) {
                $slots = $queue->getQueueByEquipment((int) $eq['id'], $from, $to);
                $utilization[] = [
                    'id' => (int) $eq['id'],
                    'name' => $eq['name'],
                    'equipment_type' => $eq['equipment_type'],
                    'slots' => array_map(function ($s) {
                        return [
                            'queue_id' => (int) $s['id'],
                            'order_id' => (int) $s['order_id'],
                            'order_number' => $s['order_number'],
                            'scheduled_start' => $s['scheduled_start'],
                            'scheduled_end' => $s['scheduled_end'],
                            'order_status' => $s['order_status'],
                        ];
                    }, $slots),
                ];
            }

            $data = [
                'queue' => array_map(function ($r) {
                    return [
                        'queue_id' => (int) $r['queue_id'],
                        'order_id' => (int) $r['order_id'],
                        'order_number' => $r['order_number'],
                        'order_status' => $r['order_status'],
                        'priority' => $r['priority'],
                        'customer_name' => $r['customer_name'] ?? null,
                        'company_name' => $r['company_name'] ?? null,
                        'sample_types' => $r['sample_types'] ? explode(',', $r['sample_types']) : [],
                        'order_note' => $r['order_note'] ?? null,
                        'created_at' => $r['created_at'] ?? null,
                        'equipment_id' => $r['equipment_id'] ? (int) $r['equipment_id'] : null,
                        'equipment_name' => $r['equipment_name'] ?? null,
                        'scheduled_start' => $r['scheduled_start'],
                        'scheduled_end' => $r['scheduled_end'],
                        'estimated_completion' => $r['estimated_completion'],
                        'position' => (int) $r['position'],
                        'queue_type' => $r['queue_type'],
                    ];
                }, $queueEntries),
                'equipment' => $equipmentList,
                'utilization' => $utilization,
            ];

            JsonResponse::send(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

