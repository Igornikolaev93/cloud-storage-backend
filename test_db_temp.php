<?php
/**
 * Тест с новым паролем
 */

require_once 'app/config/config.php';

echo "🔐 ТЕСТ С НОВЫМ ПАРОЛЕМ\n";
echo "=======================\n\n";

echo "Параметры подключения:\n";
echo "Host: " . DB_CONFIG['host'] . "\n";
echo "Port: " . DB_CONFIG['port'] . "\n";
echo "Database: " . DB_CONFIG['dbname'] . "\n";
echo "Username: " . DB_CONFIG['username'] . "\n";
echo "Password: " . str_repeat('*', strlen(DB_CONFIG['password'])) . "\n\n";

// Проверка порта
echo "Проверка порта...\n";
$fp = @fsockopen(DB_CONFIG['host'], DB_CONFIG['port'], $errno, $errstr, 5);
if ($fp) {
    echo "✅ Порт " . DB_CONFIG['port'] . " открыт\n";
    fclose($fp);
} else {
    echo "❌ Порт закрыт: " . $errstr . "\n\n";
    echo "Пробуем альтернативный порт 5432...\n";
    
    $fp = @fsockopen(DB_CONFIG['host'], 5432, $errno, $errstr, 5);
    if ($fp) {
        echo "✅ Порт 5432 открыт\n";
        fclose($fp);
        // Меняем порт на 5432
        DB_CONFIG['port'] = 5432;
    } else {
        echo "❌ Порт 5432 тоже закрыт\n";
    }
}
echo "\n";

// Попытка подключения
echo "Подключение к базе данных...\n";

try {
    $dsn = DB_CONFIG['driver'] . ':host=' . DB_CONFIG['host'] . 
           ';port=' . DB_CONFIG['port'] . 
           ';dbname=' . DB_CONFIG['dbname'] . 
           ';sslmode=require';
    
    $start = microtime(true);
    $pdo = new PDO($dsn, DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['options']);
    $time = round((microtime(true) - $start) * 1000, 2);
    
    echo "✅ ПОДКЛЮЧЕНИЕ УСПЕШНО! (время: " . $time . " мс)\n\n";
    
    // Получаем информацию
    $stmt = $pdo->query("SELECT current_user, current_database(), version()");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Информация:\n";
    echo "  Пользователь: " . $row['current_user'] . "\n";
    echo "  База данных: " . $row['current_database'] . "\n";
    echo "  Версия: " . substr($row['version'], 0, 60) . "...\n\n";
    
    // Проверяем базу storage
    $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datname = 'storage'");
    if ($stmt->fetch()) {
        echo "✅ База 'storage' существует\n";
    } else {
        echo "❌ База 'storage' не существует\n";
        echo "Создаем базу 'storage'...\n";
        
        try {
            $pdo->exec("CREATE DATABASE storage");
            echo "✅ База 'storage' создана\n";
        } catch (PDOException $e) {
            echo "❌ Ошибка создания: " . $e->getMessage() . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Диагностика:\n";
    echo "1. Проверьте, добавлен ли IP 95.24.37.162 в Supabase\n";
    echo "2. Проверьте правильность username (postgres.vvqrogorxkspdudypriy)\n";
    echo "3. Попробуйте порт 5432 вместо 6543\n";
}