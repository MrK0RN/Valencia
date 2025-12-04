<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../classes/PropertyFormHandler.php';
require_once __DIR__ . '/../../classes/Property.php';
require_once __DIR__ . '/../../classes/Session.php';

$pageTitle = 'Создать объект';
$currentPage = 'properties';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!Session::validateCsrfToken($csrfToken)) {
        $error = 'Ошибка безопасности. Пожалуйста, попробуйте снова.';
    } else {
        try {
            $property = PropertyFormHandler::handleForm($_POST, $_FILES);
            $success = 'Объект успешно создан!';
            header('Location: /admin/properties/edit.php?id=' . $property->id . '&success=created');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$csrfToken = Session::generateCsrfToken();
$mapsConfig = require __DIR__ . '/../../config/maps.php';
$googleMapsApiKey = $mapsConfig['google_maps_api_key'];
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Создать объект недвижимости</h1>
    <a href="/admin/properties/index.php" class="btn btn-secondary">Назад к списку</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="property-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
    
    <div class="form-grid">
        <div class="form-section">
            <h2>Основная информация</h2>
            
            <div class="form-group">
                <label for="title">Название объекта *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="price">Цена (€) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>

            <div class="form-group">
                <label for="video_url">Видео URL</label>
                <input type="url" id="video_url" name="video_url" 
                       placeholder="https://www.youtube.com/watch?v=...">
                <small>Ссылка на видео объекта (необязательно).</small>
            </div>
        </div>

        <div class="form-section">
            <h2>Адрес и расположение</h2>
            
            <div class="form-group">
                <label for="address_full">Полный адрес</label>
                <input type="text" id="address_full" name="address_full">
            </div>

            <div class="form-group">
                <label for="address_district">Район</label>
                <input type="text" id="address_district" name="address_district">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="show_exact_address" value="1">
                    Показывать точный адрес
                </label>
            </div>

            <div class="form-group">
                <label for="lat">Широта</label>
                <input type="number" id="lat" name="lat" step="0.00000001" 
                       min="-90" max="90">
            </div>

            <div class="form-group">
                <label for="lng">Долгота</label>
                <input type="number" id="lng" name="lng" step="0.00000001" 
                       min="-180" max="180">
            </div>

            <div class="form-group">
                <button type="button" id="selectOnMap" class="btn btn-secondary">
                    <i class="fas fa-map-marker-alt"></i> Выбрать на карте
                </button>
            </div>

            <div id="map" style="height: 300px; width: 100%; margin-top: 10px; display: none;"></div>
        </div>

        <div class="form-section">
            <h2>Характеристики</h2>
            
            <div class="form-group">
                <label for="area_total">Общая площадь (м²)</label>
                <input type="number" id="area_total" name="area_total" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="area_living">Жилая площадь (м²)</label>
                <input type="number" id="area_living" name="area_living" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="area_kitchen">Площадь кухни (м²)</label>
                <input type="number" id="area_kitchen" name="area_kitchen" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="rooms">Количество комнат</label>
                <input type="number" id="rooms" name="rooms" min="0">
            </div>

            <div class="form-group">
                <label for="floor">Этаж</label>
                <input type="number" id="floor" name="floor">
            </div>

            <div class="form-group">
                <label for="total_floors">Всего этажей</label>
                <input type="number" id="total_floors" name="total_floors" min="1">
            </div>
        </div>

        <div class="form-section">
            <h2>Дополнительные характеристики</h2>
            
            <div class="features-grid">
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_BALCONY; ?>]" value="1"> Балкон</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_PARKING; ?>]" value="1"> Парковка</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_ELEVATOR; ?>]" value="1"> Лифт</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_FURNISHED; ?>]" value="1"> Мебель</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_AIR_CONDITIONING; ?>]" value="1"> Кондиционер</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_HEATING; ?>]" value="1"> Отопление</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_POOL; ?>]" value="1"> Бассейн</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_GARDEN; ?>]" value="1"> Сад</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_TERRACE; ?>]" value="1"> Терраса</label>
                <label><input type="checkbox" name="features[<?php echo Property::FEATURE_STORAGE; ?>]" value="1"> Кладовая</label>
            </div>
        </div>

        <div class="form-section">
            <h2>Фотографии</h2>
            
            <div class="form-group">
                <label for="photos">Загрузить фотографии</label>
                <input type="file" id="photos" name="photos[]" multiple accept="image/*">
                <small>Можно выбрать несколько файлов. Форматы: JPEG, PNG, WebP. Макс. размер: 10MB</small>
            </div>

            <div id="photoPreview" class="photo-preview"></div>
        </div>

        <div class="form-section">
            <h2>Настройки</h2>
            
            <div class="form-group">
                <label for="status">Статус</label>
                <select id="status" name="status">
                    <option value="<?php echo Property::STATUS_ACTIVE; ?>">Активен</option>
                    <option value="<?php echo Property::STATUS_SOLD; ?>">Продан</option>
                    <option value="<?php echo Property::STATUS_HIDDEN; ?>">Скрыт</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1">
                    Избранный объект (показывать на главной)
                </label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="repair_needed" value="1">
                    Требуется ремонт
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Сохранить
        </button>
        <a href="/admin/properties/index.php" class="btn btn-secondary">Отмена</a>
    </div>
</form>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($googleMapsApiKey); ?>&loading=async" defer></script>
<script>
// Инициализация карты будет добавлена в admin.js
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

