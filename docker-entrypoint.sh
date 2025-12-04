#!/bin/bash
set -e

# Создаем директории для загрузки файлов если их нет
mkdir -p /var/www/html/uploads/properties/thumbs

# Устанавливаем правильные права на директорию uploads
chown -R www-data:www-data /var/www/html/uploads
chmod -R 777 /var/www/html/uploads

# Устанавливаем права на выполнение для скриптов (если нужно)
find /var/www/html -type f -name "*.sh" -exec chmod +x {} \; 2>/dev/null || true

# Запускаем оригинальную команду Apache
exec apache2-foreground

