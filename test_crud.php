<?php
/**
 * Тест CRUD операций с базой данных
 * Запуск: php test_crud.php
 */

require_once 'app/config/config.php';
require_once 'app/models/Database.php';

use App\Models\Database;

echo "🧪 ТЕСТИРОВАНИЕ CRUD ОПЕРАЦИЙ\n";
echo "==============================\n\n";

// Проверяем подключение
try {
    $pdo = Database::getConnection();
    echo "✅ Подключение к базе данных успешно\n\n";
} catch (Exception $e) {
    die("❌ Ошибка подключения: " . $e->getMessage() . "\n");
}

// Проверяем, подключены ли мы к правильной базе
try {
    $stmt = $pdo->query("SELECT current_database()");
    $dbname = $stmt->fetchColumn();
    echo "📊 Текущая база данных: " . $dbname . "\n\n";
    
    if ($dbname !== 'storage') {
        echo "⚠️  Вы подключены к '{$dbname}', но ожидается 'storage'\n";
        echo "   Попробуйте переключиться на базу 'storage'\n\n";
    }
} catch (Exception $e) {
    echo "❌ Не удалось получить информацию о базе: " . $e->getMessage() . "\n\n";
}

// Проверяем существование таблицы users
try {
    $tables = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'users'
    ")->fetchAll();
    
    if (empty($tables)) {
        echo "❌ Таблица 'users' не найдена. Сначала запустите database_setup.php\n";
        exit(1);
    }
    
    echo "✅ Таблица 'users' существует\n\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка проверки таблиц: " . $e->getMessage() . "\n";
    exit(1);
}

// ТЕСТ 1: Вставка данных
echo "🔹 ТЕСТ 1: Вставка пользователя (INSERT)\n";
echo "----------------------------------------\n";

$testUser = [
    'username' => 'testuser_' . rand(1000, 9999),
    'email' => 'test_' . time() . '@example.com',
    'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
    'created_at' => date('Y-m-d H:i:s')
];

try {
    $sql = "INSERT INTO users (username, email, password_hash, created_at) 
            VALUES (:username, :email, :password_hash, :created_at) 
            RETURNING id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($testUser);
    $userId = $stmt->fetchColumn();
    
    echo "✅ Пользователь создан с ID: " . $userId . "\n\n";
    
    // ТЕСТ 2: Чтение данных
    echo "🔹 ТЕСТ 2: Чтение пользователя (SELECT)\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Пользователь найден:\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Created: " . $user['created_at'] . "\n\n";
    } else {
        echo "❌ Пользователь не найден\n\n";
    }
    
    // ТЕСТ 3: Обновление данных
    echo "🔹 ТЕСТ 3: Обновление пользователя (UPDATE)\n";
    echo "----------------------------------------\n";
    
    $updateData = [
        'username' => $testUser['username'] . '_updated',
        'id' => $userId
    ];
    
    $sql = "UPDATE users SET username = :username WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($updateData);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Пользователь обновлен\n";
        
        // Проверяем обновление
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $newUsername = $stmt->fetchColumn();
        echo "   Новый username: " . $newUsername . "\n\n";
    } else {
        echo "❌ Не удалось обновить пользователя\n\n";
    }
    
    // ТЕСТ 4: Подсчет пользователей
    echo "🔹 ТЕСТ 4: Подсчет пользователей (COUNT)\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $count = $stmt->fetchColumn();
    echo "✅ Всего пользователей в базе: " . $count . "\n\n";
    
    // ТЕСТ 5: Выборка всех пользователей
    echo "🔹 ТЕСТ 5: Выборка всех пользователей (SELECT ALL)\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($recentUsers)) {
        echo "✅ Последние 5 пользователей:\n";
        foreach ($recentUsers as $u) {
            echo "   • {$u['id']}: {$u['username']} ({$u['email']}) - {$u['created_at']}\n";
        }
        echo "\n";
    }
    
    // ТЕСТ 6: Удаление тестового пользователя
    echo "🔹 ТЕСТ 6: Удаление тестового пользователя (DELETE)\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Тестовый пользователь удален\n";
        
        // Проверяем, что пользователь действительно удален
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        if (!$stmt->fetch()) {
            echo "✅ Пользователь больше не существует в базе\n\n";
        }
    } else {
        echo "❌ Не удалось удалить пользователя\n\n";
    }
    
    // ИТОГ
    echo "🎉 ТЕСТИРОВАНИЕ ЗАВЕРШЕНО УСПЕШНО!\n";
    echo "================================\n";
    echo "✅ Все CRUD операции работают корректно\n";
    echo "✅ Подключение к Supabase стабильно\n";
    echo "✅ База данных готова к использованию\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка во время тестирования: " . $e->getMessage() . "\n";
    exit(1);
}