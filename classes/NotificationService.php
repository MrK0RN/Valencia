<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Property.php';
require_once __DIR__ . '/User.php';

class NotificationService
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
     * Создать уведомление
     * 
     * @param int $userId ID пользователя
     * @param int|null $propertyId ID объекта (может быть null)
     * @param string $type Тип уведомления
     * @param string $title Заголовок
     * @param string $message Сообщение
     * @return bool
     */
    public static function createNotification(
        int $userId,
        ?int $propertyId,
        string $type,
        string $title,
        string $message = ''
    ): bool {
        try {
            $db = self::getDb();
            $sql = "INSERT INTO property_notifications 
                    (user_id, property_id, type, title, message) 
                    VALUES (:user_id, :property_id, :type, :title, :message)";
            $db->query($sql, [
                'user_id' => $userId,
                'property_id' => $propertyId,
                'type' => $type,
                'title' => $title,
                'message' => $message
            ]);
            return true;
        } catch (Exception $e) {
            error_log("NotificationService::createNotification() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Отправить уведомление о снижении цены
     * 
     * @param int $userId ID пользователя
     * @param Property $property Объект недвижимости
     * @param float $oldPrice Старая цена
     * @param float $newPrice Новая цена
     * @return bool
     */
    public static function notifyPriceDrop(int $userId, Property $property, float $oldPrice, float $newPrice): bool
    {
        // Проверяем, включены ли уведомления у пользователя
        $user = User::find($userId);
        if (!$user || !$user->notification_enabled) {
            return false;
        }

        $priceDiff = $oldPrice - $newPrice;
        $priceDiffPercent = ($priceDiff / $oldPrice) * 100;

        $title = "Снижение цены: " . $property->title;
        $message = sprintf(
            "Цена объекта снижена с %s € до %s € (на %.2f%%)",
            number_format($oldPrice, 0, ',', ' '),
            number_format($newPrice, 0, ',', ' '),
            $priceDiffPercent
        );

        return self::createNotification(
            $userId,
            $property->id,
            'price_drop',
            $title,
            $message
        );
    }

    /**
     * Отправить уведомление о продаже объекта
     * 
     * @param int $userId ID пользователя
     * @param Property $property Объект недвижимости
     * @return bool
     */
    public static function notifySold(int $userId, Property $property): bool
    {
        $user = User::find($userId);
        if (!$user || !$user->notification_enabled) {
            return false;
        }

        $title = "Объект продан: " . $property->title;
        $message = "Объект из вашего избранного был продан.";

        return self::createNotification(
            $userId,
            $property->id,
            'sold',
            $title,
            $message
        );
    }

    /**
     * Отправить уведомление о новом избранном объекте
     * 
     * @param int $userId ID пользователя
     * @param Property $property Объект недвижимости
     * @return bool
     */
    public static function notifyNewFeatured(int $userId, Property $property): bool
    {
        $user = User::find($userId);
        if (!$user || !$user->notification_enabled) {
            return false;
        }

        $title = "Новый избранный объект: " . $property->title;
        $message = "Добавлен новый избранный объект, который может вас заинтересовать.";

        return self::createNotification(
            $userId,
            $property->id,
            'new_featured',
            $title,
            $message
        );
    }

    /**
     * Отметить уведомление как прочитанное
     * 
     * @param int $notificationId ID уведомления
     * @param int $userId ID пользователя (для безопасности)
     * @return bool
     */
    public static function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $db = self::getDb();
            $sql = "UPDATE property_notifications 
                    SET is_read = true 
                    WHERE id = :id AND user_id = :user_id";
            $db->query($sql, [
                'id' => $notificationId,
                'user_id' => $userId
            ]);
            return true;
        } catch (Exception $e) {
            error_log("NotificationService::markAsRead() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить непрочитанные уведомления пользователя
     * 
     * @param int $userId ID пользователя
     * @return array
     */
    public static function getUnreadNotifications(int $userId): array
    {
        try {
            $db = self::getDb();
            $sql = "SELECT * FROM property_notifications 
                    WHERE user_id = :user_id AND is_read = false 
                    ORDER BY created_at DESC";
            return $db->fetchAll($sql, ['user_id' => $userId]);
        } catch (Exception $e) {
            error_log("NotificationService::getUnreadNotifications() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Проверка изменений цены и отправка уведомлений
     * Вызывается из cron скрипта
     * 
     * @return int Количество отправленных уведомлений
     */
    public static function checkPriceChanges(): int
    {
        $db = self::getDb();
        $notificationsSent = 0;

        try {
            // Получаем все активные подписки
            $alerts = $db->fetchAll(
                "SELECT pa.*, p.price as current_price, p.status
                 FROM price_alerts pa
                 INNER JOIN properties p ON pa.property_id = p.id
                 WHERE pa.is_active = true AND p.status = 'active'"
            );

            foreach ($alerts as $alert) {
                $currentPrice = floatval($alert['current_price']);
                $targetPrice = floatval($alert['target_price'] ?? 0);

                // Если цена упала до целевой или ниже
                if ($targetPrice > 0 && $currentPrice <= $targetPrice) {
                    $property = Property::find($alert['property_id']);
                    if ($property) {
                        // Получаем старую цену из истории
                        $priceHistory = json_decode($property->price_history ?? '[]', true);
                        $oldPrice = $currentPrice;
                        
                        if (!empty($priceHistory)) {
                            $lastEntry = end($priceHistory);
                            $oldPrice = floatval($lastEntry['price'] ?? $currentPrice);
                        }

                        if ($oldPrice > $currentPrice) {
                            self::notifyPriceDrop($alert['user_id'], $property, $oldPrice, $currentPrice);
                            $notificationsSent++;

                            // Деактивируем подписку
                            $db->query(
                                "UPDATE price_alerts SET is_active = false WHERE id = :id",
                                ['id' => $alert['id']]
                            );
                        }
                    }
                }
            }

            // Проверяем изменения цены для всех объектов
            $properties = Property::all();
            foreach ($properties as $property) {
                if ($property->status !== 'active') {
                    continue;
                }

                $priceHistory = json_decode($property->price_history ?? '[]', true);
                $currentPrice = floatval($property->price);

                // Если есть история, сравниваем с последней записью
                if (!empty($priceHistory)) {
                    $lastEntry = end($priceHistory);
                    $lastPrice = floatval($lastEntry['price'] ?? $currentPrice);

                    if ($lastPrice > $currentPrice) {
                        // Цена снизилась, уведомляем всех подписчиков
                        $subscribers = $db->fetchAll(
                            "SELECT user_id FROM price_alerts 
                             WHERE property_id = :property_id AND is_active = true",
                            ['property_id' => $property->id]
                        );

                        foreach ($subscribers as $subscriber) {
                            self::notifyPriceDrop($subscriber['user_id'], $property, $lastPrice, $currentPrice);
                            $notificationsSent++;
                        }
                    }
                }

                // Обновляем историю цены
                $priceHistory[] = [
                    'price' => $currentPrice,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                // Оставляем только последние 10 записей
                $priceHistory = array_slice($priceHistory, -10);

                $db->query(
                    "UPDATE properties SET price_history = :history WHERE id = :id",
                    [
                        'history' => json_encode($priceHistory),
                        'id' => $property->id
                    ]
                );
            }

        } catch (Exception $e) {
            error_log("NotificationService::checkPriceChanges() error: " . $e->getMessage());
        }

        return $notificationsSent;
    }

    /**
     * Проверка проданных объектов и отправка уведомлений
     * 
     * @return int Количество отправленных уведомлений
     */
    public static function checkSoldProperties(): int
    {
        $db = self::getDb();
        $notificationsSent = 0;

        try {
            // Находим объекты, которые недавно были проданы
            $soldProperties = $db->fetchAll(
                "SELECT p.* FROM properties p
                 WHERE p.status = 'sold' 
                 AND p.updated_at > NOW() - INTERVAL '1 day'"
            );

            foreach ($soldProperties as $propertyData) {
                $property = new Property($propertyData);

                // Находим всех пользователей, у которых этот объект в избранном
                $users = $db->fetchAll(
                    "SELECT DISTINCT user_id FROM favorites WHERE property_id = :property_id",
                    ['property_id' => $property->id]
                );

                foreach ($users as $userData) {
                    self::notifySold($userData['user_id'], $property);
                    $notificationsSent++;
                }
            }
        } catch (Exception $e) {
            error_log("NotificationService::checkSoldProperties() error: " . $e->getMessage());
        }

        return $notificationsSent;
    }
}

