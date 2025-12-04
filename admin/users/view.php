<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Property.php';

$pageTitle = 'Просмотр пользователя';
$currentPage = 'users';

$userId = intval($_GET['id'] ?? 0);
if (!$userId) {
    header('Location: /admin/users/index.php');
    exit;
}

$user = User::find($userId);
if (!$user) {
    header('Location: /admin/users/index.php');
    exit;
}

$db = new Database();

// Получаем избранное
$favorites = $user->getFavorites();

// Получаем историю просмотров
$viewHistory = $db->fetchAll(
    "SELECT pvh.*, p.title, p.price, p.image_path
     FROM property_view_history pvh
     LEFT JOIN properties p ON pvh.property_id = p.id
     WHERE pvh.user_id = :user_id
     ORDER BY pvh.viewed_at DESC
     LIMIT 50",
    ['user_id' => $userId]
);

// Получаем подписки на уведомления
$priceAlerts = $db->fetchAll(
    "SELECT pa.*, p.title, p.price
     FROM price_alerts pa
     LEFT JOIN properties p ON pa.property_id = p.id
     WHERE pa.user_id = :user_id AND pa.is_active = true
     ORDER BY pa.created_at DESC",
    ['user_id' => $userId]
);

// Получаем уведомления
$notifications = $db->fetchAll(
    "SELECT * FROM property_notifications
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 20",
    ['user_id' => $userId]
);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Пользователь: <?php echo htmlspecialchars($user->name); ?></h1>
    <a href="/admin/users/index.php" class="btn btn-secondary">Назад к списку</a>
</div>

<div class="user-details">
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h2>Основная информация</h2>
            <table class="data-table">
                <tr>
                    <td><strong>ID:</strong></td>
                    <td><?php echo $user->id; ?></td>
                </tr>
                <tr>
                    <td><strong>Имя:</strong></td>
                    <td><?php echo htmlspecialchars($user->name); ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($user->email); ?></td>
                </tr>
                <tr>
                    <td><strong>Телефон:</strong></td>
                    <td><?php echo htmlspecialchars($user->phone ?? '-'); ?></td>
                </tr>
                <tr>
                    <td><strong>Роль:</strong></td>
                    <td>
                        <span class="badge badge-<?php echo $user->role === 'admin' ? 'warning' : 'success'; ?>">
                            <?php echo $user->role === 'admin' ? 'Администратор' : 'Пользователь'; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Уведомления:</strong></td>
                    <td>
                        <?php if ($user->notification_enabled): ?>
                            <i class="fas fa-bell text-success"></i> Включены
                        <?php else: ?>
                            <i class="fas fa-bell-slash text-muted"></i> Отключены
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Дата регистрации:</strong></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($user->created_at)); ?></td>
                </tr>
                <tr>
                    <td><strong>Последний вход:</strong></td>
                    <td><?php echo $user->last_login_at ? date('d.m.Y H:i', strtotime($user->last_login_at)) : 'Никогда'; ?></td>
                </tr>
            </table>
        </div>

        <div class="dashboard-card">
            <h2>Избранное (<?php echo count($favorites); ?>)</h2>
            <?php if (empty($favorites)): ?>
                <p class="text-center">Нет избранных объектов</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Цена</th>
                            <th>Дата добавления</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favorites as $property): ?>
                            <tr>
                                <td><?php echo $property->id; ?></td>
                                <td>
                                    <a href="/admin/properties/edit.php?id=<?php echo $property->id; ?>">
                                        <?php echo htmlspecialchars($property->title); ?>
                                    </a>
                                </td>
                                <td><?php echo $property->getFormattedPrice(); ?></td>
                                <td>
                                    <?php
                                    $favDate = $db->fetchOne(
                                        "SELECT created_at FROM favorites 
                                         WHERE user_id = :user_id AND property_id = :property_id",
                                        ['user_id' => $userId, 'property_id' => $property->id]
                                    );
                                    echo $favDate ? date('d.m.Y', strtotime($favDate['created_at'])) : '-';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-card">
            <h2>История просмотров (<?php echo count($viewHistory); ?>)</h2>
            <?php if (empty($viewHistory)): ?>
                <p class="text-center">Нет истории просмотров</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Объект</th>
                            <th>Цена</th>
                            <th>Дата просмотра</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($viewHistory as $view): ?>
                            <tr>
                                <td>
                                    <?php if ($view['property_id']): ?>
                                        <a href="/admin/properties/edit.php?id=<?php echo $view['property_id']; ?>">
                                            <?php echo htmlspecialchars($view['title'] ?? 'Объект #' . $view['property_id']); ?>
                                        </a>
                                    <?php else: ?>
                                        Объект удален
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $view['price'] ? number_format($view['price'], 0, ',', ' ') . ' €' : '-'; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($view['viewed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-card">
            <h2>Подписки на уведомления (<?php echo count($priceAlerts); ?>)</h2>
            <?php if (empty($priceAlerts)): ?>
                <p class="text-center">Нет активных подписок</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Объект</th>
                            <th>Текущая цена</th>
                            <th>Целевая цена</th>
                            <th>Дата подписки</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($priceAlerts as $alert): ?>
                            <tr>
                                <td>
                                    <?php if ($alert['property_id']): ?>
                                        <a href="/admin/properties/edit.php?id=<?php echo $alert['property_id']; ?>">
                                            <?php echo htmlspecialchars($alert['title'] ?? 'Объект #' . $alert['property_id']); ?>
                                        </a>
                                    <?php else: ?>
                                        Объект удален
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $alert['price'] ? number_format($alert['price'], 0, ',', ' ') . ' €' : '-'; ?></td>
                                <td><?php echo $alert['target_price'] ? number_format($alert['target_price'], 0, ',', ' ') . ' €' : '-'; ?></td>
                                <td><?php echo date('d.m.Y', strtotime($alert['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="dashboard-card">
            <h2>Уведомления (<?php echo count($notifications); ?>)</h2>
            <?php if (empty($notifications)): ?>
                <p class="text-center">Нет уведомлений</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Заголовок</th>
                            <th>Дата</th>
                            <th>Прочитано</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notification): ?>
                            <tr>
                                <td>
                                    <?php
                                    $types = [
                                        'price_drop' => 'Снижение цены',
                                        'sold' => 'Продано',
                                        'new_featured' => 'Новое избранное'
                                    ];
                                    echo $types[$notification['type']] ?? $notification['type'];
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($notification['created_at'])); ?></td>
                                <td>
                                    <?php if ($notification['is_read']): ?>
                                        <i class="fas fa-check text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle text-warning"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

