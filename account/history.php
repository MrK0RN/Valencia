<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = 'История просмотров';
$user = Auth::user();

$db = new Database();
$viewHistory = $db->fetchAll(
    "SELECT pvh.*, p.title, p.price, p.id as property_id
     FROM property_view_history pvh
     LEFT JOIN properties p ON pvh.property_id = p.id
     WHERE pvh.user_id = :user_id
     ORDER BY pvh.viewed_at DESC
     LIMIT 100",
    ['user_id' => $user->id]
);

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
                    <h1>История просмотров</h1>
                </div>
                <div class="header-right">
                    <a href="/account/index.php" class="btn btn-secondary">Назад</a>
                    <a href="/admin/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </header>
            <main class="admin-main">
                <?php if (empty($viewHistory)): ?>
                    <div class="dashboard-card">
                        <p class="text-center">История просмотров пуста.</p>
                    </div>
                <?php else: ?>
                    <div class="dashboard-card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Объект</th>
                                    <th>Цена</th>
                                    <th>Дата просмотра</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viewHistory as $view): ?>
                                    <tr>
                                        <td>
                                            <?php if ($view['property_id']): ?>
                                                <a href="/property.php?id=<?php echo $view['property_id']; ?>">
                                                    <?php echo htmlspecialchars($view['title'] ?? 'Объект #' . $view['property_id']); ?>
                                                </a>
                                            <?php else: ?>
                                                Объект удален
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $view['price'] ? number_format($view['price'], 0, ',', ' ') . ' €' : '-'; ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($view['viewed_at'])); ?></td>
                                        <td>
                                            <?php if ($view['property_id']): ?>
                                                <a href="/property.php?id=<?php echo $view['property_id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>

