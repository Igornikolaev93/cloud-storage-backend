<?php
declare(strict_types=1);

// Включение отображения ошибок (только для разработки)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Автозагрузка Composer
require_once __DIR__ . '/vendor/autoload.php';

// Загрузка конфигураций
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/models/Database.php';

// --- Definitive Automatic Database Setup ---
try {
    $pdo = App\Models\Database::getConnection();
    // This more robust check verifies if the schema is up-to-date.
    // The 'updated_at' column was added in the correct schema.
    $pdo->query("SELECT `id`, `updated_at` FROM `directories` LIMIT 1");
} catch (PDOException $e) {
    // If the query fails, the database is either missing or has the old, broken schema.
    // We must run the setup script to create/recreate the tables correctly.
    try {
        // Capture output to prevent it from breaking the redirect.
        ob_start();
        require __DIR__ . '/database_setup.php';
        ob_end_clean();

        // Reload the page to ensure the application uses the new database.
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $setupException) {
        // If the setup itself fails, there is a critical configuration error.
        die("FATAL ERROR: Automatic database setup failed. Please check your database configuration and permissions. Details: " . $setupException->getMessage());
    }
}

// Определение константы для папки загрузок
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/uploads');
}
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// --- ЗАГРУЗКА ПРАВИЛЬНЫХ МАРШРУТОВ ---
require_once __DIR__ . '/app/routes.php';

// Получаем URI и метод запроса
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Запускаем маршрутизатор
App\Utils\Router::dispatch($requestUri, $requestMethod);
