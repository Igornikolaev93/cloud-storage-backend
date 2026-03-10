<?php
/**
 * Тестирование порта 5432
 */

$host = 'aws-0-eu-central-1.pooler.supabase.com';
$port = 5432; // Меняем порт
$password = 'pppx37CRZxhSUqCt';
$project_ref = 'vvqrogorxkspdudypriy';

echo "🔌 ТЕСТИРОВАНИЕ ПОРТА 5432\n";
echo "==========================\n\n";

// Проверка порта
$fp = @fsockopen($host, $port, $errno, $errstr, 3);
if ($fp) {
    echo "✅ Порт {$port} открыт\n\n";
    fclose($fp);
} else {
    echo "❌ Порт {$port} закрыт: {$errstr}\n\n";
}

// Тестируем основные варианты
$usernames = [
    "postgres.{$project_ref}",
    "postgres",
    $project_ref
];

foreach ($usernames as $username) {
    echo "Тестируем username: '{$username}'\n";
    
    try {
        $dsn = "pgsql:host={$host};port={$port};dbname=postgres;sslmode=require";
        
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]);
        
        echo "  ✅ УСПЕХ!\n";
        $found = true;
        break;
        
    } catch (PDOException $e) {
        echo "  ❌ " . $e->getMessage() . "\n";
    }
}