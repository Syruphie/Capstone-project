<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class OrderTypesAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $user = new FrontendUser();
        if ($user->getRole() !== 'administrator') {
            JsonResponse::send(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $orderType = new FrontendOrderType();
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
            if ($id) {
                $one = $orderType->getById($id);
                if ($one) {
                    JsonResponse::send(['success' => true, 'data' => $one]);
                } else {
                    JsonResponse::send(['success' => false, 'error' => 'Not found'], 404);
                }
            } else {
                $list = $orderType->getAll(false);
                JsonResponse::send(['success' => true, 'data' => $list]);
            }
            return;
        }

        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $sampleType = in_array($input['sample_type'] ?? '', ['ore', 'liquid'], true) ? $input['sample_type'] : 'ore';
            $cost = isset($input['cost']) ? (float) $input['cost'] : 0;
            if ($name === '') {
                JsonResponse::send(['success' => false, 'error' => 'Name is required']);
                return;
            }
            $id = $orderType->create($name, $description, $sampleType, $cost);
            if ($id) {
                JsonResponse::send(['success' => true, 'id' => (int) $id, 'order_type' => $orderType->getById($id)]);
            } else {
                JsonResponse::send(['success' => false, 'error' => 'Failed to create']);
            }
            return;
        }

        if ($method === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = isset($input['id']) ? (int) $input['id'] : 0;
            if (!$id) {
                JsonResponse::send(['success' => false, 'error' => 'ID required']);
                return;
            }
            $data = [];
            if (array_key_exists('name', $input)) $data['name'] = trim($input['name']);
            if (array_key_exists('description', $input)) $data['description'] = trim($input['description']);
            if (array_key_exists('sample_type', $input)) $data['sample_type'] = in_array($input['sample_type'], ['ore', 'liquid'], true) ? $input['sample_type'] : 'ore';
            if (array_key_exists('cost', $input)) $data['cost'] = (float) $input['cost'];
            if (array_key_exists('is_active', $input)) $data['is_active'] = !empty($input['is_active']);
            if (empty($data)) {
                JsonResponse::send(['success' => false, 'error' => 'No fields to update']);
                return;
            }
            if ($orderType->update($id, $data)) {
                JsonResponse::send(['success' => true, 'order_type' => $orderType->getById($id)]);
            } else {
                JsonResponse::send(['success' => false, 'error' => 'Update failed']);
            }
            return;
        }

        if ($method === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = isset($input['id']) ? (int) $input['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
            if (!$id) {
                JsonResponse::send(['success' => false, 'error' => 'ID required']);
                return;
            }
            if ($orderType->delete($id)) {
                JsonResponse::send(['success' => true]);
            } else {
                JsonResponse::send(['success' => false, 'error' => 'Delete failed']);
            }
            return;
        }

        JsonResponse::send(['success' => false, 'error' => 'Method not allowed'], 405);
    }
}

