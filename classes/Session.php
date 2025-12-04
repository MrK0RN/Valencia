<?php

class Session
{
    /**
     * Инициализация сессии
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Установить значение в сессию
     * 
     * @param string $key Ключ
     * @param mixed $value Значение
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Получить значение из сессии
     * 
     * @param string $key Ключ
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Проверить наличие ключа в сессии
     * 
     * @param string $key Ключ
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Удалить значение из сессии
     * 
     * @param string $key Ключ
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Очистить всю сессию
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Уничтожить сессию
     */
    public static function destroy(): void
    {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Генерация CSRF токена
     * 
     * @return string
     */
    public static function generateCsrfToken(): string
    {
        self::start();
        if (!self::has('csrf_token')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Проверка CSRF токена
     * 
     * @param string $token Токен для проверки
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        self::start();
        return self::has('csrf_token') && hash_equals($_SESSION['csrf_token'], $token);
    }
}

