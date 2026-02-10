<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/models/User.php';

use App\Models\User;

if ($argc < 4) {
    echo "Usage: php create_user.php <username> <email> <password>\n";
    exit(1);
}

$username = $argv[1];
$email = $argv[2];
$password = $argv[3];

$userData = [
    'username' => $username,
    'email' => $email,
    'password' => $password,
];

try {
    echo "Attempting to create user...\n";

    if (User::findByEmail($userData['email'])) {
        echo "User with email '" . $userData['email'] . "' already exists.\n";
    } else {
        echo "User does not exist, creating new user...\n";
        $userId = User::create($userData);
        if ($userId) {
            echo "User account created successfully!\n";
            echo "User ID: " . $userId . "\n";
            echo "Username: " . $userData['username'] . "\n";
            echo "Email: " . $userData['email'] . "\n";
        } else {
            echo "Failed to create user account.\n";
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
