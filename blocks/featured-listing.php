<?php
require_once __DIR__ . '/../classes/Property.php';

// Получаем featured объекты
$featuredProperties = Property::getFeatured();
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isEn = (strpos($requestUri, '/en/') === 0) || (rtrim($requestUri, '/') === '/en');
$locale = $isEn ? 'en' : 'ru';

$titleText = $isEn ? 'Featured listing' : 'Избранные объекты';
$subtitleText = $isEn
    ? 'Our houses are synonymous of well-being, comfort, and quality of life. In our portfolio you will find luxury properties for sale and for rent in Valencia, with excellent locations, and with designs and services that make them truly unique and special'
    : 'Наши дома — это комфорт, качество жизни и внимание к деталям. В портфолио — лучшие объекты для покупки и аренды в Валенсии: отличные локации, продуманный дизайн и сервисы, которые делают их по-настоящему особенными.';
?>
<!-- Featured Listing -->
<div class="section">
    <div class="text-content" style="align-items: center;">
        <div class="section-title">
            <?php if ($isEn): ?>
                <span style="color: #60724F">Featured&nbsp;</span><span style="color: black"> listing</span>
            <?php else: ?>
                <span style="color: #60724F"><?php echo htmlspecialchars($titleText); ?></span>
            <?php endif; ?>
        </div>
        <div class="section-subtitle">
            <?php echo htmlspecialchars($subtitleText); ?>
        </div>
    </div>
    <div class="frame-6">
        <div class="tab-container">
            <div class="tab tab-active">
                <div class="tab-text">See all</div>
            </div>
        </div>
        <div class="arrows">
            <div class="arrow arrow-prev" id="carouselPrev">
                <div class="arrow-left">
                    <div class="arrow-vector" style="left: 3.75px; top: 3.75px;"></div>
                </div>
            </div>
            <div class="arrow arrow-next" id="carouselNext">
                <div class="arrow-left">
                    <div class="arrow-vector" style="left: 16.25px; top: 16.25px; transform: rotate(-180deg); transform-origin: top left;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="carousel-wrapper">
        <div class="card-container" id="featuredCarousel">
            <?php if (empty($featuredProperties)): ?>
                <div class="card">
                    <div class="card-text">
                        <div class="card-title">Нет объектов</div>
                        <div class="card-desc">В данный момент нет featured объектов</div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($featuredProperties as $property): 
                    $photos = $property->getPhotos();
                    $firstPhoto = !empty($photos) ? $photos[0]['image_path'] : null;
                    if ($firstPhoto) {
                        $imageSrc = (strpos($firstPhoto, '/') === 0) ? htmlspecialchars($firstPhoto) : '/' . htmlspecialchars($firstPhoto);
                    } else {
                        $imageSrc = '/assets/img/wp14994042-valencia-4k-wallpapers.png';
                    }
                    $imageAlt = htmlspecialchars($property->getLocalizedTitle($locale) ?? 'Property');
                    $title = htmlspecialchars($property->getLocalizedTitle($locale) ?? 'Без названия');
                    $description = htmlspecialchars($property->getLocalizedDescription($locale) ?? '');
                    // Обрезаем описание если слишком длинное
                    if (mb_strlen($description) > 100) {
                        $description = mb_substr($description, 0, 97) . '...';
                    }
                    $price = $property->getFormattedPrice();
                    $propertyId = $property->id ?? 0;
                    $propertyLink = $isEn
                        ? "/en/property.php?id={$propertyId}"
                        : "/property/{$propertyId}";
                ?>
                    <a href="<?php echo $propertyLink; ?>" class="card-link">
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
            <?php endif; ?>
        </div>
    </div>
    <a href="<?php echo $isEn ? '/en/catalog.php' : '/catalog'; ?>" class="btn btn-large">
        <?php echo $isEn ? 'Show more' : 'Показать еще'; ?>
    </a>
</div>

