<?php
declare(strict_types=1);

// Устанавливаем внутреннюю кодировку для многобайтовых строк
mb_internal_encoding('UTF-8');

// --- Основные настройки приложения ---

// URL вашего приложения. Замените на ваш реальный домен при развертывании.
define('APP_URL', 'http://localhost:8080'); 

// Абсолютный путь к корневой директории проекта. Используется для инклудов.
define('BASE_PATH', dirname(__DIR__) . '/');

// Название вашего приложения.
define('APP_NAME', 'CloudDrive');

// --- Настройки для работы с JWT (JSON Web Tokens) ---

// Секретный ключ для подписи токенов. Должен быть длинной и сложной строкой.
define('JWT_SECRET', 'your-super-secret-key-for-jwt'); 

// Время жизни токена в секундах (например, 1 час).
define('JWT_EXPIRATION', 3600);

// Алгоритм шифрования, используемый для JWT.
define('JWT_ALGO', 'HS256');

// --- Настройки базы данных (PostgreSQL) ---
// Все параметры собраны в один массив, как того требует класс Database.php
define('DB_CONFIG', [
    'driver'   => 'pgsql', // Драйвер базы данных
    //'host'     => 'db.jiqoibgkivhbnoptlome.supabase.co', // Хост
    'host'     => '123.45.67.89', // Хост
    'port'     => '5432',      // Порт (стандартный для PostgreSQL)
    'dbname'   => 'postgres', // Имя базы данных
    'username' => 'postgres',  // Имя пользователя
    'password' => 'Ybrjkftdbujhm',      // Пароль пользователя
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

// --- Настройки для загрузки файлов ---

// Путь к директории для загружаемых файлов (относительно корневой директории проекта).
define('UPLOAD_DIR', BASE_PATH . 'uploads/');

// Максимально допустимый размер файла для загрузки (в байтах).
// Пример: 10 * 1024 * 1024 = 10MB
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

// Список разрешенных MIME-типов для загрузки.
// 'image/jpeg', 'image/png', 'application/pdf', и т.д.
// Оставьте пустым, чтобы разрешить все типы (не рекомендуется из соображений безопасности).
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'text/plain',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);
