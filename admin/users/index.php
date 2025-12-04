<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

$pageTitle = 'Пользователи';
$currentPage = 'users';

$db = new Database();

// Фильтрация и поиск
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(name ILIKE :search OR email ILIKE :search OR phone ILIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if ($role) {
    $where[] = "role = :role";
    $params['role'] = $role;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Получаем пользователей
$users = $db->fetchAll(
    "SELECT u.*, 
     (SELECT COUNT(*) FROM favorites WHERE user_id = u.id) as favorites_count,
     (SELECT COUNT(*) FROM property_view_history WHERE user_id = u.id) as views_count
     FROM users u 
     $whereClause
     ORDER BY u.created_at DESC",
    $params
);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Пользователи</h1>
</div>

<div class="filters">
    <form method="GET" class="filter-form">
        <div class="form-group" style="display: inline-block; margin-right: 10px;">
            <input type="text" name="search" placeholder="Поиск по имени, email, телефону" 
                   value="<?php echo htmlspecialchars($search); ?>" style="width: 300px;">
        </div>
        <div class="form-group" style="display: inline-block; margin-right: 10px;">
            <select name="role">
                <option value="">Все роли</option>
                <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Администратор</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Поиск</button>
        <?php if ($search || $role): ?>
            <a href="/admin/users/index.php" class="btn btn-secondary">Сбросить</a>
        <?php endif; ?>
    </form>
</div>

<div class="users-list">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Роль</th>
                <th>Избранное</th>
                <th>Просмотры</th>
                <th>Уведомления</th>
                <th>Дата регистрации</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="10" class="text-center">Пользователи не найдены</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <a href="/admin/users/view.php?id=<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'warning' : 'success'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'Админ' : 'Пользователь'; ?>
                            </span>
                        </td>
                        <td><?php echo $user['favorites_count']; ?></td>
                        <td><?php echo $user['views_count']; ?></td>
                        <td>
                            <?php if ($user['notification_enabled']): ?>
                                <i class="fas fa-bell text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-bell-slash text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="/admin/users/view.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

