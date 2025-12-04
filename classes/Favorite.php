<?php

require_once __DIR__ . '/Database.php';

class Favorite
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
     * Переключить избранное (добавить, если нет, удалить если есть)
     * 
     * @param int $user_id ID пользователя
     * @param int $property_id ID объекта недвижимости
     * @return bool true если добавлено, false если удалено
     */
    public static function toggle(int $user_id, int $property_id): bool
    {
        try {
            $db = self::getDb();
            
            // Проверяем существование
            if (self::exists($user_id, $property_id)) {
                // Удаляем
                $sql = "DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
                $db->query($sql, [
                    'user_id' => $user_id,
                    'property_id' => $property_id
                ]);
                return false; // Удалено
            } else {
                // Добавляем
                $sql = "INSERT INTO favorites (user_id, property_id) VALUES (:user_id, :property_id)";
                $db->query($sql, [
                    'user_id' => $user_id,
                    'property_id' => $property_id
                ]);
                return true; // Добавлено
            }
        } catch (Exception $e) {
            error_log("Favorite::toggle() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Проверить существование записи в избранном
     * 
     * @param int $user_id ID пользователя
     * @param int $property_id ID объекта недвижимости
     * @return bool true если существует
     */
    public static function exists(int $user_id, int $property_id): bool
    {
        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
            $result = $db->fetchOne($sql, [
                'user_id' => $user_id,
                'property_id' => $property_id
            ]);
            
            return isset($result['count']) && $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Favorite::exists() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить количество избранных объектов у пользователя
     * 
     * @param int $user_id ID пользователя
     * @return int Количество избранных объектов
     */
    public static function count(int $user_id): int
    {
        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = :user_id";
            $result = $db->fetchOne($sql, ['user_id' => $user_id]);
            
            return isset($result['count']) ? (int) $result['count'] : 0;
        } catch (Exception $e) {
            error_log("Favorite::count() error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Получить все избранные объекты пользователя (массив ID)
     * 
     * @param int $user_id ID пользователя
     * @return array Массив ID объектов недвижимости
     */
    public static function getPropertyIds(int $user_id): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT property_id FROM favorites WHERE user_id = :user_id ORDER BY created_at DESC";
            $results = $db->fetchAll($sql, ['user_id' => $user_id]);
            
            return array_column($results, 'property_id');
        } catch (Exception $e) {
            error_log("Favorite::getPropertyIds() error: " . $e->getMessage());
            return [];
        }
    }
}

