<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Property.php';

class PropertyAdmin
{
    protected static $db = null;
    protected static $photosDir = 'uploads/properties/';
    protected static $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    protected static $maxFileSize = 5242880; // 5MB

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
     * Валидация данных объекта недвижимости
     * 
     * @param array $data Данные для валидации
     * @return array Массив ошибок (пустой если валидация прошла)
     */
    protected static function validatePropertyData(array $data): array
    {
        $errors = [];

        // Обязательные поля
        if (empty($data['title'])) {
            $errors[] = 'Название объекта обязательно';
        }

        if (empty($data['price']) || !is_numeric($data['price'])) {
            $errors[] = 'Цена должна быть числом';
        } elseif ($data['price'] < 0) {
            $errors[] = 'Цена не может быть отрицательной';
        }

        // Валидация статуса
        if (isset($data['status'])) {
            $validStatuses = [Property::STATUS_ACTIVE, Property::STATUS_SOLD, Property::STATUS_HIDDEN];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = 'Некорректный статус';
            }
        }

        // Валидация числовых полей
        if (isset($data['area_total']) && $data['area_total'] !== null && !is_numeric($data['area_total'])) {
            $errors[] = 'Общая площадь должна быть числом';
        }

        if (isset($data['rooms']) && $data['rooms'] !== null && !is_numeric($data['rooms'])) {
            $errors[] = 'Количество комнат должно быть числом';
        }

        // Валидация URL видео
        if (!empty($data['video_url']) && !filter_var($data['video_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Некорректный URL видео';
        }

        return $errors;
    }

    /**
     * Получить сообщение об ошибке загрузки
     * 
     * @param int $errorCode Код ошибки
     * @return string Сообщение об ошибке
     */
    protected static function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер, разрешенный в php.ini',
            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер, указанный в форме',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла была остановлена расширением PHP',
        ];

