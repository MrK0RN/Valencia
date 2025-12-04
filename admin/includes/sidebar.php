<?php
$currentPage = $currentPage ?? '';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <h2>Админ-панель</h2>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="/admin/index.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Дашборд
                </a>
            </li>
            <li>
                <a href="/admin/properties/index.php" class="<?php echo $currentPage === 'properties' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> Объекты недвижимости
                </a>
            </li>
            <li>
                <a href="/admin/users/index.php" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Пользователи
                </a>
            </li>
        </ul>
    </nav>
</aside>

