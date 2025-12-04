<?php

require_once __DIR__ . '/Database.php';

abstract class Model
{
    protected $attributes = [];
    protected $original = [];
    protected static $db = null;

    /**
     * Конструктор модели
     * 
     * @param array $attributes Атрибуты модели
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->original = $attributes;
    }

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
     * Автоматическое определение имени таблицы по имени класса
     * User -> users, Property -> properties
     * 
     * @return string Имя таблицы
     */
    protected static function getTableName(): string
    {
        $className = static::class;
        $className = basename(str_replace('\\', '/', $className));
        
        // Преобразуем CamelCase в snake_case и добавляем 's' для множественного числа
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        
        // Если не заканчивается на 's', добавляем
        if (substr($tableName, -1) !== 's') {
            $tableName .= 's';
        }
        
        return $tableName;
    }

    /**
     * Получить все записи из таблицы
     * 
     * @return array Массив объектов модели
     */
    public static function all(): array
    {
        try {
            $tableName = static::getTableName();
            $sql = "SELECT * FROM {$tableName} ORDER BY id";
            $results = self::getDb()->fetchAll($sql);
            
            return array_map(function ($row) {
                return new static($row);
            }, $results);
        } catch (Exception $e) {
            error_log("Model::all() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Найти запись по ID
     * 
     * @param int $id ID записи
     * @return static|null Объект модели или null
     */
    public static function find(int $id): ?static
    {
        try {
            $tableName = static::getTableName();
            $sql = "SELECT * FROM {$tableName} WHERE id = :id LIMIT 1";
            $result = self::getDb()->fetchOne($sql, ['id' => $id]);
            
            return $result ? new static($result) : null;
        } catch (Exception $e) {
            error_log("Model::find() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Сохранить модель (вставить или обновить)
     * 
     * @return bool true при успехе
     */
    public function save(): bool
    {
        try {
            $tableName = static::getTableName();
            
            // Определяем, обновление или вставка
            $isUpdate = isset($this->attributes['id']) && !empty($this->attributes['id']);
            
            if ($isUpdate) {
                return $this->update();
            } else {
                return $this->insert();
            }
        } catch (Exception $e) {
            error_log("Model::save() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Вставить новую запись
     * 
     * @return bool true при успехе
     */
    protected function insert(): bool
    {
        $tableName = static::getTableName();
        $attributes = $this->getDirtyAttributes();
        
        // Удаляем id из атрибутов для вставки
        unset($attributes['id']);
        
        // Добавляем временные метки
        $attributes['created_at'] = date('Y-m-d H:i:s');
        $attributes['updated_at'] = date('Y-m-d H:i:s');
        
        // Сначала обрабатываем boolean поля - они должны быть строго true/false
        $booleanFields = ['show_exact_address', 'featured'];
        foreach ($booleanFields as $field) {
            if (isset($attributes[$field])) {
                $value = $attributes[$field];
                // Преобразуем любые значения в boolean
                if ($value === '' || $value === null || $value === '0' || $value === 0 || $value === false) {
                    $attributes[$field] = false;
                } elseif ($value === '1' || $value === 1 || $value === true || $value === 'true') {
                    $attributes[$field] = true;
                } else {
                    // Если значение не распознано, устанавливаем false
                    $attributes[$field] = false;
                }
            } else {
                // Если поле отсутствует, устанавливаем false
                $attributes[$field] = false;
            }
        }
        
        // Преобразуем пустые строки в null для необязательных текстовых полей
        foreach ($attributes as $key => $value) {
            // Пропускаем boolean поля и обязательные поля
            if (!in_array($key, array_merge($booleanFields, ['title', 'price', 'created_at', 'updated_at']))) {
                if ($value === '') {
                    $attributes[$key] = null;
                }
            }
        }
        
        // Проверяем обязательные поля перед вставкой
        if (empty($attributes['title']) || (is_string($attributes['title']) && trim($attributes['title']) === '')) {
            throw new Exception('Поле title обязательно для заполнения');
        }
        
        if (empty($attributes['price']) || !is_numeric($attributes['price'])) {
            throw new Exception('Поле price обязательно и должно быть числом');
        }
        
        if (empty($attributes)) {
            return false;
        }
        
        $columns = array_keys($attributes);
        $placeholders = array_map(function ($col) {
            return ':' . $col;
        }, $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s) RETURNING id",
            $tableName,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = self::getDb()->query($sql, $attributes);
        $result = $stmt->fetch();
        
        if ($result && isset($result['id'])) {
            $this->attributes['id'] = $result['id'];
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }

    /**
     * Обновить существующую запись
     * 
     * @return bool true при успехе
     */
    protected function update(): bool
    {
        $tableName = static::getTableName();
        $attributes = $this->getDirtyAttributes();
        
        // Удаляем id из атрибутов для обновления
        $id = $attributes['id'] ?? $this->attributes['id'] ?? null;
        unset($attributes['id']);
        
        // Добавляем updated_at
        $attributes['updated_at'] = date('Y-m-d H:i:s');
        
        // Обрабатываем boolean поля
        $booleanFields = ['show_exact_address', 'featured'];
        foreach ($booleanFields as $field) {
            if (isset($attributes[$field])) {
                $value = $attributes[$field];
                if ($value === '' || $value === null || $value === '0' || $value === 0 || $value === false) {
                    $attributes[$field] = false;
                } elseif ($value === '1' || $value === 1 || $value === true || $value === 'true') {
                    $attributes[$field] = true;
                } else {
                    $attributes[$field] = false;
                }
            }
        }
        
        // Преобразуем пустые строки в null для необязательных полей
        // НО не трогаем статус и другие важные поля
        $protectedFields = array_merge($booleanFields, ['title', 'price', 'updated_at', 'status', 'created_at']);
        foreach ($attributes as $key => $value) {
            if (!in_array($key, $protectedFields)) {
                if ($value === '') {
                    $attributes[$key] = null;
                }
            }
        }
        
        // Нормализуем статус (trim)
        if (isset($attributes['status']) && is_string($attributes['status'])) {
            $attributes['status'] = trim($attributes['status']);
        }
        
        if (empty($attributes) || !$id) {
            return false;
        }
        
        $setClause = [];
        foreach ($attributes as $key => $value) {
            $setClause[] = "{$key} = :{$key}";
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $tableName,
            implode(', ', $setClause)
        );
        
        $attributes['id'] = $id;
        self::getDb()->query($sql, $attributes);
        
        $this->original = $this->attributes;
        return true;
    }

    /**
     * Получить измененные атрибуты
     * 
     * @return array Массив измененных атрибутов
     */
    protected function getDirtyAttributes(): array
    {
        // Для новых объектов (без ID) возвращаем все атрибуты
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return $this->attributes;
        }
        
        // Для существующих объектов возвращаем только измененные
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            // Пропускаем id
            if ($key === 'id') {
                continue;
            }
            
            // Для boolean полей используем строгое сравнение с преобразованием
            if (in_array($key, ['show_exact_address', 'featured'])) {
                $currentBool = (bool)$value;
                $originalBool = isset($this->original[$key]) ? (bool)$this->original[$key] : false;
                if ($currentBool !== $originalBool) {
                    $dirty[$key] = $value;
                }
            }
            // Для остальных полей (включая status) обычное сравнение
            else {
                $originalValue = $this->original[$key] ?? null;
                // Нормализуем строки для сравнения (trim)
                if (is_string($value) && is_string($originalValue)) {
                    if (trim($value) !== trim($originalValue)) {
                        $dirty[$key] = $value;
                    }
                } elseif ($value !== $originalValue) {
                    $dirty[$key] = $value;
                }
            }
        }
        return $dirty;
    }

    /**
     * Удалить запись
     * 
     * @return bool true при успехе
     */
    public function delete(): bool
    {
        try {
            if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
                return false;
            }
            
            $tableName = static::getTableName();
            $sql = "DELETE FROM {$tableName} WHERE id = :id";
            self::getDb()->query($sql, ['id' => $this->attributes['id']]);
            
            $this->attributes = [];
            $this->original = [];
            return true;
        } catch (Exception $e) {
            error_log("Model::delete() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Преобразовать модель в массив
     * 
     * @return array Массив атрибутов
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Магический метод для получения свойства
     * 
     * @param string $name Имя свойства
     * @return mixed Значение свойства или null
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Магический метод для установки свойства
     * 
     * @param string $name Имя свойства
     * @param mixed $value Значение свойства
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Проверить существование свойства
     * 
     * @param string $name Имя свойства
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Удалить свойство
     * 
     * @param string $name Имя свойства
     */
    public function __unset(string $name): void
    {
        unset($this->attributes[$name]);
    }

    /**
     * Получить все атрибуты
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Установить атрибуты
     * 
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        // Обрабатываем boolean поля перед установкой
        $booleanFields = ['show_exact_address', 'featured'];
        foreach ($booleanFields as $field) {
            if (isset($attributes[$field])) {
                $value = $attributes[$field];
                if ($value === '' || $value === null || $value === '0' || $value === 0 || $value === false) {
                    $attributes[$field] = false;
                } elseif ($value === '1' || $value === 1 || $value === true || $value === 'true') {
                    $attributes[$field] = true;
                } else {
                    $attributes[$field] = false;
                }
            }
        }
        
        // Мерджим атрибуты
        $this->attributes = array_merge($this->attributes, $attributes);
    }
}

