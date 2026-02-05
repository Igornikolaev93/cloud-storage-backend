<?php

require_once __DIR__ . '/app/models/Database.php';

use App\Models\Database;

echo "Starting database setup...\n";

try {
    $pdo = Database::getConnection();

    // Turn off foreign key checks to avoid issues with table drop order
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

    // Drop tables if they exist
    $pdo->exec('DROP TABLE IF EXISTS file_shares, files, directories, users');
    echo "Existing tables dropped.\n";

    // Create users table
    $pdo->exec("CREATE TABLE `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'users' created successfully.\n";

    // Create directories table
    $pdo->exec("CREATE TABLE `directories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `parent_id` INT NULL,
        `name` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`parent_id`) REFERENCES `directories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'directories' created successfully.\n";

    // Create files table
    $pdo->exec("CREATE TABLE `files` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `directory_id` INT NULL,
        `name` VARCHAR(255) NOT NULL,
        `stored_name` VARCHAR(255) NOT NULL,
        `mime_type` VARCHAR(255) NOT NULL,
        `size` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`directory_id`) REFERENCES `directories`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'files' created successfully.\n";
    
    // Create file_shares table
    $pdo->exec("CREATE TABLE `file_shares` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `file_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `shared_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_share` (`file_id`, `user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'file_shares' created successfully.\n";

    // Turn foreign key checks back on
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

    echo "Database setup completed successfully!\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}
