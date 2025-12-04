<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Session.php';

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

try {
    // Получаем данные из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Валидация данных
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный формат email';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
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
    
    // Попытка авторизации
    $user = Auth::attempt($email, $password);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'message' => 'Успешный вход',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Неверный email или пароль',
            'errors' => ['Неверный email или пароль']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка при входе. Пожалуйста, попробуйте позже.'
    ]);
}




