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

// --- Automatic Database Setup ---
try {
    $pdo = App\Models\Database::getConnection();
    // Check if a key table (e.g., files) exists.
    $pdo->query("SELECT id FROM files LIMIT 1");
} catch (PDOException $e) {
    // If the query fails, it's very likely the tables are not set up.
    // Run the setup script silently and then reload the page.
    try {
        // Capture output to prevent it from messing up the page
        ob_start();
        require __DIR__ . '/database_setup.php';
        ob_end_clean();

        // Reload the page to see the changes
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $setupException) {
        // If the setup itself fails, we have a bigger problem.
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
