<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/User.php';

class Auth
{
    protected static $db = null;

    /**
     * Получить экземпляр Database
     * 
     * @return Database
     */
    protected static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = new Database();
        }
        return self::$db;
    }

    /**
     * Аутентификация пользователя по email и паролю
     * 
     * @param string $email Email пользователя
     * @param string $password Пароль
     * @return User|null Объект пользователя или null
     */
    public static function attempt(string $email, string $password): ?User
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $userData = $db->fetchOne($sql, ['email' => $email]);

            if (!$userData) {
                return null;
            }

            // Проверка пароля
            if (empty($userData['password']) || !password_verify($password, $userData['password'])) {
                return null;
            }

            // Создаем объект пользователя
            $user = new User($userData);

            // Сохраняем в сессию
            Session::set('user_id', $user->id);
            Session::set('user_role', $user->role ?? 'user');
            Session::set('user_name', $user->name);

            // Обновляем last_login_at
            $db->query(
                "UPDATE users SET last_login_at = :last_login WHERE id = :id",
                ['last_login' => date('Y-m-d H:i:s'), 'id' => $user->id]
            );

            return $user;
        } catch (Exception $e) {
            error_log("Auth::attempt() error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверка, авторизован ли пользователь
     * 
     * @return bool
     */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Получить текущего пользователя
     * 
     * @return User|null
     */
    public static function user(): ?User
    {
        if (!self::check()) {
            return null;
        }

        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }

        try {
            return User::find($userId);
        } catch (Exception $e) {
            error_log("Auth::user() error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверка, является ли пользователь администратором
     * 
     * @return bool
     */
    public static function isAdmin(): bool
    {
        $role = Session::get('user_role');
        return $role === 'admin';
    }

    /**
     * Проверка, требуется ли роль администратора
     * 
     * @return bool
     */
    public static function requireAdmin(): bool
    {
        if (!self::check()) {
            return false;
        }

        if (!self::isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Выход пользователя
     */
    public static function logout(): void
    {
        Session::remove('user_id');
        Session::remove('user_role');
        Session::remove('user_name');
    }

    /**
     * Создание нового пользователя
     * 
     * @param array $data Данные пользователя
     * @return User|null
     */
    public static function register(array $data): ?User
    {
        try {
            // Хешируем пароль если есть
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Устанавливаем роль по умолчанию
            if (!isset($data['role'])) {
                $data['role'] = 'user';
            }

            $user = new User($data);
            $user->save();

            return $user;
        } catch (Exception $e) {
            error_log("Auth::register() error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Быстрая регистрация при добавлении в избранное
     * 
     * @param string $email Email
     * @param string $phone Телефон
     * @param string $name Имя
     * @return User|null
     */
    public static function quickRegister(string $email, string $phone, string $name): ?User
    {
        try {
            // Проверяем, существует ли пользователь
            $db = self::getDb();
            $existing = $db->fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);

            if ($existing) {
                return new User($existing);
            }

            // Создаем нового пользователя без пароля
            $data = [
                'email' => $email,
                'phone' => $phone,
                'name' => $name,
                'role' => 'user',
                'notification_enabled' => true
            ];

            return self::register($data);
        } catch (Exception $e) {
            error_log("Auth::quickRegister() error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Обновление пароля пользователя
     * 
     * @param int $userId ID пользователя
     * @param string $newPassword Новый пароль
     * @return bool
     */
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        try {
            $db = self::getDb();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->query(
                "UPDATE users SET password = :password WHERE id = :id",
                ['password' => $hashedPassword, 'id' => $userId]
            );
            return true;
        } catch (Exception $e) {
            error_log("Auth::updatePassword() error: " . $e->getMessage());
            return false;
        }
    }
}

