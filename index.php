<?php
declare(strict_types=1);

// Включение отображения ошибок (только для разработки)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Автозагрузка Composer
require_once __DIR__ . '/vendor/autoload.php';

// Загрузка конфигураций
// Этот файл теперь также отвечает за запуск сессии
require_once __DIR__ . '/app/config/config.php';

// --- Определение константы для папки загрузок ---
// Убедимся, что константа еще не определена
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/uploads');
}
// Создаем папку, если она не существует
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// --- Система маршрутизации ---

// Загрузка маршрутов, которые теперь содержат `use App\Utils\Router;`
require_once __DIR__ . '/app/routes.php';

// Получаем URI и метод запроса
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Запускаем маршрутизатор
App\Utils\Router::dispatch($requestUri, $requestMethod);
