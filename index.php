<?php
declare(strict_types=1);

// Включение отображения ошибок (только для разработки)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Стартуем сессию
session_start();

// Автозагрузка Composer
require_once __DIR__ . '/vendor/autoload.php';

// Загрузка конфигураций
require_once __DIR__ . '/app/config/config.php';

// --- Определение константы для папки загрузок ---
define('UPLOAD_DIR', __DIR__ . '/uploads');
// Создаем папку, если она не существует
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// --- Новая система маршрутизации ---
use App\Utils\Router;

// Загрузка маршрутов
require_once __DIR__ . '/app/routes.php';

// Получаем URI и метод запроса
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Обработка случая, когда проект находится в подпапке
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && $basePath !== '\\') {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Запускаем маршрутизатор
Router::dispatch($requestUri, $requestMethod);
