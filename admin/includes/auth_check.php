<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Session.php';

// Инициализация сессии
Session::start();

// Проверка авторизации
if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

// Проверка прав администратора
if (!Auth::requireAdmin()) {
    header('Location: /admin/login.php?error=access_denied');
    exit;
}

