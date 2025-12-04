<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../../classes/PropertyAdmin.php';
require_once __DIR__ . '/../../../classes/Session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!Session::validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sortedIds = $input['sorted_ids'] ?? $_POST['sorted_ids'] ?? [];

if (empty($sortedIds) || !is_array($sortedIds)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sorted_ids']);
    exit;
}

try {
    PropertyAdmin::updateSortOrder($sortedIds);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

