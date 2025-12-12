<?php
/**
 * Миграция: добавление поля repair_needed в таблицу properties
 * 
 * Этот скрипт можно запустить из командной строки:
 * php migrations/run_add_repair_needed.php
 * 
 * Или через браузер (если настроен веб-сервер):
 * http://localhost/migrations/run_add_repair_needed.php
 */

require_once __DIR__ . '/../classes/Database.php';

echo "Запуск миграции: добавление поля repair_needed...\n\n";

try {
    $db = new Database();
    
    // Проверяем, существует ли уже поле repair_needed
    $checkSql = "SELECT column_name 
                 FROM information_schema.columns 
                 WHERE table_name = 'properties' 
                 AND column_name = 'repair_needed'";
    
    $result = $db->fetchOne($checkSql);
    
    if ($result) {
        echo "✓ Поле repair_needed уже существует в таблице properties.\n";
        echo "Миграция не требуется.\n";
    } else {
        // Выполняем миграцию
        echo "Добавление поля repair_needed...\n";
        
        $migrationSql = "ALTER TABLE properties 
                        ADD COLUMN repair_needed BOOLEAN DEFAULT FALSE";
        
        $db->query($migrationSql);
        
        echo "✓ Поле repair_needed успешно добавлено в таблицу properties!\n";
        echo "Миграция завершена успешно.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Ошибка при выполнении миграции: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nГотово!\n";











