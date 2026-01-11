<?php
declare(strict_types=1);

// --- Настройки сессии ---
// Устанавливаем параметры сессии ПЕРЕД ее запуском

// Установим имя сессии, чтобы избежать конфликтов
session_name('CloudStorageSession'); 

// Устанавливаем параметры cookie для сессии
session_set_cookie_params([
    'lifetime' => 3600, // 1 час
    'path' => '/',
    // 'domain' => '.yourdomain.com', // Раскомментируйте, если есть домен
    'secure' => isset($_SERVER['HTTPS']), // true, если используется HTTPS
    'httponly' => true, // Защита от XSS
    'samesite' => 'Lax' // Защита от CSRF
]);

// --- Запуск сессии ---
// Запускаем сессию только если она еще не активна
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Настройки базы данных ---
// Замените на ваши реальные данные для подключения к PostgreSQL
define('DB_HOST', 'localhost');
//define('DB_PORT', '5432'); // можно не указывать, если используется порт по умолчанию
define('DB_NAME', 'cloud_storage');
define('DB_USER', 'postgres');
define('DB_PASS', 'root'); // Укажите ваш пароль

// --- Настройки почты (для сброса пароля) ---
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@example.com');
define('MAIL_PASS', 'your-email-password');
define('MAIL_FROM_ADDRESS', 'no-reply@example.com');
define('MAIL_FROM_NAME', 'Cloud Storage');
