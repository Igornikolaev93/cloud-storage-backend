<?php
require __DIR__ . '/app/config/config.php';
require __DIR__ . '/app/models/Database.php';
require __DIR__ . '/app/models/User.php';

use App\Models\Database;
use App\Models\User;

$adminEmail = 'admin@example.com';
$adminRole = 'admin';

try {
    // Устанавливаем соединение с базой данных
    Database::getConnection();
    echo "Database connection successful.\n";

    // Ищем пользователя
    $user = User::findByEmail($adminEmail);

    if ($user) {
        echo "User found: " . $user['email'] . "\n";
        echo "Current role: " . $user['role'] . "\n";

        if ($user['role'] !== $adminRole) {
            echo "Updating role to 'admin'...\n";
            if (User::changeRole((int)$user['id'], $adminRole)) {
                echo "Role updated successfully!\n";
            } else {
                echo "Failed to update role.\n";
            }
        } else {
            echo "User already has the 'admin' role.\n";
        }
    } else {
        echo "Administrator account not found. Please run create_admin.php first.\n";
    }

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
