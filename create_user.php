<?php
require __DIR__ . '/app/config/config.php';
require __DIR__ . '/app/models/Database.php';
require __DIR__ . '/app/models/User.php';

use App\Models\Database;
use App\Models\User;

$userData = [
    'email' => 'goscha.nikolae2015@yandex.ru',
    'username' => 'goscha',
    'password' => 'password123',
];

try {
    Database::getConnection();

    if (User::findByEmail($userData['email'])) {
        echo "User with email '" . $userData['email'] . "' already exists.\n";
    } else {
        $userId = User::create($userData);
        if ($userId) {
            echo "User account created successfully!\n";
            echo "Email: " . $userData['email'] . "\n";
            echo "Password: " . $userData['password'] . "\n";
        } else {
            echo "Failed to create user account.\n";
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
