<?php
require __DIR__ . '/app/models/Database.php';
require __DIR__ . '/app/models/User.php';

use App\Models\Database;
use App\Models\User;

$adminEmail = 'admin@example.com';
$adminRole = 'admin';

try {
    // Establish database connection
    Database::getConnection();
    echo "Database connection successful.\n";

    // Find the user
    $userModel = new User();
    $user = $userModel->findByEmail($adminEmail);

    if ($user) {
        echo "User found: " . $user['email'] . "\n";
        echo "Current role: " . $user['role'] . "\n";

        if ($user['role'] !== $adminRole) {
            echo "Updating role to 'admin'...\n";
            if ($userModel->update($user['id'], ['role' => $adminRole])) {
                echo "Role updated successfully!\n";
            } else {
                echo "Failed to update role.\n";
            }
        } else {
            echo "User already has the 'admin' role.\n";
        }
    } else {
        echo "Administrator account not found. Please create a user with the email 'admin@example.com'.\n";
    }

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
