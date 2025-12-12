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
    const FEATURE_AIRZONE = 'airzone';
    const FEATURE_CHILDREN_PLAY_AREA = 'children_play_area';
    const FEATURE_BUILT_IN_WARDROBES = 'built_in_wardrobes';
    const FEATURE_CLIMALIT = 'climalit_carpentry';
    const FEATURE_FIREPLACE = 'fireplace';
    const FEATURE_COUNTRY_CLUB = 'country_club';
    const FEATURE_OPEN_KITCHEN = 'open_kitchen';
    const FEATURE_WATER_SOFTENER = 'water_softener';
    const FEATURE_HOME_AUTOMATION = 'home_automation';
    const FEATURE_EXTERIOR = 'exterior';
    const FEATURE_PRIVATE_GARAGE = 'private_garage';
    const FEATURE_LAUNDRY_SPACE = 'laundry_space';
    const FEATURE_SOLAR_PANELS = 'solar_panels';
    const FEATURE_PARQUET = 'parquet';
    const FEATURE_COMMUNAL_POOL = 'communal_pool';
    const FEATURE_PRIVATE_POOL = 'private_pool';
    const FEATURE_PORCH = 'porch';
    const FEATURE_REINFORCED_DOOR = 'reinforced_door';
    const FEATURE_ELECTRIC_CHARGING_POINT = 'electric_charging_point';
    const FEATURE_AEROTHERMAL = 'aerothermal_system';
    const FEATURE_UNDERFLOOR_HEATING = 'underfloor_heating';
    const FEATURE_ENSUITE = 'ensuite_bathroom';
    const FEATURE_DRESSING_ROOM = 'dressing_room';
    const FEATURE_VIDEO_INTERCOM = 'video_intercom';
    const FEATURE_MOUNTAIN_VIEW = 'mountain_view';
    const FEATURE_COMMUNAL_AREA = 'communal_area';

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
     * Вернуть локализованное название
     *
     * @param string $locale ru|en
     * @return string
     */
    public function getLocalizedTitle(string $locale = 'ru'): string
    {
        $titleEn = trim($this->attributes['title_en'] ?? '');
        $titleRu = trim($this->attributes['title'] ?? '');

        if ($locale === 'en' && $titleEn !== '') {
            return $titleEn;
        }

        return $titleRu !== '' ? $titleRu : 'Без названия';
    }

    /**
     * Вернуть локализованное описание
     *
     * @param string $locale ru|en
     * @return string|null
     */
    public function getLocalizedDescription(string $locale = 'ru'): ?string
    {
        $descEn = trim($this->attributes['description_en'] ?? '');
        $descRu = trim($this->attributes['description'] ?? '');

        if ($locale === 'en' && $descEn !== '') {
            return $descEn;
        }

        return $descRu !== '' ? $descRu : null;
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

        $proximityFields = [
            'sea_distance_meters' => 'Расстояние до моря (м)',
            'sea_distance_minutes' => 'До моря (мин)',
            'metro_distance_meters' => 'Расстояние до метро (м)',
            'metro_distance_minutes' => 'До метро (мин)',
        ];

        foreach ($proximityFields as $field => $label) {
            if (isset($this->attributes[$field]) && $this->attributes[$field] !== null) {
                if (!is_numeric($this->attributes[$field])) {
                    $errors[] = $label . ' должно быть числом';
                } elseif ($this->attributes[$field] < 0) {
                    $errors[] = $label . ' не может быть отрицательным';
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception('Ошибки валидации: ' . implode(', ', $errors));
        }

        return true;
    }

    /**
     * Карта доступных характеристик (удобств) с локализацией
     *
     * @param string $locale ru|en
     * @return array<string,string>
     */
    public static function getFeatureLabels(string $locale = 'ru'): array
    {
        $ru = [
            self::FEATURE_AIR_CONDITIONING => 'Кондиционирование воздуха',
            self::FEATURE_AIRZONE => 'Система Airzone',
            self::FEATURE_CHILDREN_PLAY_AREA => 'Детская игровая площадка',
            self::FEATURE_BUILT_IN_WARDROBES => 'Встроенные шкафы',
            self::FEATURE_ELEVATOR => 'Лифт',
            self::FEATURE_CLIMALIT => 'Столярка Climalit (двойные стеклопакеты)',
            self::FEATURE_FIREPLACE => 'Камин',
            self::FEATURE_COUNTRY_CLUB => 'Кантри-клуб / клубная зона',
            self::FEATURE_OPEN_KITCHEN => 'Кухня, объединённая с гостиной',
            self::FEATURE_WATER_SOFTENER => 'Система очистки/смягчения воды',
            self::FEATURE_HOME_AUTOMATION => 'Умный дом',
            self::FEATURE_EXTERIOR => 'Внешняя отделка / фасадные решения',
            self::FEATURE_PRIVATE_GARAGE => 'Частный гараж',
            self::FEATURE_GARDEN => 'Частный сад',
            self::FEATURE_LAUNDRY_SPACE => 'Постирочная зона',
            self::FEATURE_SOLAR_PANELS => 'Солнечные панели',
            self::FEATURE_PARQUET => 'Паркет',
            self::FEATURE_COMMUNAL_POOL => 'Общий бассейн',
            self::FEATURE_PRIVATE_POOL => 'Частный бассейн',
            self::FEATURE_PORCH => 'Крытая терраса / портик',
            self::FEATURE_REINFORCED_DOOR => 'Усиленная входная дверь',
            self::FEATURE_ELECTRIC_CHARGING_POINT => 'Точка для зарядки электромобиля',
            self::FEATURE_AEROTHERMAL => 'Аэротермальная система',
            self::FEATURE_UNDERFLOOR_HEATING => 'Тёплый пол',
            self::FEATURE_ENSUITE => 'Главная спальня с санузлом (suite)',
            self::FEATURE_TERRACE => 'Терраса',
            self::FEATURE_DRESSING_ROOM => 'Гардеробная',
            self::FEATURE_VIDEO_INTERCOM => 'Видеодомофон',
            self::FEATURE_MOUNTAIN_VIEW => 'Вид на горы',
            self::FEATURE_COMMUNAL_AREA => 'Общая территория / коммунальная зона',
            self::FEATURE_BALCONY => 'Балкон',
            self::FEATURE_PARKING => 'Парковка',
            self::FEATURE_FURNISHED => 'Мебель',
            self::FEATURE_HEATING => 'Отопление',
            self::FEATURE_POOL => 'Бассейн (старый тип)',
            self::FEATURE_STORAGE => 'Кладовая',
        ];

        $en = [
            self::FEATURE_AIR_CONDITIONING => 'Air conditioning',
            self::FEATURE_AIRZONE => 'Airzone system',
            self::FEATURE_CHILDREN_PLAY_AREA => 'Children’s play area',
            self::FEATURE_BUILT_IN_WARDROBES => 'Built-in wardrobes',
            self::FEATURE_ELEVATOR => 'Lift',
            self::FEATURE_CLIMALIT => 'Climalit carpentry (double glazing)',
            self::FEATURE_FIREPLACE => 'Fireplace',
            self::FEATURE_COUNTRY_CLUB => 'Country club / club area',
            self::FEATURE_OPEN_KITCHEN => 'Kitchen open to the living room',
            self::FEATURE_WATER_SOFTENER => 'Water softener',
            self::FEATURE_HOME_AUTOMATION => 'Home automation',
            self::FEATURE_EXTERIOR => 'Exterior / facade solutions',
            self::FEATURE_PRIVATE_GARAGE => 'Private garage',
            self::FEATURE_GARDEN => 'Private garden',
            self::FEATURE_LAUNDRY_SPACE => 'Laundry space',
            self::FEATURE_SOLAR_PANELS => 'Solar panels',
            self::FEATURE_PARQUET => 'Parquet',
            self::FEATURE_COMMUNAL_POOL => 'Communal swimming pool',
            self::FEATURE_PRIVATE_POOL => 'Private pool',
            self::FEATURE_PORCH => 'Porch / covered terrace',
            self::FEATURE_REINFORCED_DOOR => 'Reinforced door',
            self::FEATURE_ELECTRIC_CHARGING_POINT => 'Electric recharging point',
            self::FEATURE_AEROTHERMAL => 'Aerothermal system',
            self::FEATURE_UNDERFLOOR_HEATING => 'Under-floor heating',
            self::FEATURE_ENSUITE => 'Suite with bathroom',
            self::FEATURE_TERRACE => 'Terrace',
            self::FEATURE_DRESSING_ROOM => 'Dressing room',
            self::FEATURE_VIDEO_INTERCOM => 'Video intercom system',
            self::FEATURE_MOUNTAIN_VIEW => 'Mountain view',
            self::FEATURE_COMMUNAL_AREA => 'Communal area',
            self::FEATURE_BALCONY => 'Balcony',
            self::FEATURE_PARKING => 'Parking',
            self::FEATURE_FURNISHED => 'Furnished',
            self::FEATURE_HEATING => 'Heating',
            self::FEATURE_POOL => 'Pool (legacy)',
            self::FEATURE_STORAGE => 'Storage room',
        ];

        return $locale === 'en' ? $en : $ru;
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

