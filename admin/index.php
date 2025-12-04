<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Property.php';
require_once __DIR__ . '/../classes/User.php';

$pageTitle = 'Дашборд';
$currentPage = 'dashboard';

$db = new Database();

// Статистика объектов
$totalProperties = $db->fetchOne("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
$activeProperties = $db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")['count'] ?? 0;
$featuredProperties = $db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE featured = true")['count'] ?? 0;
$soldProperties = $db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status = 'sold'")['count'] ?? 0;

// Статистика пользователей
$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$usersWithFavorites = $db->fetchOne("SELECT COUNT(DISTINCT user_id) as count FROM favorites")['count'] ?? 0;

// Статистика заявок
$newRequests = $db->fetchOne("SELECT COUNT(*) as count FROM requests WHERE status = 'new'")['count'] ?? 0;
$totalRequests = $db->fetchOne("SELECT COUNT(*) as count FROM requests")['count'] ?? 0;

// Последние объекты
$recentProperties = $db->fetchAll(
    "SELECT * FROM properties ORDER BY created_at DESC LIMIT 5"
);

// Последние пользователи
$recentUsers = $db->fetchAll(
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
);

include __DIR__ . '/includes/header.php';
?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #4CAF50;">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalProperties; ?></h3>
                <p>Всего объектов</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #2196F3;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $activeProperties; ?></h3>
                <p>Активных объектов</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #FF9800;">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $featuredProperties; ?></h3>
                <p>Избранных объектов</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #9C27B0;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Пользователей</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #F44336;">
                <i class="fas fa-heart"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $usersWithFavorites; ?></h3>
                <p>С избранным</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #00BCD4;">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $newRequests; ?></h3>
                <p>Новых заявок</p>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h2>Последние объекты</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentProperties)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Нет объектов</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentProperties as $prop): ?>
                            <tr>
                                <td><?php echo $prop['id']; ?></td>
                                <td>
                                    <a href="/admin/properties/edit.php?id=<?php echo $prop['id']; ?>">
                                        <?php echo htmlspecialchars($prop['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo number_format($prop['price'], 0, ',', ' '); ?> €</td>
                                <td>
                                    <span class="badge badge-<?php echo $prop['status'] === 'active' ? 'success' : ($prop['status'] === 'sold' ? 'warning' : 'secondary'); ?>">
                                        <?php 
                                        echo $prop['status'] === 'active' ? 'Активен' : 
                                            ($prop['status'] === 'sold' ? 'Продан' : 'Скрыт'); 
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($prop['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="card-footer">
                <a href="/admin/properties/index.php" class="btn btn-link">Все объекты →</a>
            </div>
        </div>

        <div class="dashboard-card">
            <h2>Последние пользователи</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentUsers)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Нет пользователей</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <a href="/admin/users/view.php?id=<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="card-footer">
                <a href="/admin/users/index.php" class="btn btn-link">Все пользователи →</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

