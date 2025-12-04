FROM php:8.1-apache

# Устанавливаем системные зависимости для PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Проверяем, что расширение установлено
RUN php -m | grep -i pdo_pgsql || (echo "ERROR: pdo_pgsql not installed" && exit 1)

# Включаем модули Apache
RUN a2enmod rewrite headers

# Базовая конфигурация Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN echo "<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Копируем файлы проекта
COPY . /var/www/html/

# Создаем директории для загрузки файлов
RUN mkdir -p /var/www/html/uploads/properties/thumbs

# Устанавливаем правильные права
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads

WORKDIR /var/www/html
EXPOSE 80

CMD ["apache2-foreground"]
