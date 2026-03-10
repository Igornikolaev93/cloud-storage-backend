<?php
/**
 * Тестирование Shared Pooler подключения
 */

require_once 'app/config/config.php';

echo "🔌 Тестирование Shared Pooler (IPv4)\n";
echo "====================================\n\n";

echo "Конфигурация:\n";
echo "Host: " . DB_CONFIG['host'] . "\n";
echo "Port: " . DB_CONFIG['port'] . "\n";
echo "Database: " . DB_CONFIG['dbname'] . "\n";
echo "Username: " . DB_CONFIG['username'] . "\n";
echo "Password length: " . strlen(DB_CONFIG['password']) . "\n\n";

// Проверка IPv4
echo "📡 Проверка IPv4:\n";
$ip = gethostbyname(DB_CONFIG['host']);
if ($ip !== DB_CONFIG['host']) {
    echo "✅ IPv4 адрес: {$ip}\n\n";
} else {
    echo "❌ Нет IPv4 адреса\n\n";
}

// Проверка порта
echo "🔌 Проверка порта " . DB_CONFIG['port'] . ":\n";
$fp = @fsockopen(DB_CONFIG['host'], DB_CONFIG['port'], $errno, $errstr, 5);
if ($fp) {
    echo "✅ Порт открыт\n";
    fclose($fp);
    echo "\n";
} else {
    echo "❌ Порт закрыт: {$errstr}\n\n";
}

// Подключение
try {
    echo "🔄 Подключение к shared pooler...\n";
    
    $dsn = sprintf(
        '%s:host=%s;port=%s;dbname=%s;sslmode=require',
        DB_CONFIG['driver'],
        DB_CONFIG['host'],
        DB_CONFIG['port'],
        DB_CONFIG['dbname']
    );
    
    $start = microtime(true);
    $pdo = new PDO($dsn, DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['options']);
    $time = round((microtime(true) - $start) * 1000, 2);
    
    echo "✅ ПОДКЛЮЧЕНИЕ УСПЕШНО! (время: {$time}мс)\n\n";
    
    // Информация о подключении
    $stmt = $pdo->query('SELECT current_user, current_database(), version()');
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Информация:\n";
    echo "  Пользователь: " . $info['current_user'] . "\n";
    echo "  База данных: " . $info['current_database'] . "\n";
    echo "  Версия: " . substr($info['version'], 0, 60) . "...\n\n";
    
    // Проверка базы storage
    echo "💾 Проверка базы 'storage':\n";
    $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datname = 'storage'");
    if ($stmt->fetch()) {
        echo "  ✅ База 'storage' существует\n";
    } else {
        echo "  Создание базы 'storage'...\n";
        $pdo->exec('CREATE DATABASE storage');
        echo "  ✅ База 'storage' создана\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Возможные решения:\n";
    echo "1. Попробуйте порт 5432 вместо 6543\n";
    echo "2. Проверьте username (должен быть postgres.vvqrogorxkspdudypriy)\n";
    echo "3. Сбросьте пароль в Supabase Dashboard\n";
    echo "4. Добавьте ваш IP в разрешенные\n";
} 