<?php
require_once __DIR__ . '/classes/Property.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/classes/Favorite.php';

// Инициализация сессии
Session::start();

// Получаем ID объекта из URL
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($propertyId <= 0) {
    header('HTTP/1.0 404 Not Found');
    die('Объект не найден');
}

$property = Property::find($propertyId);

if (!$property || $property->status !== Property::STATUS_ACTIVE) {
    header('HTTP/1.0 404 Not Found');
    die('Объект не найден или недоступен');
}

// Получаем полную информацию об объекте
$photos = $property->getPhotos();
$features = $property->getFeatures();
$fullInfo = $property->getFullInfo();

// Загружаем конфигурацию Google Maps
$mapsConfig = require __DIR__ . '/config/maps.php';
$googleMapsApiKey = $mapsConfig['google_maps_api_key'];

// Проверяем авторизацию и статус избранного
$isAuthenticated = Auth::check();
$user = $isAuthenticated ? Auth::user() : null;
$isFavorite = false;
if ($isAuthenticated && $user) {
    $isFavorite = Favorite::exists($user->id, $propertyId);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property->title ?? 'Объект недвижимости'); ?> - Валенсия</title>
    <link rel="stylesheet" href="/styles.css?v=<?php echo filemtime(__DIR__ . '/styles.css'); ?>">
    <link rel="stylesheet" href="/blocks/header.css?v=<?php echo filemtime(__DIR__ . '/blocks/header.css'); ?>">
    <link rel="stylesheet" href="/blocks/footer.css?v=<?php echo filemtime(__DIR__ . '/blocks/footer.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($property->lat) && !empty($property->lng)): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($googleMapsApiKey); ?>&callback=initPropertyMapCallback&loading=async" async defer></script>
    <?php endif; ?>
    <style>
        /* Переопределяем overflow для работы sticky только для этой страницы */
        body > .desktop > .section {
            overflow: visible !important;
        }
        
        body > .desktop {
            overflow: visible !important;
        }
        
        html, body {
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .property-section {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0;
            overflow: visible;
        }

        .property-header {
            /*margin-bottom: clamp(30px, 4vw, 40px);*/
            text-align: left;
        }

        .property-title-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 10px;
        }

        .property-title {
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            color: #60724F;
            line-height: 1.2;
            flex: 1;
            margin: 0;
        }

        .property-address {
            font-size: clamp(16px, 2vw, 18px);
            color: #666;
            margin-bottom: 20px;
        }

        .property-gallery-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: clamp(25px, 3vw, 30px);
            margin-bottom: clamp(30px, 4vw, 40px);
            align-items: start;
            overflow: visible;
        }

        .property-gallery {
            width: 100%;
        }

        .gallery-main {
            width: 100%;
            margin-bottom: 15px;
            border-radius: 20px;
            overflow: hidden;
            background: #f0f0f0;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-main:hover {
            transform: scale(1.02);
        }

        .gallery-main img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
        }

        .gallery-thumbnails {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 10px;
        }

        .gallery-thumbnail {
            width: 100%;
            height: 75px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.3s ease, transform 0.2s ease;
            border: 2px solid transparent;
            display: block;
        }

        .gallery-thumbnail:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .gallery-thumbnail.active {
            border-color: #60724F;
        }

        .property-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: clamp(25px, 3vw, 30px);
            margin-bottom: 40px;
        }
        
        .property-map-container {
            grid-column: 1 / 2;
        }

        .property-column {
            display: flex;
            flex-direction: column;
            gap: clamp(25px, 3vw, 30px);
        }

        .property-block {
            background: white;
            padding: clamp(20px, 3vw, 30px);
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .property-characteristics-sticky {
            align-self: start;
            z-index: 10;
            transition: none;
        }

        .property-characteristics-sticky.is-stuck {
            position: fixed;
            top: 80px;
        }

        .property-block h2 {
            font-size: clamp(20px, 2.5vw, 24px);
            font-weight: 700;
            color: #60724F;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .property-block h3 {
            font-size: clamp(18px, 2vw, 20px);
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .property-params {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 25px;
            /*padding-top: 20px;
            /*border-top: 2px solid #e0e0e0;*/
        }

        .property-params-two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            flex-direction: row;
        }

        .property-params-column {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .param-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .param-value-row {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
        }

        .param-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .param-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .param-value {
            font-size: clamp(16px, 2vw, 18px);
            color: #333;
            font-weight: 600;
        }

        .property-description {
            line-height: 1.8;
            color: #333;
            font-size: clamp(15px, 1.8vw, 16px);
        }

        .features-list-bullets {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .feature-bullet-item {
            display: flex;
            align-items: center;
            gap: 12px;
            /*padding: 8px 0;*/
        }

        .feature-bullet-icon {
            width: 24px;
            height: 24px;
            background: #60724F;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .feature-bullet-text {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .property-photos-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .property-photo-item {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #f0f0f0;
        }

        .property-photo-item:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .property-photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .property-price-sidebar {
            font-size: clamp(28px, 3.5vw, 36px);
            font-weight: 700;
            color: #60724F;
            margin-bottom: 10px;
            text-align: left;
        }

        .property-price-per-meter {
            font-size: clamp(16px, 2vw, 18px);
            font-weight: 500;
            color: #666;
            margin-bottom: 25px;
            /*padding-bottom: 20px;*/
            /*border-bottom: 2px solid #e0e0e0;*/
            text-align: left;
        }

        .property-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .property-actions .btn {
            width: 100%;
            text-align: center;
        }

        .repair-badge {
            background: linear-gradient(135deg, #FF7A5C 0%, #FF6B42 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: clamp(16px, 2vw, 18px);
            margin-top: 15px;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 122, 92, 0.3);
        }

        .repair-badge:hover {
            background: linear-gradient(135deg, #FF6B42 0%, #FF5A2E 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 122, 92, 0.4);
        }

        .repair-badge-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        .repair-badge-text {
            flex: 1;
            text-align: left;
        }

        .repair-badge-arrow {
            flex-shrink: 0;
            color: white;
        }

        .property-map {
            width: 100%;
            height: 500px;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 20px;
            background: #f0f0f0;
        }

        .property-map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .property-video {
            width: 100%;
            aspect-ratio: 16 / 9;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 20px;
            background: #000;
        }

        .property-video iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .photo-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.95);
        }

        .photo-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .photo-modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-modal-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }

        .photo-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: white;
            font-size: 40px;
            cursor: pointer;
            z-index: 10001;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .photo-modal-close:hover {
            transform: scale(1.2);
        }

        .photo-modal-prev,
        .photo-modal-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: white;
            font-size: 50px;
            cursor: pointer;
            z-index: 10001;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .photo-modal-prev:hover,
        .photo-modal-next:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .photo-modal-prev {
            left: -80px;
        }

        .photo-modal-next {
            right: -80px;
        }

        .photo-modal-counter {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 16px;
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 16px;
            border-radius: 20px;
        }

        @media (max-width: 768px) {
            .photo-modal-prev,
            .photo-modal-next {
                width: 40px;
                height: 40px;
                font-size: 30px;
            }

            .photo-modal-prev {
                left: 10px;
            }

            .photo-modal-next {
                right: 10px;
            }

            .photo-modal-close {
                top: 10px;
                right: 10px;
            }
        }

        @media (max-width: 992px) {
            .property-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .property-map-container {
                grid-column: 1;
            }

            .property-gallery-wrapper {
                grid-template-columns: 1fr;
            }

            .gallery-main img {
                height: 400px;
            }

            .property-column-right {
                order: -1;
            }

            .property-photos-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .property-characteristics-sticky {
                position: sticky;
                top: 80px;
            }
        }

        /* Contact Modal Styles */
        .contact-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10001;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            padding: 20px;
        }

        .contact-modal.active {
            display: flex;
        }

        .contact-modal-content {
            background: white;
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .contact-modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .contact-modal-title {
            font-size: clamp(24px, 3vw, 28px);
            font-weight: 700;
            color: #60724F;
            margin: 0;
        }

        .contact-modal-close {
            background: none;
            border: none;
            font-size: 32px;
            color: #666;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            padding: 0;
            line-height: 1;
        }

        .contact-modal-close:hover {
            background: #f0f0f0;
            color: #333;
        }

        .contact-modal-body {
            padding: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0 5px;
            border-bottom: 1px solid #f0f0f0;
        }

        .contact-item:last-of-type {
            border-bottom: none;
        }

        .contact-item-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            color: #60724F;
        }

        .contact-item-content {
            flex: 1;
        }

        .contact-item-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }

        .contact-item-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .contact-item-value a {
            color: #60724F;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .contact-item-value a:hover {
            color: #4a5a3d;
            text-decoration: underline;
        }

        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            color: #60724F;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .social-link:hover {
            background: #60724F;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(96, 114, 79, 0.3);
        }

        .callback-form {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #e0e0e0;
        }

        .callback-form-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-label.required::after {
            content: ' *';
            color: #ff4444;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #60724F;
        }

        .form-input.error {
            border-color: #ff4444;
        }

        .form-error {
            color: #ff4444;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .form-error.show {
            display: block;
        }

        .form-submit {
            width: 100%;
            padding: 14px;
            background: #60724F;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-submit:hover:not(:disabled) {
            background: #4a5a3d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(96, 114, 79, 0.3);
        }

        .form-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-success {
            display: none;
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            color: #155724;
            margin-top: 15px;
            text-align: center;
        }

        .form-success.show {
            display: block;
        }

        /* Favorite Button */
        .favorite-button {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: white;
            border: 2px solid #60724F;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
            padding: 0;
        }

        .favorite-button:hover {
            background: rgba(96, 114, 79, 0.1);
        }

        .favorite-button.active {
            background: #60724F;
            border-color: #60724F;
        }

        .favorite-button svg {
            width: 24px;
            height: 24px;
            stroke: #60724F;
            fill: none;
            stroke-width: 2;
            transition: all 0.3s ease;
        }

        .favorite-button.active svg {
            stroke: white;
            fill: white;
        }

        .favorite-button:hover svg {
            stroke: #60724F;
        }

        .favorite-button.active:hover svg {
            stroke: white;
            fill: white;
        }

        /* Login Modal */
        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10002;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            padding: 20px;
        }

        .login-modal.active {
            display: flex;
        }

        .login-modal-content {
            background: white;
            border-radius: 20px;
            max-width: 450px;
            width: 100%;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        .login-modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-modal-title {
            font-size: clamp(24px, 3vw, 28px);
            font-weight: 700;
            color: #60724F;
            margin: 0;
        }

        .login-modal-close {
            background: none;
            border: none;
            font-size: 32px;
            color: #666;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
            padding: 0;
            line-height: 1;
        }

        .login-modal-close:hover {
            background: #f0f0f0;
            color: #333;
        }

        .login-modal-body {
            padding: 30px;
        }

        .login-form-group {
            margin-bottom: 20px;
        }

        .login-form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .login-form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .login-form-input:focus {
            outline: none;
            border-color: #60724F;
        }

        .login-form-input.error {
            border-color: #ff4444;
        }

        .login-form-error {
            color: #ff4444;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .login-form-error.show {
            display: block;
        }

        .login-form-submit {
            width: 100%;
            padding: 14px;
            background: #60724F;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            margin-top: 10px;
        }

        .login-form-submit:hover:not(:disabled) {
            background: #4a5a3d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(96, 114, 79, 0.3);
        }

        .login-form-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .login-form-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .login-form-footer a {
            color: #60724F;
            text-decoration: none;
            font-weight: 500;
        }

        .login-form-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .property-section {
                padding: 0 10px;
            }

            .gallery-thumbnails {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }

            .gallery-thumbnail {
                height: 80px;
            }

            .property-block {
                padding: 20px;
            }

            .property-map {
                height: 300px;
            }

            .contact-modal-content {
                max-width: 100%;
                border-radius: 15px;
            }

            .contact-modal-header,
            .contact-modal-body {
                padding: 20px;
            }

            .favorite-button {
                width: 44px;
                height: 44px;
            }

            .favorite-button svg {
                width: 20px;
                height: 20px;
            }

            .property-title-wrapper {
                gap: 15px;
            }

            .login-modal-content {
                max-width: 100%;
                border-radius: 15px;
            }

            .login-modal-header,
            .login-modal-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="desktop">
        <!-- Header -->
        <?php include 'blocks/header.html'; ?>

        <!-- Property Section -->
        <div class="section" style="align-items: flex-start;">
            <div class="property-section">
                <!-- Header -->
                <div class="property-header">
                    <div class="property-title-wrapper">
                        <h1 class="property-title"><?php echo htmlspecialchars($property->title ?? 'Без названия'); ?></h1>
                        <button class="favorite-button <?php echo $isFavorite ? 'active' : ''; ?>" 
                                id="favoriteButton" 
                                onclick="handleFavoriteClick()"
                                title="<?php echo $isFavorite ? 'Удалить из избранного' : 'Добавить в избранное'; ?>">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="property-address"><?php echo htmlspecialchars($property->getAddressForDisplay()); ?></div>
                </div>

                <!-- Gallery and Characteristics -->
                <div class="property-gallery-wrapper">
                    <!-- Gallery -->
                    <?php if (!empty($photos)): ?>
                        <div class="property-gallery">
                            <div class="gallery-main">
                                <?php 
                                $mainImagePath = $photos[0]['image_path'];
                                $mainImagePath = (strpos($mainImagePath, '/') === 0) ? $mainImagePath : '/' . $mainImagePath;
                                ?>
                                <img id="mainImage" src="<?php echo htmlspecialchars($mainImagePath); ?>" alt="<?php echo htmlspecialchars($property->title ?? 'Property'); ?>">
                            </div>
                            <?php if (count($photos) > 1): ?>
                                <div class="gallery-thumbnails">
                                    <?php foreach ($photos as $index => $photo): 
                                        $thumbPath = $photo['image_path'];
                                        $thumbPath = (strpos($thumbPath, '/') === 0) ? $thumbPath : '/' . $thumbPath;
                                    ?>
                                        <img class="gallery-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                             src="<?php echo htmlspecialchars($thumbPath); ?>" 
                                             alt="Thumbnail <?php echo $index + 1; ?>"
                                             data-full="<?php echo htmlspecialchars($thumbPath); ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Characteristics Sidebar -->
                    <div class="property-block property-characteristics-sticky">
                        
                        <div class="property-price-sidebar"><?php echo $property->getFormattedPrice(); ?></div>
                        <?php 
                        // Рассчитываем цену за квадратный метр
                        $pricePerSquareMeter = null;
                        if (!empty($property->price) && !empty($property->area_total) && $property->area_total > 0) {
                            $pricePerSquareMeter = (float)$property->price / (float)$property->area_total;
                        }
                        ?>
                        <?php if ($pricePerSquareMeter !== null): ?>
                            <div class="property-price-per-meter">
                                <?php echo number_format($pricePerSquareMeter, 0, ',', ' '); ?> €/м²
                            </div>
                        <?php endif; ?>
                        <h2>Характеристики</h2>
                        <div class="property-params property-params-two-columns">
                            <div class="property-params-column">
                                <?php if (!empty($property->area_total)): ?>
                                    <div class="param-item">
                                        <div class="param-label">Общая площадь</div>
                                        <div class="param-value-row">
                                            <img src="/assets/icons/size.svg" alt="Площадь" class="param-icon">
                                            <div class="param-value"><?php echo number_format($property->area_total, 1, ',', ' '); ?> м²</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property->area_living)): ?>
                                    <div class="param-item">
                                        <div class="param-label">Жилая площадь</div>
                                        <div class="param-value-row">
                                            <img src="/assets/icons/size.svg" alt="Площадь" class="param-icon">
                                            <div class="param-value"><?php echo number_format($property->area_living, 1, ',', ' '); ?> м²</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property->area_kitchen)): ?>
                                    <div class="param-item">
                                        <div class="param-label">Площадь кухни</div>
                                        <div class="param-value-row">
                                            <img src="/assets/icons/kitchen.svg" alt="Кухня" class="param-icon">
                                            <div class="param-value"><?php echo number_format($property->area_kitchen, 1, ',', ' '); ?> м²</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="property-params-column">
                                <?php if (!empty($property->rooms)): ?>
                                    <div class="param-item">
                                        <div class="param-label">Комнат</div>
                                        <div class="param-value-row">
                                            <img src="/assets/icons/plan.svg" alt="Комнаты" class="param-icon">
                                            <div class="param-value"><?php echo $property->rooms; ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property->floor)): ?>
                                    <div class="param-item">
                                        <div class="param-label">Этаж</div>
                                        <div class="param-value-row">
                                            <img src="/assets/icons/floor.svg" alt="Этаж" class="param-icon">
                                            <div class="param-value"><?php echo $property->floor; ?><?php echo !empty($property->total_floors) ? ' из ' . $property->total_floors : ''; ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property->build_year)): ?>
                                    <div class="param-item">
                                        <div class="param-label">Год постройки</div>
                                        <div class="param-value-row">
                                            <img src="/assets/icons/build_year.svg" alt="Год постройки" class="param-icon">
                                            <div class="param-value"><?php echo $property->build_year; ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="property-actions">
                            <button class="btn btn-large" onclick="openContactModal()">
                                Связаться с нами
                            </button>
                            <!--
                            <button class="btn btn-large" onclick="alert('Функция добавления в избранное будет реализована позже')">
                                В избранное
                            </button>
                            -->
                            <?php if (isset($property->repair_needed) && $property->repair_needed): ?>
                                <div class="repair-badge">
                                    <img src="/assets/icons/renovation.svg" alt="Ремонт" class="repair-badge-icon">
                                    <span class="repair-badge-text">Ремонт с Wesna Group</span>
                                    <svg class="repair-badge-arrow" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="property-content">
                    <!-- Left Column: Description + Features -->
                    <div class="property-column property-column-left">
                        <!-- Description -->
                        <?php if (!empty($property->description)): ?>
                            <div class="property-block">
                                <h2>Описание</h2>
                                <div class="property-description">
                                    <?php echo nl2br(htmlspecialchars($property->description)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Features -->
                        <?php 
                        $featureLabels = [
                            Property::FEATURE_BALCONY => 'Балкон',
                            Property::FEATURE_PARKING => 'Парковка',
                            Property::FEATURE_ELEVATOR => 'Лифт',
                            Property::FEATURE_FURNISHED => 'Мебель',
                            Property::FEATURE_AIR_CONDITIONING => 'Кондиционер',
                            Property::FEATURE_HEATING => 'Отопление',
                            Property::FEATURE_POOL => 'Бассейн',
                            Property::FEATURE_GARDEN => 'Сад',
                            Property::FEATURE_TERRACE => 'Терраса',
                            Property::FEATURE_STORAGE => 'Кладовая',
                        ];
                        if (!empty($features)): ?>
                            <div class="property-block">
                                <h2>Особенности</h2>
                                <ul class="features-list-bullets">
                                    <?php foreach ($features as $feature): 
                                        $featureType = $feature['feature_type'];
                                        $featureLabel = $featureLabels[$featureType] ?? ucfirst(str_replace('_', ' ', $featureType));
                                    ?>
                                        <li class="feature-bullet-item">
                                            <span class="feature-bullet-icon">✓</span>
                                            <span class="feature-bullet-text"><?php echo htmlspecialchars($featureLabel); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column: Video -->
                    <div class="property-column property-column-right">
                        <?php if (!empty($property->video_url)): ?>
                            <div class="property-block">
                                <h2>Видео</h2>
                                <div class="property-video">
                                    <iframe src="<?php echo htmlspecialchars($property->video_url); ?>" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Map: 2 columns width -->
                    <?php if (!empty($property->lat) && !empty($property->lng)): ?>
                        <div class="property-map-container">
                            <div class="property-block">
                                <h2>Расположение</h2>
                                <div class="property-map" id="propertyMap"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php include 'blocks/footer.html'; ?>
    </div>

    <!-- Photo Modal -->
    <div id="photoModal" class="photo-modal" style="display: none;">
        <div class="photo-modal-overlay" onclick="closePhotoModal()"></div>
        <div class="photo-modal-content">
            <button class="photo-modal-close" onclick="closePhotoModal()">&times;</button>
            <button class="photo-modal-prev" onclick="changePhoto(-1)">‹</button>
            <button class="photo-modal-next" onclick="changePhoto(1)">›</button>
            <img id="modalPhoto" src="" alt="Фото">
            <div class="photo-modal-counter">
                <span id="photoCounter">1 / 1</span>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="login-modal" onclick="closeLoginModalOnOverlay(event)">
        <div class="login-modal-content" onclick="event.stopPropagation()">
            <div class="login-modal-header">
                <h2 class="login-modal-title">Вход</h2>
                <button class="login-modal-close" onclick="closeLoginModal()">&times;</button>
            </div>
            <div class="login-modal-body">
                <form id="loginForm" onsubmit="submitLogin(event)">
                    <div class="login-form-group">
                        <label class="login-form-label" for="loginEmail">Email</label>
                        <input type="email" id="loginEmail" name="email" class="login-form-input" required autocomplete="email">
                        <div class="login-form-error" id="loginEmailError"></div>
                    </div>
                    <div class="login-form-group">
                        <label class="login-form-label" for="loginPassword">Пароль</label>
                        <input type="password" id="loginPassword" name="password" class="login-form-input" required autocomplete="current-password">
                        <div class="login-form-error" id="loginPasswordError"></div>
                    </div>
                    <button type="submit" class="login-form-submit" id="loginSubmit">
                        Войти
                    </button>
                    <div class="login-form-error" id="loginGeneralError"></div>
                </form>
                <div class="login-form-footer">
                    <p>Нет аккаунта? <a href="#" onclick="event.preventDefault(); alert('Функция регистрации будет добавлена позже')">Зарегистрироваться</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="contact-modal" onclick="closeContactModalOnOverlay(event)">
        <div class="contact-modal-content" onclick="event.stopPropagation()">
            <div class="contact-modal-header">
                <h2 class="contact-modal-title">Связаться с нами</h2>
                <button class="contact-modal-close" onclick="closeContactModal()">&times;</button>
            </div>
            <div class="contact-modal-body">
                <!-- Phone -->
                <div class="contact-item">
                    <svg class="contact-item-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="contact-item-content">
                        <div class="contact-item-label">Телефон</div>
                        <div class="contact-item-value">
                            <a href="tel:+34123456789">+34 123 456 789</a>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="contact-item">
                    <svg class="contact-item-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 8L10.89 13.26C11.2187 13.4793 11.6049 13.5963 12 13.5963C12.3951 13.5963 12.7813 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="contact-item-content">
                        <div class="contact-item-label">Email</div>
                        <div class="contact-item-value">
                            <a href="mailto:info@valencia-realestate.com">info@valencia-realestate.com</a>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="contact-item">
                    <svg class="contact-item-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 2H15C13.6739 2 12.4021 2.52678 11.4645 3.46447C10.5268 4.40215 10 5.67392 10 7V10H7V14H10V22H14V14H17L18 10H14V7C14 6.73478 14.1054 6.48043 14.2929 6.29289C14.4804 6.10536 14.7348 6 15 6H18V2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="contact-item-content">
                        <div class="contact-item-label">Социальные сети</div>
                        <div class="social-links">
                            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-link" title="Facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2H15C13.6739 2 12.4021 2.52678 11.4645 3.46447C10.5268 4.40215 10 5.67392 10 7V10H7V14H10V22H14V14H17L18 10H14V7C14 6.73478 14.1054 6.48043 14.2929 6.29289C14.4804 6.10536 14.7348 6 15 6H18V2Z"/>
                                </svg>
                            </a>
                            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-link" title="Instagram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            <a href="https://telegram.org" target="_blank" rel="noopener noreferrer" class="social-link" title="Telegram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </a>
                            <a href="https://whatsapp.com" target="_blank" rel="noopener noreferrer" class="social-link" title="WhatsApp">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Callback Form -->
                <div class="callback-form">
                    <h3 class="callback-form-title">Запросить звонок</h3>
                    <form id="callbackForm" onsubmit="submitCallbackRequest(event)">
                        <div class="form-group">
                            <label class="form-label required" for="callbackName">Ваше имя</label>
                            <input type="text" id="callbackName" name="name" class="form-input" required>
                            <div class="form-error" id="nameError"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label required" for="callbackPhone">Телефон</label>
                            <input type="tel" id="callbackPhone" name="phone" class="form-input" required>
                            <div class="form-error" id="phoneError"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="callbackEmail">Email (необязательно)</label>
                            <input type="email" id="callbackEmail" name="email" class="form-input">
                            <div class="form-error" id="emailError"></div>
                        </div>
                        <button type="submit" class="form-submit" id="callbackSubmit">
                            Отправить запрос
                        </button>
                        <div class="form-success" id="callbackSuccess"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Photos array for modal
        const photos = [
            <?php 
            if (!empty($photos)) {
                $photoPaths = [];
                foreach ($photos as $photo) {
                    $photoPath = $photo['image_path'];
                    $photoPath = (strpos($photoPath, '/') === 0) ? $photoPath : '/' . $photoPath;
                    $photoPaths[] = "'" . htmlspecialchars($photoPath, ENT_QUOTES) . "'";
                }
                echo implode(', ', $photoPaths);
            }
            ?>
        ];
        let currentPhotoIndex = 0;

        function showPhotoModal(index) {
            currentPhotoIndex = index;
            const modal = document.getElementById('photoModal');
            const modalPhoto = document.getElementById('modalPhoto');
            const counter = document.getElementById('photoCounter');
            
            if (photos.length > 0 && index >= 0 && index < photos.length) {
                modalPhoto.src = photos[index];
                counter.textContent = (index + 1) + ' / ' + photos.length;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closePhotoModal() {
            const modal = document.getElementById('photoModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        function changePhoto(direction) {
            currentPhotoIndex += direction;
            if (currentPhotoIndex < 0) {
                currentPhotoIndex = photos.length - 1;
            } else if (currentPhotoIndex >= photos.length) {
                currentPhotoIndex = 0;
            }
            showPhotoModal(currentPhotoIndex);
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('photoModal');
            if (modal.style.display === 'flex') {
                if (e.key === 'Escape') {
                    closePhotoModal();
                } else if (e.key === 'ArrowLeft') {
                    changePhoto(-1);
                } else if (e.key === 'ArrowRight') {
                    changePhoto(1);
                }
            }
        });
    </script>

    <script>
            // Gallery thumbnail and main image click handlers
            document.addEventListener('DOMContentLoaded', function() {
                const thumbnails = document.querySelectorAll('.gallery-thumbnail');
                const mainImage = document.getElementById('mainImage');
                const galleryMain = document.querySelector('.gallery-main');
                
                // Click on main image to open modal
                if (mainImage && galleryMain) {
                    galleryMain.addEventListener('click', function() {
                        const currentIndex = Array.from(thumbnails).findIndex(thumb => 
                            thumb.classList.contains('active')
                        );
                        showPhotoModal(currentIndex >= 0 ? currentIndex : 0);
                    });
                }
                
                // Click on thumbnails to change main image only (no modal)
                if (thumbnails.length > 0 && mainImage) {
                    thumbnails.forEach((thumbnail, index) => {
                        thumbnail.addEventListener('click', function(e) {
                            e.stopPropagation(); // Prevent event bubbling
                            const fullImage = this.getAttribute('data-full');
                            if (fullImage) {
                                // Update main image
                                mainImage.src = fullImage;
                                
                                // Update active state
                                thumbnails.forEach(t => t.classList.remove('active'));
                                this.classList.add('active');
                            }
                        });
                    });
                }

            // Map initialization (if coordinates exist)
            <?php if (!empty($property->lat) && !empty($property->lng)): ?>
            // Callback function called when Google Maps API is loaded
            window.initPropertyMapCallback = function() {
                // Wait for DOM to be ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initPropertyMap);
                } else {
                    initPropertyMap();
                }
            };
            
            function initPropertyMap() {
                const mapDiv = document.getElementById('propertyMap');
                if (!mapDiv) {
                    // If map div doesn't exist yet, try again after a short delay
                    setTimeout(initPropertyMap, 100);
                    return;
                }

                // Double check that Google Maps API is fully loaded
                if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.Map === 'undefined') {
                    console.warn('Google Maps API not fully loaded, retrying...');
                    setTimeout(initPropertyMap, 100);
                    return;
                }

                const lat = <?php echo $property->lat; ?>;
                const lng = <?php echo $property->lng; ?>;
                
                try {
                    // Initialize Google Map
                    const mapOptions = {
                        center: { lat: lat, lng: lng },
                        zoom: 15,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        zoomControl: true,
                        mapTypeControl: false,
                        streetViewControl: true,
                        fullscreenControl: true
                    };

                    const map = new google.maps.Map(mapDiv, mapOptions);

                    // Create marker (using standard Marker)
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                        title: '<?php echo htmlspecialchars($property->title ?? 'Объект недвижимости', ENT_QUOTES); ?>'
                    });
                } catch (error) {
                    console.error('Ошибка инициализации Google Maps:', error);
                    // Fallback: show error message
                    mapDiv.innerHTML = '<p style="padding: 20px; text-align: center; color: #666;">Не удалось загрузить карту. Пожалуйста, проверьте API ключ Google Maps.</p>';
                }
            }
            <?php endif; ?>

            // Гамбургер-меню
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

            // Настройка sticky позиционирования для характеристик
            const stickyElement = document.querySelector('.property-characteristics-sticky');
            if (stickyElement) {
                // Убираем overflow: hidden у всех родителей
                let parent = stickyElement.parentElement;
                while (parent && parent !== document.body) {
                    const computedStyle = window.getComputedStyle(parent);
                    if (computedStyle.overflow === 'hidden' || computedStyle.overflowX === 'hidden' || computedStyle.overflowY === 'hidden') {
                        parent.style.overflow = 'visible';
                    }
                    parent = parent.parentElement;
                }

                // Проверяем, поддерживается ли sticky
                const supportsSticky = CSS.supports('position', 'sticky');
                
                if (!supportsSticky) {
                    console.warn('Sticky positioning not supported, using JavaScript fallback');
                }

                // Отслеживаем скролл для применения fixed позиционирования
                const stickyOffset = 80;
                const footerOffset = 60;
                let initialTop = null;
                let initialLeft = null;
                let initialWidth = null;
                let parentElement = stickyElement.parentElement;
                let lastScrollTop = 0;
                let isScrollingDown = false;
                const footerElement = document.querySelector('.footer');

                function initPositions() {
                    // Сохраняем начальную позицию элемента в потоке документа
                    if (!stickyElement.classList.contains('is-stuck')) {
                        const rect = stickyElement.getBoundingClientRect();
                        initialTop = rect.top + window.pageYOffset;
                        initialLeft = rect.left + window.pageXOffset;
                        initialWidth = rect.width;
                    }
                }

                function calculateTopPosition() {
                    if (!footerElement) {
                        return stickyOffset;
                    }

                    const footerRect = footerElement.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    const elementHeight = stickyElement.offsetHeight;
                    
                    // Проверяем, виден ли footer внизу viewport (его верх находится в пределах экрана снизу)
                    // footer должен быть виден внизу экрана, но не выше верха экрана
                    const footerVisibleInBottom = footerRect.top < viewportHeight && footerRect.top > 0;
                    
                    if (!footerVisibleInBottom) {
                        // Если footer не виден внизу экрана, используем стандартную позицию
                        return stickyOffset;
                    }
                    
                    // Вычисляем максимальную top позицию, чтобы элемент был на 60px выше footer'а
                    // footerRect.top - это позиция верха footer'а относительно viewport
                    // maxTop = позиция верха footer'а - отступ 60px - высота элемента
                    const maxTop = footerRect.top - footerOffset - elementHeight;
                    
                    // Используем минимальное значение: либо stickyOffset (80px), либо вычисленную позицию
                    // Но не меньше, чем 0
                    // Если maxTop меньше stickyOffset, значит footer близко к верху, и элемент должен быть выше
                    return Math.max(0, Math.min(stickyOffset, maxTop));
                }

                function handleScroll() {
                    if (initialTop === null) {
                        initPositions();
                        lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        return;
                    }

                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const parentRect = parentElement.getBoundingClientRect();
                    
                    // Определяем направление скролла
                    isScrollingDown = scrollTop > lastScrollTop;
                    lastScrollTop = scrollTop;
                    
                    // Вычисляем позицию скролла, когда элемент должен зафиксироваться
                    // Элемент зафиксируется, когда его верх достигнет 80px от верха
                    // Это происходит, когда scrollTop = initialTop - stickyOffset
                    const stickyTriggerPoint = initialTop - stickyOffset;
                    
                    // Проверяем, достиг ли скролл точки фиксации
                    const shouldBeStuck = scrollTop >= stickyTriggerPoint;
                    // Проверяем, что родительский контейнер еще виден
                    const parentStillVisible = parentRect.bottom > stickyOffset;
                    
                    // Если скролл вернулся к изначальной позиции (выше точки фиксации) - убираем фиксацию
                    if (scrollTop < stickyTriggerPoint) {
                        if (stickyElement.classList.contains('is-stuck')) {
                            stickyElement.classList.remove('is-stuck');
                            stickyElement.style.position = '';
                            stickyElement.style.top = '';
                            stickyElement.style.left = '';
                            stickyElement.style.width = '';
                            // Обновляем начальную позицию после возврата в поток
                            setTimeout(initPositions, 0);
                        }
                    }
                    // Если скролл достиг точки фиксации - фиксируем (только при скролле вниз для первоначальной фиксации)
                    else if (shouldBeStuck && parentStillVisible) {
                        // Вычисляем top позицию с учетом footer'а
                        const calculatedTop = calculateTopPosition();
                        
                        // Фиксируем только при скролле вниз (для первоначальной фиксации)
                        // Но если уже зафиксирован - обновляем позицию
                        if (!stickyElement.classList.contains('is-stuck') && isScrollingDown) {
                            stickyElement.classList.add('is-stuck');
                            stickyElement.style.position = 'fixed';
                            stickyElement.style.top = calculatedTop + 'px';
                            stickyElement.style.left = initialLeft + 'px';
                            stickyElement.style.width = initialWidth + 'px';
                        } else if (stickyElement.classList.contains('is-stuck')) {
                            // Обновляем top позицию, если элемент уже зафиксирован
                            stickyElement.style.top = calculatedTop + 'px';
                        }
                    }
                }

                // Инициализация
                initPositions();
                
                // Обработчики событий
                window.addEventListener('scroll', handleScroll, { passive: true });
                window.addEventListener('resize', function() {
                    if (stickyElement.classList.contains('is-stuck')) {
                        stickyElement.classList.remove('is-stuck');
                        stickyElement.style.position = '';
                        stickyElement.style.top = '';
                        stickyElement.style.left = '';
                        stickyElement.style.width = '';
                    }
                    lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    initPositions();
                    handleScroll();
                }, { passive: true });
                
                // Первоначальная проверка после загрузки
                setTimeout(function() {
                    initPositions();
                    handleScroll();
                }, 100);
            }
        });

        // Favorite Button Functions
        const isAuthenticated = <?php echo $isAuthenticated ? 'true' : 'false'; ?>;
        const propertyId = <?php echo $propertyId; ?>;
        let isFavorite = <?php echo $isFavorite ? 'true' : 'false'; ?>;

        function handleFavoriteClick() {
            if (!isAuthenticated) {
                // Открываем поп-ап входа
                openLoginModal();
            } else {
                // Переключаем избранное
                toggleFavorite();
            }
        }

        function toggleFavorite() {
            const button = document.getElementById('favoriteButton');
            const submitBtn = button;
            
            // Временно отключаем кнопку
            button.style.pointerEvents = 'none';
            
            fetch('/api/favorite_toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    property_id: propertyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isFavorite = data.added;
                    if (isFavorite) {
                        button.classList.add('active');
                        button.setAttribute('title', 'Удалить из избранного');
                    } else {
                        button.classList.remove('active');
                        button.setAttribute('title', 'Добавить в избранное');
                    }
                } else {
                    alert(data.message || 'Произошла ошибка');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении избранного');
            })
            .finally(() => {
                button.style.pointerEvents = '';
            });
        }

        // Login Modal Functions
        function openLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
                // Очищаем форму
                document.getElementById('loginForm').reset();
                document.querySelectorAll('.login-form-error').forEach(el => {
                    el.classList.remove('show');
                    el.textContent = '';
                });
                document.querySelectorAll('.login-form-input').forEach(el => {
                    el.classList.remove('error');
                });
            }
        }

        function closeLoginModalOnOverlay(event) {
            if (event.target.id === 'loginModal') {
                closeLoginModal();
            }
        }

        function submitLogin(event) {
            event.preventDefault();
            
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('loginSubmit');
            const emailInput = document.getElementById('loginEmail');
            const passwordInput = document.getElementById('loginPassword');
            
            // Получаем данные формы
            const formData = {
                email: emailInput.value.trim(),
                password: passwordInput.value
            };
            
            // Очищаем предыдущие ошибки
            document.querySelectorAll('.login-form-error').forEach(el => {
                el.classList.remove('show');
                el.textContent = '';
            });
            document.querySelectorAll('.login-form-input').forEach(el => {
                el.classList.remove('error');
            });
            
            // Отключаем кнопку отправки
            submitBtn.disabled = true;
            submitBtn.textContent = 'Вход...';
            
            // Отправляем запрос
            fetch('/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Успешный вход - перезагружаем страницу
                    window.location.reload();
                } else {
                    // Показываем ошибки
                    if (data.errors && Array.isArray(data.errors)) {
                        data.errors.forEach(error => {
                            if (error.includes('email') || error.includes('Email')) {
                                document.getElementById('loginEmailError').textContent = error;
                                document.getElementById('loginEmailError').classList.add('show');
                                emailInput.classList.add('error');
                            } else if (error.includes('пароль') || error.includes('password')) {
                                document.getElementById('loginPasswordError').textContent = error;
                                document.getElementById('loginPasswordError').classList.add('show');
                                passwordInput.classList.add('error');
                            } else {
                                document.getElementById('loginGeneralError').textContent = error;
                                document.getElementById('loginGeneralError').classList.add('show');
                            }
                        });
                    } else {
                        document.getElementById('loginGeneralError').textContent = data.message || 'Ошибка входа';
                        document.getElementById('loginGeneralError').classList.add('show');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loginGeneralError').textContent = 'Произошла ошибка при входе. Пожалуйста, попробуйте позже.';
                document.getElementById('loginGeneralError').classList.add('show');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Войти';
            });
        }

        // Contact Modal Functions
        function openContactModal() {
            const modal = document.getElementById('contactModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeContactModal() {
            const modal = document.getElementById('contactModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function closeContactModalOnOverlay(event) {
            if (event.target.id === 'contactModal') {
                closeContactModal();
            }
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const loginModal = document.getElementById('loginModal');
                const contactModal = document.getElementById('contactModal');
                
                if (loginModal && loginModal.classList.contains('active')) {
                    closeLoginModal();
                } else if (contactModal && contactModal.classList.contains('active')) {
                    closeContactModal();
                }
            }
        });

        // Callback Request Form
        function submitCallbackRequest(event) {
            event.preventDefault();
            
            const form = document.getElementById('callbackForm');
            const submitBtn = document.getElementById('callbackSubmit');
            const successMsg = document.getElementById('callbackSuccess');
            const propertyId = <?php echo $propertyId; ?>;
            
            // Get form data
            const formData = {
                name: document.getElementById('callbackName').value.trim(),
                phone: document.getElementById('callbackPhone').value.trim(),
                email: document.getElementById('callbackEmail').value.trim(),
                property_id: propertyId
            };
            
            // Clear previous errors
            document.querySelectorAll('.form-error').forEach(el => {
                el.classList.remove('show');
                el.textContent = '';
            });
            document.querySelectorAll('.form-input').forEach(el => {
                el.classList.remove('error');
            });
            successMsg.classList.remove('show');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
            
            // Send request
            fetch('/api/callback_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    successMsg.textContent = data.message;
                    successMsg.classList.add('show');
                    
                    // Reset form
                    form.reset();
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        successMsg.classList.remove('show');
                    }, 5000);
                } else {
                    // Show errors
                    if (data.errors && Array.isArray(data.errors)) {
                        data.errors.forEach(error => {
                            if (error.includes('Имя')) {
                                document.getElementById('nameError').textContent = error;
                                document.getElementById('nameError').classList.add('show');
                                document.getElementById('callbackName').classList.add('error');
                            } else if (error.includes('Телефон')) {
                                document.getElementById('phoneError').textContent = error;
                                document.getElementById('phoneError').classList.add('show');
                                document.getElementById('callbackPhone').classList.add('error');
                            } else if (error.includes('email')) {
                                document.getElementById('emailError').textContent = error;
                                document.getElementById('emailError').classList.add('show');
                                document.getElementById('callbackEmail').classList.add('error');
                            }
                        });
                    } else {
                        alert(data.message || 'Произошла ошибка при отправке запроса');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при отправке запроса. Пожалуйста, попробуйте позже.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Отправить запрос';
            });
        }
    </script>
</body>
</html>

