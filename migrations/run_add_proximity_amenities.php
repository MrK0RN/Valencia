<?php
/**
 * Миграция: добавление полей близости к морю и метро
 *
 * Запуск:
 * php migrations/run_add_proximity_amenities.php
 */

require_once __DIR__ . '/../classes/Database.php';

echo "Запуск миграции: добавление полей sea/metro distance...\n\n";

try {
    $db = new Database();

    $columns = [
        'sea_distance_meters' => "ALTER TABLE properties ADD COLUMN sea_distance_meters INTEGER",
        'sea_distance_minutes' => "ALTER TABLE properties ADD COLUMN sea_distance_minutes INTEGER",
        'metro_distance_meters' => "ALTER TABLE properties ADD COLUMN metro_distance_meters INTEGER",
        'metro_distance_minutes' => "ALTER TABLE properties ADD COLUMN metro_distance_minutes INTEGER",
    ];

    foreach ($columns as $column => $sql) {
        $checkSql = "SELECT column_name 
                     FROM information_schema.columns 
                     WHERE table_name = 'properties' 
                       AND column_name = :column";

        $result = $db->fetchOne($checkSql, ['column' => $column]);

        if ($result) {
            echo "✓ Поле {$column} уже существует. Пропускаем.\n";
            continue;
        }

        echo "Добавляем поле {$column}...\n";
        $db->query($sql);
    }

    echo "\n✓ Миграция завершена успешно.\n";
} catch (Exception $e) {
    echo "✗ Ошибка при выполнении миграции: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nГотово!\n";
