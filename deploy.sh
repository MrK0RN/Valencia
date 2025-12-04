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

# Шаг 2: Удаление образов
echo -e "${YELLOW}[2/4] Удаление старых образов...${NC}"
# Удаляем образы, связанные с проектом
docker compose down --rmi all --volumes --remove-orphans 2>/dev/null || true

# Также удаляем образы по имени проекта (если есть)
PROJECT_NAME=$(basename "$(pwd)" | tr '[:upper:]' '[:lower:]')
docker images | grep "${PROJECT_NAME}" | awk '{print $3}' | xargs -r docker rmi -f 2>/dev/null || true

# Удаляем образы valencia (если есть)
docker images | grep "valencia" | awk '{print $3}' | xargs -r docker rmi -f 2>/dev/null || true

echo -e "${GREEN}✓ Старые образы удалены${NC}"

# Шаг 3: Git pull
echo -e "${YELLOW}[3/4] Обновление кода из git...${NC}"
if ! git pull; then
    echo -e "${RED}Ошибка при выполнении git pull${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Код обновлен${NC}"

# Шаг 4: Запуск docker compose с пересборкой
echo -e "${YELLOW}[4/4] Запуск docker compose с пересборкой образов...${NC}"
docker compose up -d --build || {
    echo -e "${RED}Ошибка при запуске docker compose${NC}"
    exit 1
}
echo -e "${GREEN}✓ Docker compose запущен${NC}"

# Показываем статус
echo -e "\n${GREEN}=== Процесс обновления завершен ===${NC}"
echo -e "${YELLOW}Статус контейнеров:${NC}"
docker compose ps

echo -e "\n${GREEN}Логи можно посмотреть командой: docker compose logs -f${NC}"

