<?php
/**
 * Order types CRUD. Admin only.
 * GET: list all
 * POST: create (name, description, cost)
 * PUT: update (id, name?, description?, cost?, is_active?)
 * DELETE: delete (id)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/OrderType.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn() || $user->getRole() !== 'administrator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$orderType = new OrderType();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    if ($id) {
        $one = $orderType->getById($id);
        if ($one) {
            echo json_encode(['success' => true, 'data' => $one]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Not found']);
        }
    } else {
        $list = $orderType->getAll(false);
        echo json_encode(['success' => true, 'data' => $list]);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $name = trim($input['name'] ?? '');
    $description = trim($input['description'] ?? '');
    $sampleType = in_array($input['sample_type'] ?? '', ['ore', 'liquid'], true) ? $input['sample_type'] : 'ore';
    $cost = isset($input['cost']) ? (float) $input['cost'] : 0;
    if ($name === '') {
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        exit;
    }
    $id = $orderType->create($name, $description, $sampleType, $cost);
    if ($id) {
        echo json_encode(['success' => true, 'id' => (int) $id, 'order_type' => $orderType->getById($id)]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create']);
    }
    exit;
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($input['id']) ? (int) $input['id'] : 0;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID required']);
        exit;
    }
    $data = [];
    if (array_key_exists('name', $input)) $data['name'] = trim($input['name']);
    if (array_key_exists('description', $input)) $data['description'] = trim($input['description']);
    if (array_key_exists('sample_type', $input)) $data['sample_type'] = in_array($input['sample_type'], ['ore', 'liquid'], true) ? $input['sample_type'] : 'ore';
    if (array_key_exists('cost', $input)) $data['cost'] = (float) $input['cost'];
    if (array_key_exists('is_active', $input)) $data['is_active'] = !empty($input['is_active']);
    if (empty($data)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }
    if ($orderType->update($id, $data)) {
        echo json_encode(['success' => true, 'order_type' => $orderType->getById($id)]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
    exit;
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($input['id']) ? (int) $input['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID required']);
        exit;
    }
    if ($orderType->delete($id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Delete failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
