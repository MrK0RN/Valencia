<?php

require_once __DIR__ . '/Model.php';

class Property extends Model
{
    // Константы статусов
    const STATUS_ACTIVE = 'active';
    const STATUS_SOLD = 'sold';
    const STATUS_HIDDEN = 'hidden';

    // Константы типов характеристик
    const FEATURE_BALCONY = 'balcony';
    const FEATURE_PARKING = 'parking';
    const FEATURE_ELEVATOR = 'elevator';
    const FEATURE_FURNISHED = 'furnished';
    const FEATURE_AIR_CONDITIONING = 'air_conditioning';
    const FEATURE_HEATING = 'heating';
    const FEATURE_POOL = 'pool';
    const FEATURE_GARDEN = 'garden';
    const FEATURE_TERRACE = 'terrace';
    const FEATURE_STORAGE = 'storage';

    /**
     * Переопределяем имя таблицы (если нужно)
     * 
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'properties';
    }

    /**
     * Валидация данных перед сохранением
     * 
     * @return bool true если данные валидны
     * @throws Exception При ошибке валидации
     */
    protected function validate(): bool
    {
        $errors = [];

        // Обязательные поля
        if (empty($this->attributes['title'])) {
            $errors[] = 'Название объекта обязательно';
        }

        if (empty($this->attributes['price']) || !is_numeric($this->attributes['price'])) {
            $errors[] = 'Цена должна быть числом';
        } elseif ($this->attributes['price'] < 0) {
            $errors[] = 'Цена не может быть отрицательной';
        }

        // Валидация статуса
        if (isset($this->attributes['status'])) {
            $validStatuses = [self::STATUS_ACTIVE, self::STATUS_SOLD, self::STATUS_HIDDEN];
            if (!in_array($this->attributes['status'], $validStatuses)) {
                $errors[] = 'Некорректный статус';
            }
        }

        // Валидация числовых полей
        if (isset($this->attributes['area_total']) && !is_numeric($this->attributes['area_total']) && $this->attributes['area_total'] !== null) {
            $errors[] = 'Общая площадь должна быть числом';
        }

        if (isset($this->attributes['area_living']) && !is_numeric($this->attributes['area_living']) && $this->attributes['area_living'] !== null) {
            $errors[] = 'Жилая площадь должна быть числом';
        }

        if (isset($this->attributes['rooms']) && !is_numeric($this->attributes['rooms']) && $this->attributes['rooms'] !== null) {
            $errors[] = 'Количество комнат должно быть числом';
        } elseif (isset($this->attributes['rooms']) && $this->attributes['rooms'] < 0) {
            $errors[] = 'Количество комнат не может быть отрицательным';
        }

        if (isset($this->attributes['floor']) && !is_numeric($this->attributes['floor']) && $this->attributes['floor'] !== null) {
            $errors[] = 'Этаж должен быть числом';
        }

        if (isset($this->attributes['total_floors']) && !is_numeric($this->attributes['total_floors']) && $this->attributes['total_floors'] !== null) {
            $errors[] = 'Общее количество этажей должно быть числом';
        }

        // Валидация URL видео
        if (!empty($this->attributes['video_url']) && !filter_var($this->attributes['video_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Некорректный URL видео';
        }

        // Валидация координат
        if (isset($this->attributes['lat']) && $this->attributes['lat'] !== null) {
            if (!is_numeric($this->attributes['lat']) || $this->attributes['lat'] < -90 || $this->attributes['lat'] > 90) {
                $errors[] = 'Некорректная широта (должна быть от -90 до 90)';
            }
        }

        if (isset($this->attributes['lng']) && $this->attributes['lng'] !== null) {
            if (!is_numeric($this->attributes['lng']) || $this->attributes['lng'] < -180 || $this->attributes['lng'] > 180) {
                $errors[] = 'Некорректная долгота (должна быть от -180 до 180)';
            }
        }

        if (!empty($errors)) {
            throw new Exception('Ошибки валидации: ' . implode(', ', $errors));
        }

        return true;
    }

    /**
     * Переопределяем save() для добавления валидации
     * 
     * @return bool
     */
    public function save(): bool
    {
        // Валидация перед сохранением
        $this->validate();

        // Устанавливаем updated_at
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');

        return parent::save();
    }

    /**
     * Получить все фотографии объекта
     * 
     * @return array Массив фотографий, отсортированных по sort_order
     */
    public function getPhotos(): array
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return [];
        }

        try {
            $db = self::getDb();
            $sql = "SELECT * FROM property_photos WHERE property_id = :property_id ORDER BY sort_order ASC, id ASC";
            return $db->fetchAll($sql, ['property_id' => $this->attributes['id']]);
        } catch (Exception $e) {
            error_log("Property::getPhotos() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить все характеристики объекта
     * 
     * @return array Массив характеристик
     */
    public function getFeatures(): array
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return [];
        }

        try {
            $db = self::getDb();
            $sql = "SELECT * FROM property_features WHERE property_id = :property_id ORDER BY feature_type ASC";
            return $db->fetchAll($sql, ['property_id' => $this->attributes['id']]);
        } catch (Exception $e) {
            error_log("Property::getFeatures() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Проверить, находится ли объект в избранном у пользователя
     * 
     * @param int $user_id ID пользователя
     * @return bool true если объект в избранном
     */
    public function isFavorite(int $user_id): bool
    {
        if (!isset($this->attributes['id']) || empty($this->attributes['id'])) {
            return false;
        }

        try {
            $db = self::getDb();
            $sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
            $result = $db->fetchOne($sql, [
                'user_id' => $user_id,
                'property_id' => $this->attributes['id']
            ]);
            
            return isset($result['count']) && $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Property::isFavorite() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Форматирование цены (250 000 €)
     * 
     * @return string Отформатированная цена
     */
    public function getFormattedPrice(): string
    {
        if (!isset($this->attributes['price']) || empty($this->attributes['price'])) {
            return 'Цена не указана';
        }

        $price = (float) $this->attributes['price'];
        $formatted = number_format($price, 0, ',', ' ');
        
        return $formatted . ' €';
    }

    /**
     * Вернуть адрес для показа (учитывая show_exact_address)
     * 
     * @return string Адрес для отображения
     */
    public function getAddressForDisplay(): string
    {
        $showExact = isset($this->attributes['show_exact_address']) && $this->attributes['show_exact_address'] === true;
        
        if ($showExact && !empty($this->attributes['address_full'])) {
            return $this->attributes['address_full'];
        }
        
        if (!empty($this->attributes['address_district'])) {
            return $this->attributes['address_district'];
        }
        
        if (!empty($this->attributes['address_full'])) {
            // Если точный адрес скрыт, можно показать только район или часть адреса
            return 'Район не указан';
        }
        
        return 'Адрес не указан';
    }

    /**
     * Получить статус объекта в читаемом виде
     * 
     * @return string
     */
    public function getStatusLabel(): string
    {
        $statusLabels = [
            self::STATUS_ACTIVE => 'Активен',
            self::STATUS_SOLD => 'Продан',
            self::STATUS_HIDDEN => 'Скрыт'
        ];

        $status = $this->attributes['status'] ?? self::STATUS_ACTIVE;
        return $statusLabels[$status] ?? 'Неизвестно';
    }

    /**
     * Проверить, является ли объект избранным
     * 
     * @return bool
     */
    public function isFeatured(): bool
    {
        return isset($this->attributes['featured']) && $this->attributes['featured'] === true;
    }

    /**
     * Получить полную информацию об объекте (с фото и характеристиками)
     * 
     * @return array
     */
    public function getFullInfo(): array
    {
        $info = $this->toArray();
        $info['photos'] = $this->getPhotos();
        $info['features'] = $this->getFeatures();
        $info['formatted_price'] = $this->getFormattedPrice();
        $info['address_display'] = $this->getAddressForDisplay();
        $info['status_label'] = $this->getStatusLabel();
        
        return $info;
    }

    /**
     * Получить все featured объекты
     * 
     * @return array Массив объектов Property с featured = true и status = 'active'
     */
    public static function getFeatured(): array
    {
        try {
            $db = self::getDb();
            $tableName = static::getTableName();
            $sql = "SELECT * FROM {$tableName} 
                    WHERE featured = true AND status = :status 
                    ORDER BY sort_order ASC, created_at DESC";
            $results = $db->fetchAll($sql, ['status' => self::STATUS_ACTIVE]);
            
            return array_map(function ($row) {
                return new static($row);
            }, $results);
        } catch (Exception $e) {
            error_log("Property::getFeatured() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить все активные объекты
     * 
     * @param int $limit Ограничение количества (0 = без ограничений)
     * @param int $offset Смещение для пагинации
     * @return array Массив объектов Property с status = 'active'
     */
    public static function getActive(int $limit = 0, int $offset = 0): array
    {
        try {
            $db = self::getDb();
            $tableName = static::getTableName();
            $sql = "SELECT * FROM {$tableName} 
                    WHERE status = :status 
                    ORDER BY sort_order ASC, created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }
            
            $params = ['status' => self::STATUS_ACTIVE];
            if ($limit > 0) {
                $params['limit'] = $limit;
                $params['offset'] = $offset;
            }
            
            $results = $db->fetchAll($sql, $params);
            
            return array_map(function ($row) {
                return new static($row);
            }, $results);
        } catch (Exception $e) {
            error_log("Property::getActive() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить общее количество активных объектов
     * 
     * @return int
     */
    public static function getActiveCount(): int
    {
        try {
            $db = self::getDb();
            $tableName = static::getTableName();
            $sql = "SELECT COUNT(*) as count FROM {$tableName} WHERE status = :status";
            $result = $db->fetchOne($sql, ['status' => self::STATUS_ACTIVE]);
            
            return isset($result['count']) ? (int) $result['count'] : 0;
        } catch (Exception $e) {
            error_log("Property::getActiveCount() error: " . $e->getMessage());
            return 0;
        }
    }
}

