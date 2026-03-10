<?php
/**
 * Тест регистрации пользователя
 */

require_once 'app/config/config.php';
require_once 'app/models/Database.php';
require_once 'app/models/User.php';

use App\Models\User;

echo "🔍 ТЕСТ РЕГИСТРАЦИИ ПОЛЬЗОВАТЕЛЯ\n";
echo "================================\n\n";

// Тестовые данные
$testUser = [
    'username' => 'testuser_' . rand(100, 999),
    'email' => 'test_' . time() . '@example.com',
    'password' => 'Test123!'
];

echo "Попытка регистрации:\n";
echo "Username: {$testUser['username']}\n";
echo "Email: {$testUser['email']}\n";
echo "Password: {$testUser['password']}\n\n";

try {
    // Пробуем создать пользователя
    $userId = User::create($testUser);
    
    if ($userId) {
        echo "✅ ПОЛЬЗОВАТЕЛЬ УСПЕШНО СОЗДАН!\n";
        echo "   ID пользователя: {$userId}\n\n";
        
        // Проверяем, что пользователь создан
        $user = User::findById($userId);
        if ($user) {
            echo "📋 Данные из базы:\n";
            echo "   ID: {$user['id']}\n";
            echo "   Username: {$user['username']}\n";
            echo "   Email: {$user['email']}\n";
            echo "   Role: {$user['role']}\n";
            echo "   Created: {$user['created_at']}\n\n";
        }
        
        // Проверяем, что пароль работает
        $userByEmail = User::findByEmail($testUser['email']);
        if ($userByEmail && password_verify($testUser['password'], $userByEmail['password_hash'])) {
            echo "✅ ПАРОЛЬ РАБОТАЕТ!\n";
        } else {
            echo "❌ ПАРОЛЬ НЕ РАБОТАЕТ!\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "\nПользователь с таким именем или email уже существует.\n";
    }
}

echo "\n================================\n";

// Покажем всех пользователей
try {
    $users = User::getAll();
    echo "\n📋 Всего пользователей в базе: " . count($users) . "\n";
    foreach (array_slice($users, 0, 5) as $user) {
        echo "   - {$user['username']} ({$user['email']})\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка при получении списка: " . $e->getMessage() . "\n";
}
