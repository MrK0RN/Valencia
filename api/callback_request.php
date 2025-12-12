<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/Database.php';

// Разрешаем CORS для всех источников (в продакшене лучше ограничить)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Только POST запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

try {
    // Получаем данные из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Валидация данных
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $propertyId = isset($input['property_id']) ? (int)$input['property_id'] : null;
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Имя обязательно для заполнения';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone)) {
        $errors[] = 'Неверный формат телефона';
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный формат email';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ошибки валидации',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Сохраняем запрос в базу данных
    $db = new Database();
    
    $sql = "INSERT INTO requests (type, property_id, name, phone, email, message, status, created_at, updated_at)
            VALUES (:type, :property_id, :name, :phone, :email, :message, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            RETURNING id";
    
    $params = [
        ':type' => 'callback',
        ':property_id' => $propertyId ?: null,
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email ?: null,
        ':message' => null,
        ':status' => 'new'
    ];
    
    $result = $db->fetchOne($sql, $params);
    
    if ($result && isset($result['id'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Запрос на звонок успешно отправлен. Мы свяжемся с вами в ближайшее время.',
            'request_id' => $result['id']
        ]);
    } else {
        throw new Exception('Не удалось сохранить запрос');
    }
    
} catch (Exception $e) {
    error_log("Callback request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка при отправке запроса. Пожалуйста, попробуйте позже.'
    ]);
}











