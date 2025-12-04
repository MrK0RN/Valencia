<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Session.php';

$pageTitle = 'Настройки';
$user = Auth::user();
$db = new Database();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!Session::validateCsrfToken($csrfToken)) {
        $error = 'Ошибка безопасности.';
    } else {
        $notificationEnabled = isset($_POST['notification_enabled']) && $_POST['notification_enabled'] === '1';
        
        try {
            $db->query(
                "UPDATE users SET notification_enabled = :enabled WHERE id = :id",
                ['enabled' => $notificationEnabled, 'id' => $user->id]
            );
            $user->notification_enabled = $notificationEnabled;
            $success = 'Настройки сохранены!';
        } catch (Exception $e) {
            $error = 'Ошибка при сохранении настроек.';
        }
    }
}

// Получаем подписки
$priceAlerts = $db->fetchAll(
    "SELECT pa.*, p.title, p.price
     FROM price_alerts pa
     LEFT JOIN properties p ON pa.property_id = p.id
     WHERE pa.user_id = :user_id
     ORDER BY pa.created_at DESC",
    ['user_id' => $user->id]
);

$csrfToken = Session::generateCsrfToken();
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
                    <h1>Настройки</h1>
                </div>
                <div class="header-right">
                    <a href="/account/index.php" class="btn btn-secondary">Назад</a>
                    <a href="/admin/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </header>
            <main class="admin-main">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <h2>Уведомления</h2>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="notification_enabled" value="1" 
                                           <?php echo $user->notification_enabled ? 'checked' : ''; ?>>
                                    Получать уведомления
                                </label>
                                <small>
                                    Мы будем отправлять уведомления о снижении цены объектов из вашего избранного 
                                    и о продаже объектов. Мы не собираемся беспокоить вас лишними сообщениями.
                                </small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            </div>
                        </form>
                    </div>

                    <div class="dashboard-card">
                        <h2>Подписки на уведомления о цене</h2>
                        <?php if (empty($priceAlerts)): ?>
                            <p class="text-center">У вас нет активных подписок.</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Объект</th>
                                        <th>Текущая цена</th>
                                        <th>Целевая цена</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($priceAlerts as $alert): ?>
                                        <tr>
                                            <td>
                                                <?php if ($alert['property_id']): ?>
                                                    <a href="/property.php?id=<?php echo $alert['property_id']; ?>">
                                                        <?php echo htmlspecialchars($alert['title'] ?? 'Объект #' . $alert['property_id']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    Объект удален
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $alert['price'] ? number_format($alert['price'], 0, ',', ' ') . ' €' : '-'; ?></td>
                                            <td><?php echo $alert['target_price'] ? number_format($alert['target_price'], 0, ',', ' ') . ' €' : '-'; ?></td>
                                            <td>
                                                <?php if ($alert['is_active']): ?>
                                                    <span class="badge badge-success">Активна</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Неактивна</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

