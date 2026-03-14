<?php
/**
 * POST: Add new equipment. Admin only.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Equipment.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn() || $user->getRole() !== 'administrator') {
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
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

try {
    $equipment = new Equipment();
    $id = $equipment->addEquipment($name, $equipmentType, $processingTime, $warmupTime, $breakInterval, $breakDuration, $dailyCapacity, $isAvailable, $lastMaintenance);
    if ($id) {
        $row = $equipment->getEquipmentById($id);
        echo json_encode(['success' => true, 'id' => (int) $id, 'equipment' => $row]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add equipment']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
