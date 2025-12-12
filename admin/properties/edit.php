<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../classes/PropertyFormHandler.php';
require_once __DIR__ . '/../../classes/Property.php';
require_once __DIR__ . '/../../classes/PropertyAdmin.php';
require_once __DIR__ . '/../../classes/Session.php';

$pageTitle = 'Редактировать объект';
$currentPage = 'properties';

$error = '';
$success = '';

$propertyId = intval($_GET['id'] ?? 0);
if (!$propertyId) {
    header('Location: /admin/properties/index.php');
    exit;
}

$property = Property::find($propertyId);
if (!$property) {
    header('Location: /admin/properties/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!Session::validateCsrfToken($csrfToken)) {
        $error = 'Ошибка безопасности. Пожалуйста, попробуйте снова.';
    } else {
        try {
            $property = PropertyFormHandler::handleForm($_POST, $_FILES, $propertyId);
            $success = 'Объект успешно обновлен!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$formData = PropertyFormHandler::getFormData($property);
$csrfToken = Session::generateCsrfToken();
$mapsConfig = require __DIR__ . '/../../config/maps.php';
$googleMapsApiKey = $mapsConfig['google_maps_api_key'];
$featureLabels = Property::getFeatureLabels('ru');
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Редактировать объект: <?php echo htmlspecialchars($property->title); ?></h1>
    <a href="/admin/properties/index.php" class="btn btn-secondary">Назад к списку</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="property-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
    
    <div class="form-grid">
        <div class="form-section">
            <h2>Основная информация</h2>
            
            <div class="form-group">
                <label for="title">Название объекта *</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo htmlspecialchars($property->title); ?>" required>
            </div>

            <div class="form-group">
                <label for="title_en">Название объекта (EN)</label>
                <input type="text" id="title_en" name="title_en"
                       value="<?php echo htmlspecialchars($property->title_en ?? ''); ?>"
                       placeholder="Title in English">
            </div>

            <div class="form-group">
                <label for="price">Цена (€) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" 
                       value="<?php echo $property->price; ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($property->description ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="description_en">Описание (EN)</label>
                <textarea id="description_en" name="description_en" rows="5" placeholder="Description in English"><?php echo htmlspecialchars($property->description_en ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="video_url">Видео URL</label>
                <input type="url" id="video_url" name="video_url" 
                       value="<?php echo htmlspecialchars($property->video_url ?? ''); ?>">
                <small>Ссылка на видео объекта (необязательно).</small>
            </div>
        </div>

        <div class="form-section">
            <h2>Адрес и расположение</h2>
            
            <div class="form-group">
                <label for="address_full">Полный адрес</label>
                <input type="text" id="address_full" name="address_full" 
                       value="<?php echo htmlspecialchars($property->address_full ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address_district">Район</label>
                <input type="text" id="address_district" name="address_district" 
                       value="<?php echo htmlspecialchars($property->address_district ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="show_exact_address" value="1" 
                           <?php echo $property->show_exact_address ? 'checked' : ''; ?>>
                    Показывать точный адрес
                </label>
            </div>

            <div class="form-group">
                <label for="lat">Широта</label>
                <input type="number" id="lat" name="lat" step="0.00000001" 
                       min="-90" max="90" value="<?php echo $property->lat ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="lng">Долгота</label>
                <input type="number" id="lng" name="lng" step="0.00000001" 
                       min="-180" max="180" value="<?php echo $property->lng ?? ''; ?>">
            </div>

            <div class="form-group">
                <button type="button" id="selectOnMap" class="btn btn-secondary">
                    <i class="fas fa-map-marker-alt"></i> Выбрать на карте
                </button>
            </div>

            <div id="map" style="height: 300px; width: 100%; margin-top: 10px; display: none;"></div>
        </div>

        <div class="form-section">
            <h2>Близость</h2>
            <div class="form-grid two-columns">
                <div class="form-group">
                    <label for="sea_distance_meters">До моря (метры)</label>
                    <input type="number" id="sea_distance_meters" name="sea_distance_meters" min="0" step="1" value="<?php echo htmlspecialchars($property->sea_distance_meters ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="sea_distance_minutes">До моря (минуты)</label>
                    <input type="number" id="sea_distance_minutes" name="sea_distance_minutes" min="0" step="1" value="<?php echo htmlspecialchars($property->sea_distance_minutes ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="metro_distance_meters">До метро (метры)</label>
                    <input type="number" id="metro_distance_meters" name="metro_distance_meters" min="0" step="1" value="<?php echo htmlspecialchars($property->metro_distance_meters ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="metro_distance_minutes">До метро (минуты)</label>
                    <input type="number" id="metro_distance_minutes" name="metro_distance_minutes" min="0" step="1" value="<?php echo htmlspecialchars($property->metro_distance_minutes ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Характеристики</h2>
            
            <div class="form-group">
                <label for="area_total">Общая площадь (м²)</label>
                <input type="number" id="area_total" name="area_total" step="0.01" min="0" 
                       value="<?php echo $property->area_total ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="area_living">Жилая площадь (м²)</label>
                <input type="number" id="area_living" name="area_living" step="0.01" min="0" 
                       value="<?php echo $property->area_living ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="area_kitchen">Площадь кухни (м²)</label>
                <input type="number" id="area_kitchen" name="area_kitchen" step="0.01" min="0" 
                       value="<?php echo $property->area_kitchen ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="rooms">Количество комнат</label>
                <input type="number" id="rooms" name="rooms" min="0" 
                       value="<?php echo $property->rooms ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="floor">Этаж</label>
                <input type="number" id="floor" name="floor" 
                       value="<?php echo $property->floor ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="total_floors">Всего этажей</label>
                <input type="number" id="total_floors" name="total_floors" min="1" 
                       value="<?php echo $property->total_floors ?? ''; ?>">
            </div>
        </div>

        <div class="form-section">
            <h2>Дополнительные характеристики</h2>
            
            <div class="features-grid">
                <?php 
                $features = $formData['features'];
                foreach ($featureLabels as $type => $label): 
                    $checked = isset($features[$type]) && $features[$type] === '1' ? 'checked' : '';
                ?>
                    <label>
                        <input type="checkbox" name="features[<?php echo $type; ?>]" value="1" <?php echo $checked; ?>>
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-section">
            <h2>Фотографии</h2>
            
            <div class="form-group">
                <label for="photos">Загрузить новые фотографии</label>
                <input type="file" id="photos" name="photos[]" multiple accept="image/*">
                <small>Можно выбрать несколько файлов. Форматы: JPEG, PNG, WebP. Макс. размер: 10MB</small>
            </div>

            <div id="photoPreview" class="photo-preview">
                <?php foreach ($formData['photos'] as $photo): ?>
                    <div class="photo-item">
                        <img src="/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Photo">
                        <button type="button" class="btn-remove-photo" data-id="<?php echo $photo['id']; ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-section">
            <h2>Настройки</h2>
            
            <div class="form-group">
                <label for="status">Статус</label>
                <select id="status" name="status">
                    <option value="<?php echo Property::STATUS_ACTIVE; ?>" 
                            <?php echo $property->status === Property::STATUS_ACTIVE ? 'selected' : ''; ?>>
                        Активен
                    </option>
                    <option value="<?php echo Property::STATUS_SOLD; ?>" 
                            <?php echo $property->status === Property::STATUS_SOLD ? 'selected' : ''; ?>>
                        Продан
                    </option>
                    <option value="<?php echo Property::STATUS_HIDDEN; ?>" 
                            <?php echo $property->status === Property::STATUS_HIDDEN ? 'selected' : ''; ?>>
                        Скрыт
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" 
                           <?php echo $property->featured ? 'checked' : ''; ?>>
                    Избранный объект (показывать на главной)
                </label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="repair_needed" value="1" 
                           <?php echo isset($property->repair_needed) && $property->repair_needed ? 'checked' : ''; ?>>
                    Требуется ремонт
                </label>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Сохранить изменения
        </button>
        <a href="/admin/properties/index.php" class="btn btn-secondary">Отмена</a>
    </div>
</form>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($googleMapsApiKey); ?>&loading=async" defer></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

