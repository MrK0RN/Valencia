# Настройка Google Maps API

Для работы карт в админ-панели и на страницах объектов недвижимости необходимо настроить API ключ Google Maps.

## Получение API ключа

1. Перейдите на [Google Cloud Console](https://console.cloud.google.com/)
2. Создайте новый проект или выберите существующий
3. Включите необходимые API:
   - **Maps JavaScript API** (обязательно)
   - **Places API** (опционально, для автодополнения адресов)
4. Перейдите в раздел **"Credentials"** (Учетные данные)
5. Нажмите **"Create Credentials"** → **"API Key"**
6. Скопируйте созданный API ключ

## Настройка API ключа

### Способ 1: Через файл конфигурации (рекомендуется)

Откройте файл `config/maps.php` и замените `YOUR_GOOGLE_MAPS_API_KEY` на ваш реальный API ключ:

```php
return [
    'google_maps_api_key' => 'ВАШ_API_КЛЮЧ_ЗДЕСЬ',
];
```

### Способ 2: Через переменную окружения

Вы можете установить переменную окружения `GOOGLE_MAPS_API_KEY`:

```bash
export GOOGLE_MAPS_API_KEY="ваш_api_ключ"
```

Или в файле `.env` (если используется):

```
GOOGLE_MAPS_API_KEY=ваш_api_ключ
```

## Ограничение API ключа (рекомендуется)

Для безопасности рекомендуется ограничить использование API ключа:

1. В Google Cloud Console перейдите в раздел **"Credentials"**
2. Нажмите на ваш API ключ
3. В разделе **"API restrictions"** выберите:
   - **Restrict key** → **Maps JavaScript API**
4. В разделе **"Application restrictions"** выберите:
   - **HTTP referrers (web sites)**
   - Добавьте ваши домены (например: `http://localhost:8080/*`, `https://yourdomain.com/*`)

## Проверка работы

После настройки API ключа:

1. Откройте админ-панель: `/admin/properties/create.php` или `/admin/properties/edit.php`
2. Нажмите кнопку **"Выбрать на карте"**
3. Должна открыться интерактивная карта Google Maps
4. На странице объекта (`/property.php?id=1`) также должна отображаться карта

## Устранение проблем

### Ошибка: "InvalidKeyMapError"

- Проверьте, что API ключ правильно скопирован в `config/maps.php`
- Убедитесь, что включен **Maps JavaScript API** в Google Cloud Console
- Проверьте, что API ключ не ограничен неправильными доменами

### Карта не загружается

- Откройте консоль браузера (F12) и проверьте ошибки
- Убедитесь, что API ключ активен в Google Cloud Console
- Проверьте, что включен **Maps JavaScript API**

### Ошибка: "This API key is not authorized"

- Убедитесь, что включен **Maps JavaScript API** в разделе "APIs & Services" → "Enabled APIs"
- Проверьте ограничения API ключа в разделе "Credentials"

## Стоимость

Google Maps предоставляет бесплатный кредит в размере $200 в месяц, что покрывает:
- До 28,000 загрузок карт в месяц
- До 100,000 запросов к Places API в месяц

Для большинства сайтов недвижимости этого более чем достаточно. Подробнее: [Google Maps Pricing](https://developers.google.com/maps/billing-and-pricing/pricing)

