<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class EquipmentAddAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $user = new FrontendUser();
        if ($user->getRole() !== 'administrator') {
            JsonResponse::send(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['success' => false, 'error' => 'Method not allowed'], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $name = trim($input['name'] ?? '');
        $equipmentType = trim($input['equipment_type'] ?? '');
        $processingTime = isset($input['processing_time_per_sample']) ? (int) $input['processing_time_per_sample'] : null;
        $warmupTime = isset($input['warmup_time']) ? (int) $input['warmup_time'] : 0;
        $breakInterval = isset($input['break_interval']) ? (int) $input['break_interval'] : 0;
        $breakDuration = isset($input['break_duration']) ? (int) $input['break_duration'] : 0;
        $dailyCapacity = isset($input['daily_capacity']) ? (int) $input['daily_capacity'] : 0;
        $isAvailable = !isset($input['is_available']) || !empty($input['is_available']);
        $lastMaintenance = null;
        if (!empty($input['last_maintenance'])) {
            $lastMaintenance = preg_match('/^\d{4}-\d{2}-\d{2}/', $input['last_maintenance']) ? $input['last_maintenance'] : null;
        }

        $errors = [];
        if ($name === '') $errors[] = 'Name is required';
        if ($equipmentType === '') $errors[] = 'Equipment type is required';
        if ($processingTime === null || $processingTime < 0) $errors[] = 'Processing time per sample must be a non-negative number';

        if (!empty($errors)) {
            JsonResponse::send(['success' => false, 'error' => implode(' ', $errors)], 400);
            return;
        }

        try {
            $equipment = new FrontendEquipment();
            $id = $equipment->addEquipment($name, $equipmentType, $processingTime, $warmupTime, $breakInterval, $breakDuration, $dailyCapacity, $isAvailable, $lastMaintenance);
            if ($id) {
                $row = $equipment->getEquipmentById($id);
                JsonResponse::send(['success' => true, 'id' => (int) $id, 'equipment' => $row]);
            } else {
                JsonResponse::send(['success' => false, 'error' => 'Failed to add equipment']);
            }
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

