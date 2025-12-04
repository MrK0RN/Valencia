<?php
/**
 * Cron script для проверки изменений цены и отправки уведомлений
 * 
 * Запускать через cron:
 * 0 * * * * /usr/bin/php /path/to/cron/check_price_changes.php
 * (каждый час)
 */

require_once __DIR__ . '/../classes/NotificationService.php';

// Устанавливаем таймзону
date_default_timezone_set('Europe/Madrid');

// Логирование
$logFile = __DIR__ . '/../logs/notifications.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    logMessage("Starting price change check...");
    
    $notificationsSent = NotificationService::checkPriceChanges();
    $soldNotifications = NotificationService::checkSoldProperties();
    
    logMessage("Price changes checked. Notifications sent: $notificationsSent");
    logMessage("Sold properties checked. Notifications sent: $soldNotifications");
    logMessage("Completed successfully.");
    
    echo "OK: $notificationsSent price notifications, $soldNotifications sold notifications\n";
} catch (Exception $e) {
    $errorMsg = "Error: " . $e->getMessage();
    logMessage($errorMsg);
    echo $errorMsg . "\n";
    exit(1);
}

