<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Property.php';

class PriceAlert extends Model
{
    /**
     * Переопределяем имя таблицы
     * 
     * @return string
     */
    protected static function getTableName(): string
    {
        return 'price_alerts';
    }

    /**
     * Создать подписку на уведомление о снижении цены
     * 
     * @param int $userId ID пользователя
     * @param int $propertyId ID объекта
     * @param float|null $targetPrice Целевая цена (null = любое снижение)
     * @return PriceAlert|null
     */
    public static function create(int $userId, int $propertyId, ?float $targetPrice = null): ?PriceAlert
    {
        try {
            $alert = new PriceAlert([
                'user_id' => $userId,
                'property_id' => $propertyId,
                'target_price' => $targetPrice,
                'is_active' => true
            ]);
            $alert->save();
            return $alert;
        } catch (Exception $e) {
            error_log("PriceAlert::create() error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Деактивировать подписку
     * 
     * @return bool
     */
    public function deactivate(): bool
    {
        try {
            $this->is_active = false;
            return $this->save();
        } catch (Exception $e) {
            error_log("PriceAlert::deactivate() error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить объект недвижимости
     * 
     * @return Property|null
     */
    public function getProperty(): ?Property
    {
        if (!isset($this->attributes['property_id'])) {
            return null;
        }
        return Property::find($this->attributes['property_id']);
    }
}

