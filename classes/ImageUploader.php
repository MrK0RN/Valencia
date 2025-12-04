<?php

require_once __DIR__ . '/Database.php';

class ImageUploader
{
    protected static $db = null;
    protected static $uploadDir = 'uploads/properties/';
    protected static $thumbDir = 'uploads/properties/thumbs/';
    protected static $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    protected static $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    protected static $maxFileSize = 10485760; // 10MB
    protected static $defaultThumbWidth = 400; // Ширина превью по умолчанию

    /**
     * Получить экземпляр Database
     * 
     * @return Database
     */
    protected static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = new Database();
        }
        return self::$db;
    }

    /**
     * Валидация файла изображения
     * 
     * @param array $file Массив файла из $_FILES
     * @return array Массив ошибок (пустой если валидация прошла)
     */
    public static function validate(array $file): array
    {
        $errors = [];

        // Проверка наличия файла
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Файл не был загружен';
            return $errors;
        }

        // Проверка ошибок загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Ошибка загрузки файла: ' . self::getUploadErrorMessage($file['error']);
            return $errors;
        }

        // Проверка размера файла
        if ($file['size'] > self::$maxFileSize) {
            $errors[] = 'Файл слишком большой. Максимальный размер: ' . (self::$maxFileSize / 1048576) . 'MB';
        }

        // Проверка типа файла через MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::$allowedMimeTypes)) {
            $errors[] = 'Недопустимый тип файла: ' . $mimeType . '. Разрешены: JPEG, PNG, WebP';
        }

        // Проверка расширения
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            $errors[] = 'Недопустимое расширение файла: ' . $extension;
        }

        // Проверка, что это действительно изображение
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'Файл не является изображением';
        }

        return $errors;
    }

    /**
     * Получить сообщение об ошибке загрузки
     * 
     * @param int $errorCode Код ошибки
     * @return string Сообщение об ошибке
     */
    protected static function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер, разрешенный в php.ini',
            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер, указанный в форме',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла была остановлена расширением PHP',
        ];

        return $messages[$errorCode] ?? 'Неизвестная ошибка';
    }

    /**
     * Создание превью изображения
     * 
     * @param string $source_path Путь к исходному изображению
     * @param string $thumb_path Путь для сохранения превью
     * @param int $max_width Максимальная ширина превью
     * @return bool true при успехе
     * @throws Exception При ошибке
     */
    public static function createThumbnail(string $source_path, string $thumb_path, int $max_width = null): bool
    {
        if ($max_width === null) {
            $max_width = self::$defaultThumbWidth;
        }

        if (!file_exists($source_path)) {
            throw new Exception('Исходный файл не найден: ' . $source_path);
        }

        // Получаем информацию об изображении
        $imageInfo = @getimagesize($source_path);
        if ($imageInfo === false) {
            throw new Exception('Не удалось определить тип изображения');
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Если исходное изображение меньше максимальной ширины, просто копируем
        if ($sourceWidth <= $max_width) {
            $thumbDir = dirname($thumb_path);
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }
            return copy($source_path, $thumb_path);
        }

        // Вычисляем размеры превью с сохранением пропорций
        $ratio = $sourceWidth / $sourceHeight;
        $thumbHeight = (int) ($max_width / $ratio);

        // Создаем изображение из исходного файла
        $sourceImage = self::createImageFromFile($source_path, $mimeType);
        if ($sourceImage === false) {
            throw new Exception('Не удалось создать изображение из файла');
        }

        // Создаем новое изображение для превью
        $thumbImage = imagecreatetruecolor($max_width, $thumbHeight);

        // Включаем альфа-канал для PNG и WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefill($thumbImage, 0, 0, $transparent);
        }

        // Копируем и изменяем размер
        imagecopyresampled(
            $thumbImage,
            $sourceImage,
            0, 0, 0, 0,
            $max_width,
            $thumbHeight,
            $sourceWidth,
            $sourceHeight
        );

        // Создаем директорию для превью если не существует
        $thumbDir = dirname($thumb_path);
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        // Сохраняем превью
        $saved = self::saveImage($thumbImage, $thumb_path, $mimeType);

        // Освобождаем память
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return $saved;
    }

    /**
     * Создать изображение из файла
     * 
     * @param string $filepath Путь к файлу
     * @param string $mimeType MIME-тип
     * @return resource|false Ресурс изображения или false
     */
    protected static function createImageFromFile(string $filepath, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                return imagecreatefrompng($filepath);
            case 'image/webp':
                return imagecreatefromwebp($filepath);
            default:
                return false;
        }
    }

    /**
     * Сохранить изображение в файл
     * 
     * @param resource $image Ресурс изображения
     * @param string $filepath Путь для сохранения
     * @param string $mimeType MIME-тип
     * @return bool true при успехе
     */
    protected static function saveImage($image, string $filepath, string $mimeType): bool
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagejpeg($image, $filepath, 85); // Качество 85%
            case 'image/png':
                return imagepng($image, $filepath, 6); // Сжатие 6 (0-9)
            case 'image/webp':
                return imagewebp($image, $filepath, 85); // Качество 85%
            default:
                return false;
        }
    }

    /**
     * Загрузка нескольких фотографий для объекта недвижимости
     * 
     * @param array $files Массив файлов из $_FILES или массив массивов файлов
     * @param int $property_id ID объекта недвижимости
     * @return array Массив путей к загруженным изображениям
     * @throws Exception При ошибке
     */
    public static function upload(array $files, int $property_id): array
    {
        $uploadedPaths = [];
        $baseDir = __DIR__ . '/../' . self::$uploadDir;
        $thumbBaseDir = __DIR__ . '/../' . self::$thumbDir;

        // Создаем директории если не существуют
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        if (!is_dir($thumbBaseDir)) {
            mkdir($thumbBaseDir, 0755, true);
        }

        // Нормализуем массив файлов
        $normalizedFiles = self::normalizeFilesArray($files);

        foreach ($normalizedFiles as $file) {
            // Валидация
            $errors = self::validate($file);
            if (!empty($errors)) {
                throw new Exception('Ошибка валидации файла ' . ($file['name'] ?? 'unknown') . ': ' . implode(', ', $errors));
            }

            // Генерируем уникальное имя файла
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'prop_' . $property_id . '_' . uniqid('', true) . '.' . $extension;
            $filepath = $baseDir . $filename;
            $relativePath = self::$uploadDir . $filename;

            // Перемещаем файл
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Не удалось переместить файл: ' . $file['name']);
            }

            // Создаем превью
            $thumbFilename = 'thumb_' . $filename;
            $thumbPath = $thumbBaseDir . $thumbFilename;
            $thumbRelativePath = self::$thumbDir . $thumbFilename;

            try {
                self::createThumbnail($filepath, $thumbPath);
            } catch (Exception $e) {
                // Если не удалось создать превью, удаляем оригинал
                unlink($filepath);
                throw new Exception('Ошибка создания превью: ' . $e->getMessage());
            }

            // Сохраняем в БД
            $db = self::getDb();
            $sql = "INSERT INTO property_photos (property_id, image_path, sort_order) 
                    VALUES (:property_id, :image_path, 
                    (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM property_photos WHERE property_id = :property_id2))";
            $db->query($sql, [
                'property_id' => $property_id,
                'property_id2' => $property_id,
                'image_path' => $relativePath
            ]);

            $uploadedPaths[] = [
                'original' => $relativePath,
                'thumbnail' => $thumbRelativePath
            ];
        }

        return $uploadedPaths;
    }

    /**
     * Нормализация массива файлов из $_FILES
     * 
     * @param array $files Массив файлов
     * @return array Нормализованный массив
     */
    public static function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        // Если это массив из $_FILES с несколькими файлами
        if (isset($files['name']) && is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue; // Пропускаем пустые файлы
                }
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
            }
        }
        // Если это один файл
        elseif (isset($files['tmp_name']) && is_uploaded_file($files['tmp_name'])) {
            $normalized[] = $files;
        }
        // Если это массив массивов
        else {
            foreach ($files as $file) {
                if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                    $normalized[] = $file;
                }
            }
        }

        return $normalized;
    }

    /**
     * Удаление файла изображения (оригинал и превью)
     * 
     * @param string $image_path Путь к изображению (относительный)
     * @return bool true при успехе
     */
    public static function delete(string $image_path): bool
    {
        $baseDir = __DIR__ . '/../';
        $fullPath = $baseDir . $image_path;

        // Удаляем оригинал
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Удаляем превью
        $thumbPath = self::getThumbnailPath($image_path);
        $fullThumbPath = $baseDir . $thumbPath;

        if (file_exists($fullThumbPath)) {
            unlink($fullThumbPath);
        }

        // Удаляем запись из БД
        try {
            $db = self::getDb();
            $sql = "DELETE FROM property_photos WHERE image_path = :image_path";
            $db->query($sql, ['image_path' => $image_path]);
        } catch (Exception $e) {
            error_log("ImageUploader::delete() DB error: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Изменение порядка фотографий
     * 
     * @param int $property_id ID объекта недвижимости
     * @param array $photo_order Массив ID фотографий в новом порядке [3, 1, 5, 2, ...]
     * @return bool true при успехе
     * @throws Exception При ошибке
     */
    public static function reorder(int $property_id, array $photo_order): bool
    {
        if (empty($photo_order)) {
            return false;
        }

        $db = self::getDb();
        $db->beginTransaction();

        try {
            foreach ($photo_order as $index => $photo_id) {
                $sortOrder = $index + 1;
                $sql = "UPDATE property_photos 
                        SET sort_order = :sort_order 
                        WHERE id = :photo_id AND property_id = :property_id";
                $db->query($sql, [
                    'sort_order' => $sortOrder,
                    'photo_id' => (int) $photo_id,
                    'property_id' => $property_id
                ]);
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollback();
            error_log("ImageUploader::reorder() error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Получить путь к превью изображения
     * 
     * @param string $originalPath Путь к оригинальному изображению (относительный)
     * @return string Путь к превью (относительный)
     */
    public static function getThumbnailPath(string $originalPath): string
    {
        $filename = basename($originalPath);
        $thumbFilename = 'thumb_' . $filename;
        
        // Заменяем директорию uploads/properties/ на uploads/properties/thumbs/
        $thumbPath = str_replace(self::$uploadDir, self::$thumbDir, $originalPath);
        
        // Заменяем имя файла на thumb_имя_файла
        return str_replace($filename, $thumbFilename, $thumbPath);
    }
}

