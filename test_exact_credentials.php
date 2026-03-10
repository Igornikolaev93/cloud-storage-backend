<?php
/**
 * Тест с точными данными из дашборда Supabase
 */

// Точные данные из строки подключения
$host = 'aws-1-eu-west-1.pooler.supabase.com';
$port = 6543;
$database = 'postgres';
$username = 'postgres.vvqrogorxkspdudypriy';
$password = 'pppx37CRZxhSUqCt';

echo "🔌 ТЕСТ С ТОЧНЫМИ ДАННЫМИ ИЗ ДАШБОРДА\n";
echo "======================================\n\n";

echo "Параметры подключения:\n";
echo "Host: {$host}\n";
echo "Port: {$port}\n";
echo "Database: {$database}\n";
echo "Username: {$username}\n";
echo "Password length: " . strlen($password) . "\n\n";

// Проверка DNS
echo "📡 Проверка DNS:\n";
$ip = gethostbyname($host);
if ($ip !== $host) {
    echo "✅ Хост резолвится в IP: {$ip}\n";
} else {
    echo "❌ Хост не резолвится\n";
}
echo "\n";

// Проверка порта
echo "🔌 Проверка порта {$port}:\n";
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
if ($fp) {
    echo "✅ Порт открыт\n";
    fclose($fp);
} else {
    echo "❌ Порт закрыт: {$errstr}\n";
}
echo "\n";

// Подключение к базе
echo "🔄 Подключение к базе данных...\n";

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";
    
    $start = microtime(true);
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    $time = round((microtime(true) - $start) * 1000, 2);
    
    echo "✅ ПОДКЛЮЧЕНИЕ УСПЕШНО! (время: {$time} мс)\n\n";
    
    // Получаем информацию
    $stmt = $pdo->query("SELECT current_user, current_database(), version()");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Информация о подключении:\n";
    echo "  Пользователь: " . $row['current_user'] . "\n";
    echo "  База данных: " . $row['current_database'] . "\n";
    echo "  Версия: " . substr($row['version'], 0, 60) . "...\n\n";
    
    // Проверяем базу storage
    echo "💾 Проверка базы 'storage':\n";
    $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datname = 'storage'");
    if ($stmt->fetch()) {
        echo "  ✅ База 'storage' существует\n";
    } else {
        echo "  Создание базы 'storage'...\n";
        $pdo->exec("CREATE DATABASE storage");
        echo "  ✅ База 'storage' создана\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'Tenant or user not found') !== false) {
        echo "🔧 Проблема с аутентификацией:\n";
        echo "1. Проверьте, что проект разморожен в дашборде\n";
        echo "2. Убедитесь, что пароль правильный\n";
        echo "3. Проверьте project ref: vvqrogorxkspdudypriy\n";
    }
}