<?php
declare(strict_types=1);

// Увеличиваем максимальное время выполнения скрипта
set_time_limit(300); // 5 минут

// Включаем отображение всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<pre>";

echo "Starting database migration...\n";

// Подключаем необходимые файлы
// Важно: убедитесь, что пути к файлам указаны верно относительно migrate.php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/models/Database.php';

$sqlFilePath = __DIR__ . '/sql/database.pgsql.sql';

if (!file_exists($sqlFilePath)) {
    die("ERROR: SQL file not found at: {$sqlFilePath}\n");
}

$sql = file_get_contents($sqlFilePath);

if (empty($sql)) {
    die("ERROR: SQL file is empty.\n");
}

try {
    // Получаем соединение с базой данных из вашего конфига
    echo "Connecting to the database...\n";
    $pdo = App\Models\Database::getConnection();
    echo "Connection successful.\n";

    // Выполняем SQL-скрипт
    echo "Executing migration script...\n";
    $pdo->exec($sql);
    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    // Выводим ошибку, если что-то пошло не так с базой данных
    die("DATABASE ERROR: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    // Выводим другие возможные ошибки
    die("GENERAL ERROR: " . $e->getMessage() . "\n");
}

echo "</pre>";
