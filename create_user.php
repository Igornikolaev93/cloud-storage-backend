<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    echo "Attempting to create user...\n";
    Database::getConnection();
    echo "Database connection successful.\n";

    if (User::findByEmail($userData['email'])) {
        echo "User with email '" . $userData['email'] . "' already exists.\n";
    } else {
        echo "User does not exist, creating new user...\n";
        $userId = User::create($userData);
        if ($userId) {
            echo "User account created successfully!\n";
            echo "User ID: " . $userId . "\n";
            echo "Email: " . $userData['email'] . "\n";
            echo "Password: " . $userData['password'] . "\n";
        } else {
            echo "Failed to create user account.\n";
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
