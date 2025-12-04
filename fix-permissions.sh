#!/bin/bash

# Скрипт для исправления прав на директорию uploads

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Исправление прав на директорию uploads ===${NC}"

# Создаем директории если их нет
echo -e "${YELLOW}Создание директорий...${NC}"
mkdir -p uploads/properties/thumbs
echo -e "${GREEN}✓ Директории созданы${NC}"

# Исправляем права локально
echo -e "${YELLOW}Установка прав локально...${NC}"
chmod -R 775 uploads
echo -e "${GREEN}✓ Права установлены локально${NC}"

# Исправляем права в контейнере (если он запущен)
if docker compose ps | grep -q "Up"; then
    echo -e "${YELLOW}Установка прав в контейнере...${NC}"
    docker compose exec -T web mkdir -p /var/www/html/uploads/properties/thumbs 2>/dev/null || true
    docker compose exec -T web chown -R www-data:www-data /var/www/html/uploads 2>/dev/null || true
    docker compose exec -T web chmod -R 775 /var/www/html/uploads 2>/dev/null || true
    echo -e "${GREEN}✓ Права установлены в контейнере${NC}"
else
    echo -e "${YELLOW}Контейнер не запущен, права будут установлены при следующем запуске${NC}"
fi

echo -e "\n${GREEN}=== Готово ===${NC}"

