<?php
declare(strict_types=1);

// Настройки безопасности сессии
define('SESSION_NAME', 'CLOUD_STORAGE_SESSION');
define('SESSION_LIFETIME', 3600); // 1 час

// Инициализация сессии
// Этот блок должен быть в начале, до любого вывода данных
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Режим отладки
define('DEBUG_MODE', true);

// Настройки приложения
define('APP_NAME', 'University Cloud Storage');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://cloud-storage.local');

// Настройки безопасности
define('CSRF_TOKEN_LIFETIME', 1800); // 30 минут
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
define('PASSWORD_HASH_COST', 12);

// Настройки файлов
define('MAX_FILE_SIZE', 2 * 1024 * 1024 * 1024); // 2GB
define('UPLOAD_DIR', __DIR__ . '/../../public/uploads');
define('TEMP_DIR', sys_get_temp_dir() . '/cloud_storage_temp');

// Разрешенные типы файлов
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'text/plain',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/zip',
    'application/x-rar-compressed',
    'application/x-7z-compressed'
]);

// Настройки базы данных
define('DB_CONFIG', [
    'driver' => 'pgsql',
    'host' => 'localhost',
    'database' => 'storage',
    'username' => 'postgres',
    'password' => '1234', // Replace with your PostgreSQL password
    'charset' => 'utf8',
    'port' => 5432,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
]);

// Настройки почты (для восстановления пароля)
define('MAIL_CONFIG', [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'noreply@university.edu',
    'password' => 'your_password',
    'encryption' => 'tls',
    'from_email' => 'noreply@university.edu',
    'from_name' => 'University Cloud Storage'
]);

// Обработка ошибок
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Настройка часового пояса
date_default_timezone_set('Europe/Moscow');

// Создание необходимых директорий
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0775, true);
}
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0775, true);
}

// Функции-помощники
function sanitizeInput(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
