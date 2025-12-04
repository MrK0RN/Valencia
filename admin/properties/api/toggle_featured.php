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
$propertyId = intval($input['id'] ?? $_POST['id'] ?? 0);

if (!$propertyId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid property ID']);
    exit;
}

try {
    $newFeatured = PropertyAdmin::toggleFeatured($propertyId);
    echo json_encode(['success' => true, 'featured' => $newFeatured]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

