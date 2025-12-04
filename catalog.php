<?php
require_once __DIR__ . '/classes/Property.php';

// Пагинация
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Получаем объекты
$properties = Property::getActive($perPage, $offset);
$totalCount = Property::getActiveCount();
$totalPages = ceil($totalCount / $perPage);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог недвижимости - Валенсия</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo filemtime('styles.css'); ?>">
    <link rel="stylesheet" href="blocks/header.css?v=<?php echo filemtime('blocks/header.css'); ?>">
    <link rel="stylesheet" href="blocks/featured-listing.css?v=<?php echo filemtime('blocks/featured-listing.css'); ?>">
    <link rel="stylesheet" href="blocks/footer.css?v=<?php echo filemtime('blocks/footer.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .catalog-grid {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .catalog-header {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .catalog-title {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 800;
            color: #60724F;
            margin-bottom: 10px;
        }

        .catalog-subtitle {
            font-size: clamp(16px, 2vw, 18px);
            color: #666;
            /*margin-bottom: 30px;*/
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #60724F;
            background: white;
            border: 1px solid #60724F;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #60724F;
            color: white;
        }

        .pagination .current {
            background: #60724F;
            color: white;
            border-color: #60724F;
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .catalog-stats {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .card-icons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 12px 0;
        }

        .card-icon-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .card-icon-text {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .catalog-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="desktop">
        <!-- Header -->
        <?php include 'blocks/header.html'; ?>

        <!-- Catalog Section -->
        <div class="section">
            <div class="catalog-header">
                <h1 class="catalog-title">Каталог недвижимости</h1>
                <p class="catalog-subtitle">Найдите идеальное жилье в Валенсии</p>
                <div class="catalog-stats">
                    Найдено объектов: <?php echo $totalCount; ?>
                    <?php if ($totalPages > 1): ?>
                        | Страница <?php echo $page; ?> из <?php echo $totalPages; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($properties)): ?>
                <div class="text-content" style="text-align: center;">
                    <p style="font-size: 18px; color: #666;">В данный момент нет доступных объектов недвижимости.</p>
                </div>
            <?php else: ?>
                <div class="catalog-grid">
                    <?php foreach ($properties as $property): 
                        $photos = $property->getPhotos();
                        $firstPhoto = !empty($photos) ? $photos[0]['image_path'] : null;
                        if ($firstPhoto) {
                            $imageSrc = (strpos($firstPhoto, '/') === 0) ? htmlspecialchars($firstPhoto) : '/' . htmlspecialchars($firstPhoto);
                        } else {
                            $imageSrc = '/assets/img/wp14994042-valencia-4k-wallpapers.png';
                        }
                        $imageAlt = htmlspecialchars($property->title ?? 'Property');
                        $title = htmlspecialchars($property->title ?? 'Без названия');
                        $description = htmlspecialchars($property->description ?? '');
                        // Обрезаем описание если слишком длинное
                        if (mb_strlen($description) > 100) {
                            $description = mb_substr($description, 0, 97) . '...';
                        }
                        $price = $property->getFormattedPrice();
                        $propertyId = $property->id ?? 0;
                    ?>
                        <a href="/property/<?php echo $propertyId; ?>" class="card-link">
                            <div class="card">
                                <img class="card-img" src="<?php echo $imageSrc; ?>" alt="<?php echo $imageAlt; ?>" loading="lazy">
                                <div class="card-text">
                                    <div class="card-title"><?php echo $title; ?></div>
                                    <div class="card-desc"><?php echo $description ?: 'Описание отсутствует'; ?></div>
                                    <div class="card-icons">
                                        <?php if (!empty($property->area_total)): ?>
                                            <div class="card-icon-item">
                                                <img src="/assets/icons/size.svg" alt="Площадь" class="card-icon">
                                                <span class="card-icon-text"><?php echo number_format($property->area_total, 0, ',', ' '); ?> м²</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($property->rooms)): ?>
                                            <div class="card-icon-item">
                                                <img src="/assets/icons/plan.svg" alt="Комнаты" class="card-icon">
                                                <span class="card-icon-text"><?php echo $property->rooms; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($property->floor)): ?>
                                            <div class="card-icon-item">
                                                <img src="/assets/icons/floor.svg" alt="Этаж" class="card-icon">
                                                <span class="card-icon-text"><?php echo $property->floor; ?><?php echo !empty($property->total_floors) ? '/' . $property->total_floors : ''; ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($property->build_year)): ?>
                                            <div class="card-icon-item">
                                                <img src="/assets/icons/build_year.svg" alt="Год постройки" class="card-icon">
                                                <span class="card-icon-text"><?php echo $property->build_year; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-price"><?php echo $price; ?></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="/catalog?page=<?php echo $page - 1; ?>">&laquo; Назад</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Назад</span>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        if ($startPage > 1): ?>
                            <a href="/catalog?page=1">1</a>
                            <?php if ($startPage > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="/catalog?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="/catalog?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="/catalog?page=<?php echo $page + 1; ?>">Вперед &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Вперед &raquo;</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php include 'blocks/footer.html'; ?>
    </div>
    <script>
        // Гамбургер-меню
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            const menu = document.querySelector('.menu');
            
            if (hamburgerBtn && menu) {
                hamburgerBtn.addEventListener('click', function() {
                    menu.classList.toggle('active');
                    hamburgerBtn.classList.toggle('active');
                });
                
                // Закрытие меню при клике вне его
                document.addEventListener('click', function(event) {
                    if (!hamburgerBtn.contains(event.target) && !menu.contains(event.target)) {
                        menu.classList.remove('active');
                        hamburgerBtn.classList.remove('active');
                    }
                });
                
                // Закрытие меню при клике на пункт меню
                const menuItems = menu.querySelectorAll('.menu-item');
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        menu.classList.remove('active');
                        hamburgerBtn.classList.remove('active');
                    });
                });
            }
        });
    </script>
</body>
</html>