<script>
(function() {
    function initCarousel() {
        const carousel = document.getElementById('featuredCarousel');
        const prevBtn = document.getElementById('carouselPrev');
        const nextBtn = document.getElementById('carouselNext');
        const carouselWrapper = carousel?.closest('.carousel-wrapper');
        
        if (!carousel || !prevBtn || !nextBtn) {
            // Retry if elements not ready yet
            setTimeout(initCarousel, 100);
            return;
        }
        
        let currentIndex = 0;
        let cardsPerView = 3;
        let resizeTimeout;
        let autoPlayInterval = null;
        let isPaused = false;
        const autoPlayDelay = 5000; // 5 seconds
        
        function getCardsPerView() {
            const width = window.innerWidth;
            if (width >= 992) return 3;
            if (width >= 769) return 2;
            return 1;
        }
        
        function updateCardsPerView() {
            const oldCardsPerView = cardsPerView;
            cardsPerView = getCardsPerView();
            
            // Reset index if needed when switching views
            if (oldCardsPerView !== cardsPerView) {
                const totalCards = carousel.children.length;
                const maxIndex = Math.max(0, totalCards - cardsPerView);
                currentIndex = Math.min(currentIndex, maxIndex);
            }
            
            updateCarousel();
        }
        
        function updateCarousel() {
            const firstCard = carousel.querySelector('.card');
            if (!firstCard) return;
            
            const cardWidth = firstCard.offsetWidth;
            const gap = 20;
            const translateX = -(currentIndex * (cardWidth + gap));
            carousel.style.transform = `translateX(${translateX}px)`;
            
            // Update button states
            const totalCards = carousel.children.length;
            const maxIndex = Math.max(0, totalCards - cardsPerView);
            
            prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
            prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
            
            nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
            nextBtn.style.pointerEvents = currentIndex >= maxIndex ? 'none' : 'auto';
        }
        
        function goToNext() {
            const totalCards = carousel.children.length;
            const maxIndex = Math.max(0, totalCards - cardsPerView);
            if (currentIndex < maxIndex) {
                currentIndex++;
            } else {
                currentIndex = 0; // Loop back to start
            }
            updateCarousel();
        }
        
        function goToPrev() {
            const totalCards = carousel.children.length;
            const maxIndex = Math.max(0, totalCards - cardsPerView);
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = maxIndex; // Loop to end
            }
            updateCarousel();
        }
        
        function startAutoPlay() {
            if (autoPlayInterval) return;
            
            autoPlayInterval = setInterval(() => {
                if (!isPaused) {
                    goToNext();
                }
            }, autoPlayDelay);
        }
        
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }
        
        function pauseAutoPlay() {
            isPaused = true;
        }
        
        function resumeAutoPlay() {
            isPaused = false;
        }
        
        // Button click handlers
        prevBtn.addEventListener('click', () => {
            goToPrev();
            pauseAutoPlay();
            stopAutoPlay();
            setTimeout(() => {
                startAutoPlay();
                resumeAutoPlay();
            }, autoPlayDelay * 2);
        });
        
        nextBtn.addEventListener('click', () => {
            goToNext();
            pauseAutoPlay();
            stopAutoPlay();
            setTimeout(() => {
                startAutoPlay();
                resumeAutoPlay();
            }, autoPlayDelay * 2);
        });
        
        // Pause on hover
        if (carouselWrapper) {
            carouselWrapper.addEventListener('mouseenter', () => {
                pauseAutoPlay();
            });
            
            carouselWrapper.addEventListener('mouseleave', () => {
                resumeAutoPlay();
            });
        }
        
        // Pause on touch/interaction
        let interactionTimeout;
        function handleInteraction() {
            pauseAutoPlay();
            stopAutoPlay();
            clearTimeout(interactionTimeout);
            interactionTimeout = setTimeout(() => {
                startAutoPlay();
                resumeAutoPlay();
            }, autoPlayDelay * 2);
        }
        
        carousel.addEventListener('touchstart', handleInteraction);
        carousel.addEventListener('touchmove', handleInteraction);
        
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                updateCardsPerView();
            }, 150);
        });
        
        // Wait for images to load before initializing
        const images = carousel.querySelectorAll('img');
        let imagesLoaded = 0;
        
        function initialize() {
            updateCardsPerView();
            startAutoPlay();
        }
        
        if (images.length === 0) {
            initialize();
        } else {
            images.forEach(img => {
                if (img.complete) {
                    imagesLoaded++;
                } else {
                    img.addEventListener('load', () => {
                        imagesLoaded++;
                        if (imagesLoaded === images.length) {
                            initialize();
                        }
                    });
                    img.addEventListener('error', () => {
                        imagesLoaded++;
                        if (imagesLoaded === images.length) {
                            initialize();
                        }
                    });
                }
            });
            
            if (imagesLoaded === images.length) {
                initialize();
            }
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopAutoPlay();
        });
    }
    
    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousel);
    } else {
        initCarousel();
    }
})();
</script>

