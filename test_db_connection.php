<?php
/**
 * Финальный тест подключения после добавления IP
 */

require_once 'app/config/config.php';

echo "🚀 ФИНАЛЬНЫЙ ТЕСТ ПОДКЛЮЧЕНИЯ
";
echo "==============================

";

echo "Ваш IP: 95.24.37.162
";
echo "Разрешенный IP в Supabase: 95.24.37.162/32

";

echo "📡 Параметры подключения:
";
echo "Host: " . DB_CONFIG['host'] . "
";
echo "Port: " . DB_CONFIG['port'] . "
";
echo "Database: " . DB_CONFIG['dbname'] . "
";
echo "Username: " . DB_CONFIG['username'] . "
";
echo "Password length: " . strlen(DB_CONFIG['password']) . "

";

// Проверка доступности порта
echo "🔌 Проверка порта " . DB_CONFIG['port'] . ":
";
$fp = @fsockopen(DB_CONFIG['host'], DB_CONFIG['port'], $errno, $errstr, 5);
if ($fp) {
    echo "✅ Порт открыт
";
    fclose($fp);
    echo "
";
} else {
    echo "❌ Порт закрыт: {$errstr}

";
}

// Подключение к базе
try {
    echo "🔄 Подключение к базе данных...
";
    
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
    
    echo "✅ ПОДКЛЮЧЕНИЕ УСПЕШНО! (время: {$time}мс)

";
    
    // Информация о подключении
    $info = $pdo->query("SELECT current_user, current_database(), version()")->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Информация:
";
    echo "  Пользователь: " . $info['current_user'] . "
";
    echo "  База данных: " . $info['current_database'] . "
";
    echo "  Версия PostgreSQL: " . substr($info['version'], 0, 60) . "...

";
    
    // Проверка базы storage
    echo "💾 Проверка базы 'storage':
";
    $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datname = 'storage'");
    if ($stmt->fetch()) {
        echo "  ✅ База 'storage' существует

";
    } else {
        echo "  Создание базы 'storage'...
";
        $pdo->exec('CREATE DATABASE storage');
        echo "  ✅ База 'storage' создана

";
    }
    
    // Подключение к storage
    echo "🔄 Подключение к базе 'storage':
";
    $dsn_storage = sprintf(
        '%s:host=%s;port=%s;dbname=storage;sslmode=require',
        DB_CONFIG['driver'],
        DB_CONFIG['host'],
        DB_CONFIG['port']
    );
    
    $pdo_storage = new PDO($dsn_storage, DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['options']);
    echo "  ✅ Подключение к 'storage' успешно

";
    
    // Проверка таблиц
    $tables = $pdo_storage->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "📋 В базе 'storage' нет таблиц
";
        echo "   Запустите: php database_setup.php
";
    } else {
        echo "📋 Таблицы в базе 'storage':
";
        foreach ($tables as $table) {
            $count = $pdo_storage->query("SELECT COUNT(*) FROM \"{$table}\"")->fetchColumn();
            echo "  - {$table}: {$count} записей
";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "

";
    
    echo "🔧 Возможные причины:
";
    echo "1. IP адрес 95.24.37.162 еще не добавлен в Supabase
";
    echo "2. Пароль неправильный
";
    echo "3. Не тот порт (попробуйте 5432)

";
    
    echo "📋 Проверьте в Supabase Dashboard:
";
    echo "   Project Settings → Database → Network Restrictions
";
    echo "   Там должен быть: 95.24.37.162/32
";
}