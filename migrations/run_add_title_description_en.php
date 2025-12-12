<?php
/**
 * Миграция: добавление английских полей title_en и description_en
 *
 * Запуск:
 * php migrations/run_add_title_description_en.php
 */

require_once __DIR__ . '/../classes/Database.php';

echo "Запуск миграции: добавление полей title_en и description_en...\n\n";

try {
    $db = new Database();

    $columns = [
        'title_en' => "ALTER TABLE properties ADD COLUMN title_en VARCHAR(255)",
        'description_en' => "ALTER TABLE properties ADD COLUMN description_en TEXT",
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

