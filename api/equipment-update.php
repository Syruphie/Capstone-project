<?php
/**
 * POST: Update equipment. Admin only.
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
$id = isset($input['id']) ? (int) $input['id'] : 0;

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
if ($id <= 0) $errors[] = 'Invalid equipment id';
if ($name === '') $errors[] = 'Name is required';
if ($equipmentType === '') $errors[] = 'Equipment type is required';
if ($processingTime === null || $processingTime < 0) $errors[] = 'Processing time per sample must be a non-negative number';
if ($warmupTime < 0) $errors[] = 'Warmup time must be a non-negative number';
if ($breakInterval < 0) $errors[] = 'Break interval must be a non-negative number';
if ($breakDuration < 0) $errors[] = 'Break duration must be a non-negative number';
if ($dailyCapacity < 0) $errors[] = 'Daily capacity must be a non-negative number';
if (strlen($name) > 20) $errors[] = 'Equipment name must be 20 characters or less';
if (strlen($equipmentType) > 20) $errors[] = 'Equipment type must be 20 characters or less';

// Offensive-word check
$offensiveWords = ['fuck','fucking','shit','bitch','bastard','cunt','dick',
    'pussy','nigger','nigga','faggot','fag','retard','whore','slut',
    'piss','cock','asshole','motherfucker','wanker','twat','prick'];
foreach ([$name, $equipmentType] as $fieldValue) {
    foreach ($offensiveWords as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $fieldValue)) {
            $errors[] = 'Offensive or inappropriate language is not allowed';
            break 2;
        }
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

try {
    $equipment = new Equipment();
    $existing = $equipment->getEquipmentById($id);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Equipment not found']);
        exit;
    }

    $ok = $equipment->updateEquipment($id, [
        'name' => $name,
        'equipment_type' => $equipmentType,
        'processing_time_per_sample' => $processingTime,
        'warmup_time' => $warmupTime,
        'break_interval' => $breakInterval,
        'break_duration' => $breakDuration,
        'daily_capacity' => $dailyCapacity,
        'is_available' => $isAvailable,
        'last_maintenance' => $lastMaintenance,
    ]);

    if ($ok) {
        $row = $equipment->getEquipmentById($id);
        echo json_encode(['success' => true, 'equipment' => $row]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update equipment']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

