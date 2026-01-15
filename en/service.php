<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full-cycle Buyer Service | Real Estate in Valencia</title>
    <meta name="description" content="Complete property purchase support in Valencia — from search to key handover. Documents, legal verification, turnkey transaction.">
    <link rel="stylesheet" href="/styles.css?v=<?php echo filemtime(__DIR__ . '/../styles.css'); ?>">
    <link rel="stylesheet" href="/blocks/header.css?v=<?php echo filemtime(__DIR__ . '/../blocks/header.css'); ?>">
    <link rel="stylesheet" href="/blocks/footer.css?v=<?php echo filemtime(__DIR__ . '/../blocks/footer.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="desktop">
        <!-- Header -->
        <?php include __DIR__ . '/../blocks/header-en.html'; ?>

        <!-- Service Content -->
        <?php include __DIR__ . '/../blocks/service-en.php'; ?>

        <!-- Footer -->
        <?php include __DIR__ . '/../blocks/footer.html'; ?>
    </div>
    <script>
        // Hamburger menu
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            const menu = document.querySelector('.menu');
            
            if (hamburgerBtn && menu) {
                hamburgerBtn.addEventListener('click', function() {
                    menu.classList.toggle('active');
                    hamburgerBtn.classList.toggle('active');
                });
                
                document.addEventListener('click', function(event) {
                    if (!hamburgerBtn.contains(event.target) && !menu.contains(event.target)) {
                        menu.classList.remove('active');
                        hamburgerBtn.classList.remove('active');
                    }
                });
                
                const menuItems = menu.querySelectorAll('.menu-item');
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        menu.classList.remove('active');
                        hamburgerBtn.classList.remove('active');
                    });
                });
            }

            // ============================================
            // SCROLL ANIMATIONS
            // ============================================
            
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        const words = entry.target.querySelectorAll('.word-animate');
                        words.forEach(word => {
                            word.classList.add('animated');
                        });
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            function initAnimations() {
                // Section titles
                const sectionTitles = document.querySelectorAll('.section-title');
                sectionTitles.forEach((title, index) => {
                    const rect = title.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    if (!title.classList.contains('processed')) {
                        title.classList.add('processed');
                        const text = title.innerHTML;
                        const words = text.match(/<span[^>]*>.*?<\/span>|[^<]+/g) || [text];
                        title.innerHTML = '';
                        words.forEach((word, wordIndex) => {
                            const span = document.createElement('span');
                            span.innerHTML = word;
                            if (!isVisible) {
                                span.classList.add('word-animate');
                                span.style.transitionDelay = `${wordIndex * 0.08}s`;
                            }
                            title.appendChild(span);
                        });
                    }
                    
                    if (isVisible) {
                        const words = title.querySelectorAll('.word-animate');
                        words.forEach(word => {
                            word.classList.add('animated');
                        });
                    } else {
                        title.classList.add('fade-slide-up');
                        observer.observe(title);
                    }
                });

                // Section subtitles
                const sectionSubtitles = document.querySelectorAll('.section-subtitle');
                sectionSubtitles.forEach((subtitle, index) => {
                    const rect = subtitle.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    if (isVisible) {
                        subtitle.classList.add('fade-slide-up', 'stagger-1', 'animated');
                    } else {
                        subtitle.classList.add('fade-slide-up', 'stagger-1');
                        observer.observe(subtitle);
                    }
                });

                // Content cards
                const contentCards = document.querySelectorAll('.content-card');
                contentCards.forEach((card, index) => {
                    const rect = card.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    if (isVisible) {
                        card.classList.add('animated');
                    } else {
                        card.classList.add('fade-scale');
                        card.style.transitionDelay = `${(index % 4) * 0.1}s`;
                        observer.observe(card);
                    }
                });

                // Hero cards
                const heroCards = document.querySelectorAll('.content-hero-card');
                heroCards.forEach((card, index) => {
                    const rect = card.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    
                    if (isVisible) {
                        card.classList.add('animated');
                    } else {
                        card.classList.add('fade-slide-up');
                        observer.observe(card);
                    }
                });

                // Buttons (excluding those in hero cards already processed)
                const buttons = document.querySelectorAll('.btn');
                buttons.forEach(button => {
                    if (!button.closest('.content-hero-card')) {
                        button.classList.add('fade-scale');
                        observer.observe(button);
                    }
                });
            }

            initAnimations();

            // Check for dynamically loaded content
            const checkForNewContent = setInterval(() => {
                const unprocessed = document.querySelectorAll('.section-title:not(.processed)');
                const titlesWithHiddenWords = document.querySelectorAll('.section-title.processed .word-animate:not(.animated)');
                if (unprocessed.length > 0 || titlesWithHiddenWords.length > 0) {
                    initAnimations();
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

            setTimeout(() => clearInterval(checkForNewContent), 10000);
            
            window.addEventListener('load', () => {
                const sectionTitles = document.querySelectorAll('.section-title');
                sectionTitles.forEach(title => {
                    const rect = title.getBoundingClientRect();
                    const isVisible = rect.top < (window.innerHeight + 200) && rect.bottom > -200;
                    const words = title.querySelectorAll('.word-animate:not(.animated)');
                    
                    if (isVisible && words.length > 0) {
                        words.forEach(word => {
                            word.classList.add('animated');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
