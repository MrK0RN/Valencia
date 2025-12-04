<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = 'Личный кабинет';
$user = Auth::user();

$db = new Database();

// Статистика
$favoritesCount = count($user->getFavorites());
$viewsCount = $db->fetchOne(
    "SELECT COUNT(*) as count FROM property_view_history WHERE user_id = :user_id",
    ['user_id' => $user->id]
)['count'] ?? 0;

$alertsCount = $db->fetchOne(
    "SELECT COUNT(*) as count FROM price_alerts WHERE user_id = :user_id AND is_active = true",
    ['user_id' => $user->id]
)['count'] ?? 0;

$unreadNotifications = $db->fetchOne(
    "SELECT COUNT(*) as count FROM property_notifications WHERE user_id = :user_id AND is_read = false",
    ['user_id' => $user->id]
)['count'] ?? 0;

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <div class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Личный кабинет</h1>
                </div>
                <div class="header-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($user->name); ?>
                    </span>
                    <a href="/admin/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </header>
            <main class="admin-main">
                <div class="dashboard">
                    <div class="stats-grid">
                        <a href="/account/favorites.php" class="stat-card" style="text-decoration: none; color: inherit;">
                            <div class="stat-icon" style="background: #e74c3c;">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $favoritesCount; ?></h3>
                                <p>Избранное</p>
                            </div>
                        </a>

                        <a href="/account/history.php" class="stat-card" style="text-decoration: none; color: inherit;">
                            <div class="stat-icon" style="background: #3498db;">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $viewsCount; ?></h3>
                                <p>Просмотров</p>
                            </div>
                        </a>

                        <div class="stat-card">
                            <div class="stat-icon" style="background: #f39c12;">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $alertsCount; ?></h3>
                                <p>Подписки</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon" style="background: #9b59b6;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $unreadNotifications; ?></h3>
                                <p>Новых уведомлений</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <div class="dashboard-card">
                            <h2>Быстрые ссылки</h2>
                            <ul style="list-style: none; padding: 0;">
                                <li style="margin-bottom: 10px;">
                                    <a href="/account/favorites.php" class="btn btn-link">
                                        <i class="fas fa-heart"></i> Мои избранные объекты
                                    </a>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <a href="/account/history.php" class="btn btn-link">
                                        <i class="fas fa-history"></i> История просмотров
                                    </a>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <a href="/account/settings.php" class="btn btn-link">
                                        <i class="fas fa-cog"></i> Настройки
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="dashboard-card">
                            <h2>Информация</h2>
                            <p style="line-height: 1.8;">
                                <strong>Мы не собираемся беспокоить вас.</strong><br>
                                Вы можете управлять уведомлениями в настройках. 
                                Мы будем отправлять уведомления только о важных событиях, 
                                связанных с объектами из вашего избранного.
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

