<?php
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Session.php';

// Инициализация сессии
Session::start();

// Проверка авторизации (любой авторизованный пользователь)
if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

