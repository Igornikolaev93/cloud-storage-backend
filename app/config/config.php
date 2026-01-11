<?php
declare(strict_types=1);

/**
 * Основной конфигурационный файл приложения.
 * Содержит настройки для подключения к базе данных, почты и другие глобальные параметры.
 */

// Установка внутренней кодировки для многобайтовых строк
mb_internal_encoding('UTF-8');

// --- Часовой пояс ---
define('APP_TIMEZONE', 'UTC');
date_default_timezone_set(APP_TIMEZONE);

// --- Режим отладки ---
// Установите в `true` для отображения подробных ошибок в процессе разработки.
// В производственной среде ОБЯЗАТЕЛЬНО установите в `false`.
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// --- Настройки базы данных (PostgreSQL) ---
// Все параметры собраны в один массив, как того требует класс Database.php
define('DB_CONFIG', [
    'driver'   => 'pgsql', // Драйвер базы данных
    'host'     => 'localhost', // Хост
    'port'     => '5432',      // Порт (стандартный для PostgreSQL)
    'dbname'   => 'cloud_storage', // Имя базы данных
    'username' => 'postgres',  // Имя пользователя
    'password' => 'root',      // Пароль пользователя
    'charset'  => 'utf8',      // Кодировка
    'options'  => [
        // Режим обработки ошибок: выбрасывать исключения (рекомендуется)
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Режим выборки по умолчанию: ассоциативный массив
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Отключение эмуляции подготовленных запросов для безопасности
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
]);

// --- Настройки почты (для сброса пароля) ---
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@example.com');
define('MAIL_PASS', 'your-email-password');
define('MAIL_FROM_ADDRESS', 'no-reply@example.com');
define('MAIL_FROM_NAME', 'Cloud Storage');

// --- Прочие настройки ---
// URL вашего приложения (используется для генерации ссылок, например, для сброса пароля)
define('APP_URL', 'http://localhost/cloud-storage-backend');
