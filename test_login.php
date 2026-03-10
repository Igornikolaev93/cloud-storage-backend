<?php
session_start();

require_once 'app/config/config.php';
require_once 'app/models/Database.php';

use App\Models\Database;

echo "🔐 ТЕСТ ВХОДА В СИСТЕМУ\n";
echo "======================\n\n";

$test_credentials = [
    ['login' => 'testuser', 'password' => 'test123'],
    ['login' => 'test@example.com', 'password' => 'test123']
];

foreach ($test_credentials as $cred) {
    echo "Пробуем войти с логином: {$cred['login']}\n";
    
    try {
        $pdo = Database::getConnection();
        
        // Ищем пользователя
        $sql = "SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cred['login'], $cred['login']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "✅ Пользователь найден: {$user['username']}\n";
            
            if (password_verify($cred['password'], $user['password_hash'])) {
                echo "✅ Пароль верный!\n";
            } else {
                echo "❌ Неверный пароль\n";
                
                // Для отладки покажем хеш
                echo "   Хеш в БД: {$user['password_hash']}\n";
                echo "   Длина хеша: " . strlen($user['password_hash']) . "\n";
            }
        } else {
            echo "❌ Пользователь не найден\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Ошибка: " . $e->getMessage() . "\n";
    }
    
    echo str_repeat("-", 50) . "\n\n";
}

// Покажем всех пользователей в базе
echo "\n📋 Пользователи в базе:\n";
try {
    $users = Database::fetchAll("SELECT id, username, email FROM users");
    foreach ($users as $user) {
        echo "   ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка получения списка: " . $e->getMessage() . "\n";
}
