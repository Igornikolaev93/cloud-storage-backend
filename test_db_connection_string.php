<?php
/**
 * Проверка с данными из Connection String
 */

// ВСТАВЬТЕ СЮДА ВАШУ ПОЛНУЮ СТРОКУ ПОДКЛЮЧЕНИЯ ИЗ DASHBOARDA
$connection_string = "postgresql://postgres.vvqrogorxkspdudypriy:pppx37CRZxhSUqCt@aws-0-eu-central-1.pooler.supabase.com:6543/postgres";

// Разбираем строку подключения
$parts = parse_url($connection_string);

if (!$parts) {
    die("❌ Неправильный формат строки подключения\n");
}

$username = $parts['user'] ?? '';
$password = $parts['pass'] ?? '';
$host = $parts['host'] ?? '';
$port = $parts['port'] ?? '5432';
$path = $parts['path'] ?? '/postgres';
$database = ltrim($path, '/');

echo "📋 Данные из строки подключения:\n";
echo "Host: {$host}\n";
echo "Port: {$port}\n";
echo "Database: {$database}\n";
echo "Username: {$username}\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

// Проверка порта
$fp = @fsockopen($host, $port, $errno, $errstr, 3);
if ($fp) {
    echo "✅ Порт {$port} открыт\n";
    fclose($fp);
} else {
    echo "❌ Порт {$port} закрыт: {$errstr}\n";
}
echo "\n";

// Подключение
try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "✅ ПОДКЛЮЧЕНИЕ УСПЕШНО!\n";
    
    $result = $pdo->query("SELECT current_user, current_database()")->fetch(PDO::FETCH_ASSOC);
    echo "Пользователь: " . $result['current_user'] . "\n";
    echo "База данных: " . $result['current_database'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Tenant or user not found') !== false) {
        echo "\n🔧 Проблема с username:\n";
        echo "   Текущий username: {$username}\n";
        echo "   Попробуйте другие варианты:\n";
        echo "   - postgres\n";
        echo "   - postgres:{$project_ref}\n";
        echo "   - {$project_ref}\n";
    }
} 