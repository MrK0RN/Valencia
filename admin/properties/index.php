<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Property.php';
require_once __DIR__ . '/../../classes/Database.php';

$pageTitle = 'Объекты недвижимости';
$currentPage = 'properties';

$db = new Database();

// Получаем все объекты с сортировкой
$properties = $db->fetchAll(
    "SELECT * FROM properties ORDER BY sort_order ASC, created_at DESC"
);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Объекты недвижимости</h1>
    <a href="/admin/properties/create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить объект
    </a>
</div>

<div class="properties-list" id="propertiesList">
    <table class="data-table sortable-table">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Площадь</th>
                <th>Комнаты</th>
                <th>Статус</th>
                <th>Featured</th>
                <th style="width: 150px;">Действия</th>
            </tr>
        </thead>
        <tbody id="sortableProperties">
            <?php foreach ($properties as $index => $prop): ?>
                <tr data-id="<?php echo $prop['id']; ?>" class="sortable-row">
                    <td class="drag-handle">
                        <i class="fas fa-grip-vertical"></i>
                        <?php echo $prop['id']; ?>
                    </td>
                    <td>
                        <a href="/admin/properties/edit.php?id=<?php echo $prop['id']; ?>">
                            <?php echo htmlspecialchars($prop['title']); ?>
                        </a>
                    </td>
                    <td><?php echo number_format($prop['price'], 0, ',', ' '); ?> €</td>
                    <td><?php echo $prop['area_total'] ? $prop['area_total'] . ' м²' : '-'; ?></td>
                    <td><?php echo $prop['rooms'] ?? '-'; ?></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $prop['status'] === 'active' ? 'success' : 
                                ($prop['status'] === 'sold' ? 'warning' : 'secondary'); 
                        ?>">
                            <?php 
                            echo $prop['status'] === 'active' ? 'Активен' : 
                                ($prop['status'] === 'sold' ? 'Продан' : 'Скрыт'); 
                            ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-toggle-featured" 
                                data-id="<?php echo $prop['id']; ?>"
                                data-featured="<?php echo $prop['featured'] ? '1' : '0'; ?>">
                            <?php if ($prop['featured']): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        </button>
                    </td>
                    <td>
                        <a href="/admin/properties/edit.php?id=<?php echo $prop['id']; ?>" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger btn-delete-property" 
                                data-id="<?php echo $prop['id']; ?>"
                                data-title="<?php echo htmlspecialchars($prop['title']); ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

