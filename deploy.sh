#!/bin/bash

# Скрипт для обновления и перезапуска приложения
# Выполняет: остановку docker compose, удаление образов, git pull, запуск docker compose

set -e  # Остановка при ошибке

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Начало процесса обновления ===${NC}"

# Шаг 1: Остановка docker compose
echo -e "${YELLOW}[1/4] Остановка docker compose...${NC}"
docker compose down || {
    echo -e "${RED}Ошибка при остановке docker compose${NC}"
    exit 1
}
echo -e "${GREEN}✓ Docker compose остановлен${NC}"

# Шаг 2: Удаление образа веб-сервера (БД не трогаем)
echo -e "${YELLOW}[2/4] Удаление старого образа веб-сервера...${NC}"
# Удаляем только локально собранные образы (веб-сервер), не трогая официальные образы (postgres) и volumes
# Флаг --rmi local удаляет только образы, собранные локально (build), не трогая образы из репозитория (postgres:15-alpine)
docker compose down --rmi local --remove-orphans 2>/dev/null || true
echo -e "${GREEN}✓ Старый образ веб-сервера удален (БД и volumes сохранены)${NC}"

# Шаг 3: Git pull
echo -e "${YELLOW}[3/4] Обновление кода из git...${NC}"
if ! git pull; then
    echo -e "${RED}Ошибка при выполнении git pull${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Код обновлен${NC}"

# Шаг 4: Запуск docker compose с пересборкой
echo -e "${YELLOW}[4/5] Запуск docker compose с пересборкой образов...${NC}"
docker compose up -d --build || {
    echo -e "${RED}Ошибка при запуске docker compose${NC}"
    exit 1
}
echo -e "${GREEN}✓ Docker compose запущен${NC}"

# Шаг 5: Исправление прав на директорию uploads
echo -e "${YELLOW}[5/5] Установка прав на директорию uploads...${NC}"
# Создаем директории если их нет
mkdir -p uploads/properties/thumbs

# Исправляем права в контейнере
docker compose exec -T web mkdir -p /var/www/html/uploads/properties/thumbs 2>/dev/null || true
docker compose exec -T web chown -R www-data:www-data /var/www/html/uploads 2>/dev/null || true
docker compose exec -T web chmod -R 775 /var/www/html/uploads 2>/dev/null || true

# Также исправляем права локально (на случай если используется volume)
chmod -R 775 uploads 2>/dev/null || true

echo -e "${GREEN}✓ Права установлены${NC}"

# Показываем статус
echo -e "\n${GREEN}=== Процесс обновления завершен ===${NC}"
echo -e "${YELLOW}Статус контейнеров:${NC}"
docker compose ps

echo -e "\n${GREEN}Логи можно посмотреть командой: docker compose logs -f${NC}"

