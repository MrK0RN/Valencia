<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Property.php';

class User extends Model
{
    /**
     * Переопределяем имя таблицы
     * 
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'users';
    }

    /**
     * Получить все избранные объекты пользователя
     * 
     * @return array Массив объектов Property
     */
    public function getFavorites(): array
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return [];
        }

        try {
            $db = self::getDb();
            $sql = "SELECT p.* 
                    FROM properties p
                    INNER JOIN favorites f ON p.id = f.property_id
                    WHERE f.user_id = :user_id
                    ORDER BY f.created_at DESC";
            
            $results = $db->fetchAll($sql, ['user_id' => $this->attributes['id']]);
            
            return array_map(function ($row) {
                return new Property($row);
            }, $results);
        } catch (Exception $e) {
            error_log("User::getFavorites() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Добавить объект в избранное
     * 
     * @param int $property_id ID объекта недвижимости
     * @return bool true при успехе
     */
    public function addFavorite(int $property_id): bool
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return false;
        }

        try {
            // Проверяем, не добавлен ли уже
            if (Favorite::exists($this->attributes['id'], $property_id)) {
                return true; // Уже в избранном
            }

            $db = self::getDb();
            $sql = "INSERT INTO favorites (user_id, property_id) VALUES (:user_id, :property_id)";
            $db->query($sql, [
                'user_id' => $this->attributes['id'],
                'property_id' => $property_id
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("User::addFavorite() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Удалить объект из избранного
     * 
     * @param int $property_id ID объекта недвижимости
     * @return bool true при успехе
     */
    public function removeFavorite(int $property_id): bool
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return false;
        }

        try {
            $db = self::getDb();
            $sql = "DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
            $db->query($sql, [
                'user_id' => $this->attributes['id'],
                'property_id' => $property_id
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("User::removeFavorite() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверить, находится ли объект в избранном
     * 
     * @param int $property_id ID объекта недвижимости
     * @return bool
     */
    public function hasFavorite(int $property_id): bool
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return false;
        }

        return Favorite::exists($this->attributes['id'], $property_id);
    }
}

