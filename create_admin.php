<?php
require __DIR__ . '/app/models/Database.php';
require __DIR__ . '/app/models/User.php';

// Загружаем конфигурацию
$config = require __DIR__ . '/app/config/config.php';

// Инициализируем базу данных
App\Models\Database::init($config['db']);

// Данные для нового администратора
$adminData = [
    'email' => 'admin@example.com',
    'password' => 'password123',
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin'
];

try {
    // Проверяем, не существует ли уже такой пользователь
    if (App\Models\User::findByEmail($adminData['email'])) {
        echo "Administrator account already exists.\n";
    } else {
        // Создаем нового пользователя
        $userId = App\Models\User::create($adminData);
        if ($userId) {
            echo "Administrator account created successfully!\n";
            echo "Email: " . $adminData['email'] . "\n";
            echo "Password: " . $adminData['password'] . "\n";
        } else {
            echo "Failed to create administrator account.\n";
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
