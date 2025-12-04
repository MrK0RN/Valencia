<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Property.php';

$pageTitle = 'Избранное';
$user = Auth::user();
$favorites = $user->getFavorites();

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
                    <h1>Избранное</h1>
                </div>
                <div class="header-right">
                    <a href="/account/index.php" class="btn btn-secondary">Назад</a>
                    <a href="/admin/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </header>
            <main class="admin-main">
                <?php if (empty($favorites)): ?>
                    <div class="dashboard-card">
                        <p class="text-center">У вас пока нет избранных объектов.</p>
                        <p class="text-center">
                            <a href="/" class="btn btn-primary">Перейти к каталогу</a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="properties-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($favorites as $property): ?>
                            <div class="dashboard-card">
                                <h3><?php echo htmlspecialchars($property->title); ?></h3>
                                <p><strong>Цена:</strong> <?php echo $property->getFormattedPrice(); ?></p>
                                <p><strong>Адрес:</strong> <?php echo htmlspecialchars($property->getAddressForDisplay()); ?></p>
                                <?php if ($property->rooms): ?>
                                    <p><strong>Комнат:</strong> <?php echo $property->rooms; ?></p>
                                <?php endif; ?>
                                <?php if ($property->area_total): ?>
                                    <p><strong>Площадь:</strong> <?php echo $property->area_total; ?> м²</p>
                                <?php endif; ?>
                                <div style="margin-top: 15px;">
                                    <a href="/property.php?id=<?php echo $property->id; ?>" class="btn btn-primary btn-sm">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>

