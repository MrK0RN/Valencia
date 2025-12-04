<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Session.php';
require_once __DIR__ . '/../classes/Favorite.php';

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

// Инициализация сессии
Session::start();

// Проверка авторизации
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Требуется авторизация',
        'requires_auth' => true
    ]);
    exit;
}

try {
    $user = Auth::user();
    if (!$user) {
        throw new Exception('Пользователь не найден');
    }
    
    // Получаем данные из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Валидация данных
    $propertyId = isset($input['property_id']) ? (int)$input['property_id'] : 0;
    
    if ($propertyId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Неверный ID объекта'
        ]);
        exit;
    }
    
    // Переключаем избранное
    $added = Favorite::toggle($user->id, $propertyId);
    
    echo json_encode([
        'success' => true,
        'added' => $added,
        'message' => $added ? 'Объект добавлен в избранное' : 'Объект удален из избранного'
    ]);
    
} catch (Exception $e) {
    error_log("Favorite toggle API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка при обновлении избранного. Пожалуйста, попробуйте позже.'
    ]);
}






