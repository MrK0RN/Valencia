<?php
/**
 * Скрипт для создания демо-администратора
 * 
 * Запустите этот скрипт один раз для создания демо-пользователя:
 * php setup_demo_admin.php
 */

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$db = new Database();

// Данные демо-администратора
$demoEmail = 'admin@demo.com';
$demoPassword = 'demo123';
$demoName = 'Демо Администратор';

echo "Создание демо-администратора...\n";

try {
    // Проверяем, существует ли уже пользователь
    $existing = $db->fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $demoEmail]);
    
    if ($existing) {
        // Обновляем пароль
        $hashedPassword = password_hash($demoPassword, PASSWORD_DEFAULT);
        $db->query(
            "UPDATE users SET password = :password, role = 'admin', name = :name WHERE email = :email",
            [
                'password' => $hashedPassword,
                'name' => $demoName,
                'email' => $demoEmail
            ]
        );
        echo "✓ Демо-администратор обновлен!\n";
    } else {
        // Создаем нового
        $hashedPassword = password_hash($demoPassword, PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO users (name, email, password, role, notification_enabled, created_at, updated_at)
             VALUES (:name, :email, :password, 'admin', true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
            [
                'name' => $demoName,
                'email' => $demoEmail,
                'password' => $hashedPassword
            ]
        );
        echo "✓ Демо-администратор создан!\n";
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "  ДЕМО-ДОСТУП К АДМИН-ПАНЕЛИ\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "  Email:    {$demoEmail}\n";
    echo "  Password: {$demoPassword}\n";
    echo "  URL:      http://localhost:8080/admin/login.php\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}

