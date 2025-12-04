<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Недвижимость в Валенсии</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo filemtime('styles.css'); ?>">
    <link rel="stylesheet" href="blocks/header.css?v=<?php echo filemtime('blocks/header.css'); ?>">
    <link rel="stylesheet" href="blocks/hero.css?v=<?php echo filemtime('blocks/hero.css'); ?>">
    <link rel="stylesheet" href="blocks/featured-listing.css?v=<?php echo filemtime('blocks/featured-listing.css'); ?>">
    <link rel="stylesheet" href="blocks/steps.css?v=<?php echo filemtime('blocks/steps.css'); ?>">
    <link rel="stylesheet" href="blocks/approach.css?v=<?php echo filemtime('blocks/approach.css'); ?>">
    <link rel="stylesheet" href="blocks/benefits.css?v=<?php echo filemtime('blocks/benefits.css'); ?>">
    <link rel="stylesheet" href="blocks/cases.css?v=<?php echo filemtime('blocks/cases.css'); ?>">
    <link rel="stylesheet" href="blocks/footer.css?v=<?php echo filemtime('blocks/footer.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="desktop">
        <!-- Header -->
        <?php include 'blocks/header.html'; ?>

        <!-- Hero Section -->
        <?php include 'blocks/hero.html'; ?>

        <!-- Featured Listing -->
        <?php include 'blocks/featured-listing.php'; ?>

        <!-- Steps Section -->
        <?php include 'blocks/steps.html'; ?>

        <!-- Approach Section -->
        <?php include 'blocks/approach.html'; ?>

        <!-- Benefits Section -->
        <?php include 'blocks/benefits.html'; ?>

        <!-- Cases Section -->
        <?php include 'blocks/cases.html'; ?>

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

            // ============================================
            // АНИМАЦИИ ПОЯВЛЕНИЯ ТЕКСТА ПРИ ПРОКРУТКЕ
            // ============================================
            
            // Intersection Observer для анимаций
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        // Если это заголовок со словами, показываем слова тоже
                        const words = entry.target.querySelectorAll('.word-animate');
                        words.forEach(word => {
                            word.classList.add('animated');
                        });
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Применяем анимации к различным элементам
            function initAnimations() {
                // Hero section - специальная анимация для заголовка
                const heroTitle = document.querySelector('.hero-title');
                if (heroTitle && !heroTitle.classList.contains('processed')) {
                    heroTitle.classList.add('processed');
                    // Разбиваем заголовок на слова для анимации
                    const text = heroTitle.textContent;
                    heroTitle.innerHTML = '';
                    const words = text.split(' ');
                    words.forEach((word, index) => {
                        const span = document.createElement('span');
                        span.textContent = word + (index < words.length - 1 ? ' ' : '');
                        span.classList.add('word-animate');
                        span.style.transitionDelay = `${index * 0.1}s`;
                        heroTitle.appendChild(span);
                    });
                    // Анимируем слова сразу при загрузке
                    setTimeout(() => {
                        heroTitle.querySelectorAll('.word-animate').forEach(word => {
                            word.classList.add('animated');
                        });
                    }, 100);
                }

                // Hero subtitle
                const heroSubtitle = document.querySelector('.hero-subtitle');
                if (heroSubtitle) {
                    heroSubtitle.classList.add('fade-slide-up', 'stagger-2');
                    observer.observe(heroSubtitle);
                }

                // Hero button
                const heroButton = document.querySelector('.hero-text .btn');
                if (heroButton) {
                    heroButton.classList.add('fade-scale', 'stagger-3');
                    observer.observe(heroButton);
                }

                // Hero bullets
                const heroBullets = document.querySelectorAll('.hero .bullet');
                heroBullets.forEach((bullet, index) => {
                    bullet.classList.add('fade-slide-right');
                    bullet.style.transitionDelay = `${0.4 + index * 0.15}s`;
                    observer.observe(bullet);
                });

                // Section titles
                const sectionTitles = document.querySelectorAll('.section-title');
                sectionTitles.forEach((title, index) => {
                    // Сначала проверяем, виден ли заголовок сразу
                    const rect = title.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    // Разбиваем заголовок на слова для красивого эффекта
                    if (!title.classList.contains('processed')) {
                        title.classList.add('processed');
                        const text = title.innerHTML;
                        const words = text.match(/<span[^>]*>.*?<\/span>|[^<]+/g) || [text];
                        title.innerHTML = '';
                        words.forEach((word, wordIndex) => {
                            const span = document.createElement('span');
                            span.innerHTML = word;
                            // Если заголовок виден сразу, не добавляем word-animate, чтобы слова были видны сразу
                            if (!isVisible) {
                                span.classList.add('word-animate');
                                span.style.transitionDelay = `${wordIndex * 0.08}s`;
                            }
                            title.appendChild(span);
                        });
                    }
                    
                    if (isVisible) {
                        // Если заголовок уже виден, не добавляем класс анимации
                        // Слова уже должны быть видны (без word-animate)
                        // Но на всякий случай убеждаемся, что все слова видны
                        const words = title.querySelectorAll('.word-animate');
                        words.forEach(word => {
                            word.classList.add('animated');
                        });
                    } else {
                        // Если не виден, добавляем анимацию и наблюдаем
                        title.classList.add('fade-slide-up');
                        observer.observe(title);
                    }
                });

                // Section subtitles
                const sectionSubtitles = document.querySelectorAll('.section-subtitle');
                sectionSubtitles.forEach((subtitle, index) => {
                    // Проверяем, виден ли подзаголовок сразу
                    const rect = subtitle.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    if (isVisible) {
                        // Если подзаголовок уже виден, сразу показываем его
                        subtitle.classList.add('fade-slide-up', 'stagger-1', 'animated');
                    } else {
                        // Если не виден, добавляем анимацию и наблюдаем
                        subtitle.classList.add('fade-slide-up', 'stagger-1');
                        observer.observe(subtitle);
                    }
                });

                // Steps
                const steps = document.querySelectorAll('.step');
                steps.forEach((step, index) => {
                    step.classList.add('fade-slide-left');
                    step.style.transitionDelay = `${index * 0.15}s`;
                    observer.observe(step);
                });

                // Approach items
                const approachItems = document.querySelectorAll('.approach-item');
                approachItems.forEach((item, index) => {
                    item.classList.add('fade-scale');
                    item.style.transitionDelay = `${index * 0.1}s`;
                    observer.observe(item);
                });

                // Benefits
                const benefits = document.querySelectorAll('.benefit');
                benefits.forEach((benefit, index) => {
                    benefit.classList.add('fade-slide-up');
                    benefit.style.transitionDelay = `${index * 0.15}s`;
                    observer.observe(benefit);
                });

                // Cases section
                const casesTitle = document.querySelector('.cases-title');
                if (casesTitle) {
                    casesTitle.classList.add('fade-slide-up');
                    observer.observe(casesTitle);
                }

                const tableRows = document.querySelectorAll('.table-row');
                tableRows.forEach((row, index) => {
                    row.classList.add('fade-slide-left');
                    row.style.transitionDelay = `${index * 0.1}s`;
                    observer.observe(row);
                });

                const feedback = document.querySelector('.feedback');
                if (feedback) {
                    feedback.classList.add('fade-slide-up', 'stagger-2');
                    observer.observe(feedback);
                }

                // Buttons
                const buttons = document.querySelectorAll('.btn');
                buttons.forEach(button => {
                    if (!button.closest('.hero-text')) { // Уже обработали hero button
                        button.classList.add('fade-scale');
                        observer.observe(button);
                    }
                });

                // Featured listing cards - исправленный селектор
                // Ищем карточки внутри card-link или напрямую
                const featuredCards = document.querySelectorAll('.card-container .card-link .card, .card-container > .card');
                if (featuredCards.length > 0) {
                    featuredCards.forEach((card, index) => {
                        // Проверяем, видна ли карточка сразу (с небольшим запасом)
                        const rect = card.getBoundingClientRect();
                        const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                        
                        if (isVisible) {
                            // Если карточка уже видна, сразу показываем без анимации
                            // НЕ добавляем fade-scale, чтобы карточка оставалась видимой
                            card.classList.add('animated');
                        } else {
                            // Если не видна, добавляем анимацию и наблюдаем
                            card.classList.add('fade-scale');
                            card.style.transitionDelay = `${index * 0.1}s`;
                            observer.observe(card);
                        }
                    });
                }

                // Tabs
                const tabs = document.querySelectorAll('.tab');
                tabs.forEach((tab, index) => {
                    const rect = tab.getBoundingClientRect();
                    const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                    
                    if (isVisible) {
                        tab.classList.add('fade-slide-up', 'animated');
                    } else {
                        tab.classList.add('fade-slide-up');
                        tab.style.transitionDelay = `${index * 0.08}s`;
                        observer.observe(tab);
                    }
                });

                // Images - используем специальную анимацию для изображений
                const images = document.querySelectorAll('.card-img, .steps-image, .approach-image');
                images.forEach((img, index) => {
                    const rect = img.getBoundingClientRect();
                    const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                    
                    if (isVisible) {
                        // Если изображение уже видно, показываем сразу
                        img.classList.add('animated');
                    } else {
                        observer.observe(img);
                    }
                });
            }

            // Инициализация анимаций
            initAnimations();

            // Повторная инициализация для динамически загруженного контента
            const checkForNewContent = setInterval(() => {
                const unprocessed = document.querySelectorAll('.section-title:not(.processed), .hero-title:not(.processed)');
                const unprocessedCards = document.querySelectorAll('.card-container .card:not(.animated):not(.fade-scale)');
                // Проверяем заголовки, которые обработаны, но слова могут быть скрыты
                const titlesWithHiddenWords = document.querySelectorAll('.section-title.processed .word-animate:not(.animated)');
                if (unprocessed.length > 0 || unprocessedCards.length > 0 || titlesWithHiddenWords.length > 0) {
                    initAnimations();
                    // Дополнительно показываем слова в видимых заголовках
                    titlesWithHiddenWords.forEach(word => {
                        const title = word.closest('.section-title');
                        if (title) {
                            const rect = title.getBoundingClientRect();
                            const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                            if (isVisible) {
                                word.classList.add('animated');
                            }
                        }
                    });
                }
            }, 1000);

            // Остановка проверки через 10 секунд
            setTimeout(() => clearInterval(checkForNewContent), 10000);
            
            // Дополнительная проверка после полной загрузки
            window.addEventListener('load', () => {
                // Проверяем заголовки еще раз
                const sectionTitles = document.querySelectorAll('.section-title');
                sectionTitles.forEach(title => {
                    const rect = title.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    const words = title.querySelectorAll('.word-animate:not(.animated)');
                    
                    if (isVisible && words.length > 0) {
                        // Если заголовок виден, но слова скрыты - показываем их
                        words.forEach(word => {
                            word.classList.add('animated');
                        });
                    }
                });
                
                // Проверяем карточки
                const cards = document.querySelectorAll('.card-container .card-link .card, .card-container > .card');
                cards.forEach((card, index) => {
                    // Пропускаем карточки, которые уже обработаны
                    if (card.classList.contains('animated') || card.classList.contains('fade-scale')) {
                        return;
                    }
                    
                    const rect = card.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    if (isVisible) {
                        // Если видна - просто помечаем как анимированную, но не скрываем
                        card.classList.add('animated');
                    } else {
                        // Если не видна - добавляем анимацию
                        card.classList.add('fade-scale');
                        card.style.transitionDelay = `${index * 0.1}s`;
                        observer.observe(card);
                    }
                });
            });
        });
    </script>
</body>
</html>
