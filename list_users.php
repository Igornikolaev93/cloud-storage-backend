<?php
require_once 'app/config/config.php';
require_once 'app/models/Database.php';
require_once 'app/models/User.php';

use App\Models\User;

echo "\n📋 Current users in the database:\n";
try {
    $users = User::getAll();
    if (empty($users)) {
        echo "No users found.\n";
    } else {
        echo "Total users: " . count($users) . "\n";
        foreach ($users as $user) {
            echo "   - ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error fetching user list: " . $e->getMessage() . "\n";
}