        return $messages[$errorCode] ?? 'Неизвестная ошибка (код: ' . $errorCode . ')';
    }

    /**
     * Загрузка фотографий
     * 
     * @param array $photos Массив файлов ($_FILES) или массив путей к файлам
     * @return array Массив путей к сохраненным файлам
     * @throws Exception При ошибке загрузки
     */
    protected static function uploadPhotos(array $photos): array
    {
        $uploadedPaths = [];
        $uploadDir = __DIR__ . '/../' . self::$photosDir;

        // Создаем директорию если не существует
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                throw new Exception('Не удалось создать директорию для загрузки: ' . $uploadDir);
            }
            // Пытаемся установить права на запись (если возможно)
            @chmod($uploadDir, 0775);
        }

        // Проверяем права на запись в директорию
        if (!is_writable($uploadDir)) {
            // Пытаемся исправить права
            @chmod($uploadDir, 0775);
            if (!is_writable($uploadDir)) {
                throw new Exception('Директория для загрузки недоступна для записи: ' . $uploadDir . '. Проверьте права доступа и владельца директории.');
            }
        }

        $skippedFiles = [];
        foreach ($photos as $index => $photo) {
            // Если это массив из $_FILES
            if (isset($photo['tmp_name']) && is_uploaded_file($photo['tmp_name'])) {
                // Проверка ошибок загрузки
                if (isset($photo['error']) && $photo['error'] !== UPLOAD_ERR_OK) {
                    $errorMsg = self::getUploadErrorMessage($photo['error']);
                    throw new Exception('Ошибка загрузки файла ' . ($photo['name'] ?? 'unknown') . ': ' . $errorMsg);
                }

                // Проверка существования временного файла
                if (!file_exists($photo['tmp_name'])) {
                    throw new Exception('Временный файл не найден: ' . $photo['tmp_name']);
                }

                // Проверка прав на запись в директорию
                if (!is_writable($uploadDir)) {
                    throw new Exception('Директория для загрузки недоступна для записи: ' . $uploadDir);
                }

                // Валидация типа файла
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $photo['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, self::$allowedImageTypes)) {
                    throw new Exception('Недопустимый тип файла: ' . $mimeType);
                }

                // Валидация размера
                if ($photo['size'] > self::$maxFileSize) {
                    throw new Exception('Файл слишком большой. Максимальный размер: 5MB');
                }

                // Генерируем уникальное имя файла
                $extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prop_', true) . '.' . $extension;
                $filepath = $uploadDir . $filename;

                // Перемещаем файл
                if (!move_uploaded_file($photo['tmp_name'], $filepath)) {
                    $errorDetails = [];
                    if (!file_exists($photo['tmp_name'])) {
                        $errorDetails[] = 'временный файл не существует';
                    }
                    if (!is_writable($uploadDir)) {
                        $errorDetails[] = 'директория недоступна для записи';
                    }
                    if (file_exists($filepath)) {
                        $errorDetails[] = 'файл назначения уже существует';
                    }
                    $details = !empty($errorDetails) ? ' (' . implode(', ', $errorDetails) . ')' : '';
                    throw new Exception('Ошибка загрузки файла: ' . $photo['name'] . $details);
                }

                $uploadedPaths[] = self::$photosDir . $filename;
            }
            // Если это строка (путь к файлу)
            elseif (is_string($photo) && file_exists($photo)) {
                $extension = pathinfo($photo, PATHINFO_EXTENSION);
                $filename = uniqid('prop_', true) . '.' . $extension;
                $filepath = $uploadDir . $filename;

                if (!copy($photo, $filepath)) {
                    throw new Exception('Ошибка копирования файла: ' . $photo);
                }

                $uploadedPaths[] = self::$photosDir . $filename;
            }
            else {
                // Файл не был обработан - логируем для отладки
                $fileName = is_array($photo) && isset($photo['name']) ? $photo['name'] : (is_string($photo) ? $photo : 'unknown');
                $skippedFiles[] = $fileName;
                error_log("PropertyAdmin::uploadPhotos() skipped file: " . $fileName . " (index: $index)");
            }
        }

        // Если были пропущены файлы, но мы ожидали их обработать, выводим предупреждение
        if (!empty($skippedFiles) && empty($uploadedPaths)) {
            throw new Exception('Не удалось обработать ни одного файла. Пропущенные файлы: ' . implode(', ', $skippedFiles));
        }

        return $uploadedPaths;
    }

    /**
     * Сохранение фотографий в БД
     * 
     * @param int $property_id ID объекта
     * @param array $photoPaths Массив путей к фотографиям
     * @return void
     */
    protected static function savePhotos(int $property_id, array $photoPaths): void
    {
        $db = self::getDb();
        $sortOrder = 0;

        foreach ($photoPaths as $path) {
            $sql = "INSERT INTO property_photos (property_id, image_path, sort_order) 
                    VALUES (:property_id, :image_path, :sort_order)";
            $db->query($sql, [
                'property_id' => $property_id,
                'image_path' => $path,
                'sort_order' => $sortOrder++
            ]);
        }
    }

    /**
     * Сохранение характеристик в БД
     * 
     * @param int $property_id ID объекта
     * @param array $features Массив характеристик [['type' => '...', 'value' => '...'], ...]
     * @return void
     */
    protected static function saveFeatures(int $property_id, array $features): void
    {
        $db = self::getDb();

        foreach ($features as $feature) {
            if (empty($feature['type']) || empty($feature['value'])) {
                continue;
            }

            $sql = "INSERT INTO property_features (property_id, feature_type, feature_value) 
                    VALUES (:property_id, :feature_type, :feature_value)";
            $db->query($sql, [
                'property_id' => $property_id,
                'feature_type' => $feature['type'],
                'feature_value' => $feature['value']
            ]);
        }
    }

    /**
     * Удаление фотографий объекта
     * 
     * @param int $property_id ID объекта
     * @return void
     */
    protected static function deletePhotos(int $property_id): void
    {
        $db = self::getDb();

        // Получаем пути к фотографиям
        $sql = "SELECT image_path FROM property_photos WHERE property_id = :property_id";
        $photos = $db->fetchAll($sql, ['property_id' => $property_id]);

        // Удаляем файлы
        foreach ($photos as $photo) {
            $filepath = __DIR__ . '/../' . $photo['image_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        // Удаляем записи из БД (CASCADE должен удалить автоматически, но для надежности)
        $sql = "DELETE FROM property_photos WHERE property_id = :property_id";
        $db->query($sql, ['property_id' => $property_id]);
    }

    /**
     * Удаление характеристик объекта
     * 
     * @param int $property_id ID объекта
     * @return void
     */
    protected static function deleteFeatures(int $property_id): void
    {
        $db = self::getDb();
        $sql = "DELETE FROM property_features WHERE property_id = :property_id";
        $db->query($sql, ['property_id' => $property_id]);
    }

    /**
     * Создание объекта недвижимости
     * 
     * @param array $data Данные объекта
     * @param array $photos Массив фотографий (файлы или пути)
     * @param array $features Массив характеристик
     * @return Property Созданный объект
     * @throws Exception При ошибке
     */
    public static function createProperty(array $data, array $photos = [], array $features = []): Property
    {
        $db = self::getDb();

        // Валидация данных
        $errors = self::validatePropertyData($data);
        if (!empty($errors)) {
            throw new Exception('Ошибки валидации: ' . implode(', ', $errors));
        }

        // Начинаем транзакцию
        $db->beginTransaction();

        try {
            // Подготавливаем данные (не добавляем created_at/updated_at, они добавятся автоматически)
            // Создаем объект
            $property = new Property($data);
            $property->save();
            $propertyId = $property->id;

            if (!$propertyId) {
                throw new Exception('Не удалось создать объект');
            }

            // Загружаем и сохраняем фотографии
            if (!empty($photos)) {
                $uploadedPaths = self::uploadPhotos($photos);
                self::savePhotos($propertyId, $uploadedPaths);
            }

            // Сохраняем характеристики
            if (!empty($features)) {
                self::saveFeatures($propertyId, $features);
            }

            // Подтверждаем транзакцию
            $db->commit();

            // Перезагружаем объект с полными данными
            return Property::find($propertyId);

        } catch (Exception $e) {
            // Откатываем транзакцию
            $db->rollback();
            error_log("PropertyAdmin::createProperty() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Обновление объекта недвижимости
     * 
     * @param int $id ID объекта
     * @param array $data Данные для обновления
     * @param array|null $photos Новые фотографии (null = не обновлять, [] = удалить все)
     * @param array|null $features Новые характеристики (null = не обновлять, [] = удалить все)
     * @return Property Обновленный объект
     * @throws Exception При ошибке
     */
    public static function updateProperty(int $id, array $data, ?array $photos = null, ?array $features = null): Property
    {
        $db = self::getDb();

        // Проверяем существование объекта
        $property = Property::find($id);
        if (!$property) {
            throw new Exception('Объект не найден');
        }

        // Валидация данных
        $errors = self::validatePropertyData($data);
        if (!empty($errors)) {
            throw new Exception('Ошибки валидации: ' . implode(', ', $errors));
        }

        // Начинаем транзакцию
        $db->beginTransaction();

        try {
            // Обновляем данные объекта
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Устанавливаем все атрибуты через setAttributes
            // Это обновит $this->attributes, но не $this->original
            // При вызове save() getDirtyAttributes() сравнит attributes с original
            // и найдет все изменения, включая статус
            $property->setAttributes($data);
            $property->save();

            // Обновляем фотографии
            if ($photos !== null) {
                // Удаляем старые фотографии
                self::deletePhotos($id);

                // Добавляем новые
                if (!empty($photos)) {
                    $uploadedPaths = self::uploadPhotos($photos);
                    self::savePhotos($id, $uploadedPaths);
                }
            }

            // Обновляем характеристики
            if ($features !== null) {
                // Удаляем старые характеристики
                self::deleteFeatures($id);

                // Добавляем новые
                if (!empty($features)) {
                    self::saveFeatures($id, $features);
                }
            }

            // Подтверждаем транзакцию
            $db->commit();

            // Перезагружаем объект
            return Property::find($id);

        } catch (Exception $e) {
            // Откатываем транзакцию
            $db->rollback();
            error_log("PropertyAdmin::updateProperty() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Удаление объекта недвижимости
     * 
     * @param int $id ID объекта
     * @return bool true при успехе
     * @throws Exception При ошибке
     */
    public static function deleteProperty(int $id): bool
    {
        $db = self::getDb();

        // Проверяем существование объекта
        $property = Property::find($id);
        if (!$property) {
            throw new Exception('Объект не найден');
        }

        // Начинаем транзакцию
        $db->beginTransaction();

        try {
            // Удаляем фотографии (файлы и записи)
            self::deletePhotos($id);

            // Удаляем характеристики
            self::deleteFeatures($id);

            // Удаляем объект (CASCADE удалит связанные записи автоматически)
            $property->delete();

            // Подтверждаем транзакцию
            $db->commit();

            return true;

        } catch (Exception $e) {
            // Откатываем транзакцию
            $db->rollback();
            error_log("PropertyAdmin::deleteProperty() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Обновление порядка сортировки объектов (drag-and-drop)
     * 
     * @param array $sorted_ids Массив ID объектов в новом порядке [1, 3, 2, 5, ...]
     * @return bool true при успехе
     * @throws Exception При ошибке
     */
    public static function updateSortOrder(array $sorted_ids): bool
    {
        $db = self::getDb();

        if (empty($sorted_ids)) {
            return false;
        }

        // Начинаем транзакцию
        $db->beginTransaction();

        try {
            foreach ($sorted_ids as $index => $id) {
                $sortOrder = $index + 1; // Начинаем с 1
                $sql = "UPDATE properties SET sort_order = :sort_order WHERE id = :id";
                $db->query($sql, [
                    'sort_order' => $sortOrder,
                    'id' => (int) $id
                ]);
            }

            // Подтверждаем транзакцию
            $db->commit();

            return true;

        } catch (Exception $e) {
            // Откатываем транзакцию
            $db->rollback();
            error_log("PropertyAdmin::updateSortOrder() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Переключение featured статуса
     * 
     * @param int $id ID объекта
     * @return bool Новое значение featured (true/false)
     * @throws Exception При ошибке
     */
    public static function toggleFeatured(int $id): bool
    {
        $db = self::getDb();

        // Проверяем существование объекта
        $property = Property::find($id);
        if (!$property) {
            throw new Exception('Объект не найден');
        }

        try {
            // Получаем текущее значение
            $currentFeatured = isset($property->featured) && $property->featured === true;
            $newFeatured = !$currentFeatured;

            // Обновляем
            $sql = "UPDATE properties SET featured = :featured WHERE id = :id";
            $db->query($sql, [
                'featured' => $newFeatured,
                'id' => $id
            ]);

            return $newFeatured;

        } catch (Exception $e) {
            error_log("PropertyAdmin::toggleFeatured() error: " . $e->getMessage());
            throw $e;
        }
    }
}

