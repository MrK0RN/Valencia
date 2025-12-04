<?php

require_once __DIR__ . '/PropertyAdmin.php';
require_once __DIR__ . '/ImageUploader.php';
require_once __DIR__ . '/Property.php';

class PropertyFormHandler
{
    /**
     * Обработка формы создания/редактирования объекта
     * 
     * @param array $data Данные формы
     * @param array $files Файлы ($_FILES)
     * @param int|null $propertyId ID объекта (null для создания)
     * @return Property Созданный/обновленный объект
     * @throws Exception При ошибке
     */
    public static function handleForm(array $data, array $files = [], ?int $propertyId = null): Property
    {
        // Проверка обязательных полей перед обработкой
        if (empty($data['title']) || trim($data['title']) === '') {
            throw new Exception('Название объекта обязательно для заполнения');
        }
        
        if (empty($data['price']) || !is_numeric($data['price']) || floatval($data['price']) <= 0) {
            throw new Exception('Цена должна быть положительным числом');
        }

        // Подготовка данных
        $title = trim($data['title']);
        $price = floatval($data['price']);
        
        $propertyData = [
            'title' => $title,
            'price' => $price,
            'address_full' => !empty($data['address_full']) ? trim($data['address_full']) : null,
            'address_district' => !empty($data['address_district']) ? trim($data['address_district']) : null,
            'show_exact_address' => isset($data['show_exact_address']) && $data['show_exact_address'] === '1' ? true : false,
            'area_total' => !empty($data['area_total']) && is_numeric($data['area_total']) ? floatval($data['area_total']) : null,
            'area_living' => !empty($data['area_living']) && is_numeric($data['area_living']) ? floatval($data['area_living']) : null,
            'area_kitchen' => !empty($data['area_kitchen']) && is_numeric($data['area_kitchen']) ? floatval($data['area_kitchen']) : null,
            'floor' => !empty($data['floor']) && is_numeric($data['floor']) ? intval($data['floor']) : null,
            'total_floors' => !empty($data['total_floors']) && is_numeric($data['total_floors']) ? intval($data['total_floors']) : null,
            'rooms' => !empty($data['rooms']) && is_numeric($data['rooms']) ? intval($data['rooms']) : null,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'video_url' => !empty($data['video_url']) ? trim($data['video_url']) : null,
            'lat' => !empty($data['lat']) && is_numeric($data['lat']) ? floatval($data['lat']) : null,
            'lng' => !empty($data['lng']) && is_numeric($data['lng']) ? floatval($data['lng']) : null,
            'status' => !empty($data['status']) ? trim($data['status']) : Property::STATUS_ACTIVE,
            'featured' => isset($data['featured']) && $data['featured'] === '1' ? true : false,
            'repair_needed' => isset($data['repair_needed']) && $data['repair_needed'] === '1' ? true : false,
        ];
        
        // Валидация статуса
        $validStatuses = [Property::STATUS_ACTIVE, Property::STATUS_SOLD, Property::STATUS_HIDDEN];
        if (!in_array($propertyData['status'], $validStatuses)) {
            $propertyData['status'] = Property::STATUS_ACTIVE;
        }

        // Обработка характеристик
        $features = [];
        $featureTypes = [
            Property::FEATURE_BALCONY,
            Property::FEATURE_PARKING,
            Property::FEATURE_ELEVATOR,
            Property::FEATURE_FURNISHED,
            Property::FEATURE_AIR_CONDITIONING,
            Property::FEATURE_HEATING,
            Property::FEATURE_POOL,
            Property::FEATURE_GARDEN,
            Property::FEATURE_TERRACE,
            Property::FEATURE_STORAGE,
        ];

        foreach ($featureTypes as $type) {
            if (isset($data['features'][$type]) && $data['features'][$type] === '1') {
                $features[] = ['type' => $type, 'value' => '1'];
            }
        }

        // Дополнительные характеристики из текстовых полей
        if (!empty($data['custom_features'])) {
            foreach ($data['custom_features'] as $custom) {
                if (!empty($custom['type']) && !empty($custom['value'])) {
                    $features[] = [
                        'type' => trim($custom['type']),
                        'value' => trim($custom['value'])
                    ];
                }
            }
        }

        // Обработка фотографий
        $photos = null; // null означает "не обновлять фотографии"
        if (!empty($files['photos']) && isset($files['photos']['name'])) {
            // Проверяем, есть ли хотя бы один загруженный файл
            $hasFiles = false;
            if (is_array($files['photos']['name'])) {
                // Множественные файлы
                foreach ($files['photos']['name'] as $name) {
                    if (!empty($name)) {
                        $hasFiles = true;
                        break;
                    }
                }
            } else {
                // Один файл
                $hasFiles = !empty($files['photos']['name']);
            }
            
            if ($hasFiles) {
                // Нормализуем массив файлов из $_FILES
                $normalizedPhotos = ImageUploader::normalizeFilesArray($files['photos']);
                // Передаем нормализованный массив (даже если он пустой после нормализации)
                $photos = $normalizedPhotos;
            }
        }

        // Создание или обновление
        if ($propertyId) {
            // Обновление: передаем null если нет новых файлов (не трогаем существующие)
            // или массив (пустой или с файлами) если нужно обновить
            $property = PropertyAdmin::updateProperty($propertyId, $propertyData, $photos, $features);
        } else {
            // Создание: передаем пустой массив если нет файлов, или массив с файлами
            $photosForCreate = $photos ?? [];
            $property = PropertyAdmin::createProperty($propertyData, $photosForCreate, $features);
        }

        return $property;
    }

    /**
     * Получить данные формы из объекта
     * 
     * @param Property $property Объект недвижимости
     * @return array Данные для формы
     */
    public static function getFormData(Property $property): array
    {
        $features = $property->getFeatures();
        $featuresMap = [];
        foreach ($features as $feature) {
            $featuresMap[$feature['feature_type']] = $feature['feature_value'];
        }

        return [
            'property' => $property,
            'features' => $featuresMap,
            'photos' => $property->getPhotos(),
        ];
    }
}

