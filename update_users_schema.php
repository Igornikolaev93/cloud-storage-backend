<?php

require_once 'app/config/config.php';

try {
    $pdo = new PDO(
        DB_CONFIG['driver'] . ':host=' . DB_CONFIG['host'] . ';port=' . DB_CONFIG['port'] . ';dbname=' . DB_CONFIG['dbname'],
        DB_CONFIG['username'],
        DB_CONFIG['password'],
        DB_CONFIG['options']
    );

    // Check if the 'role' column exists
    $stmt = $pdo->query("SELECT 1 FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'role'");
    
    if ($stmt->fetch()) {
        echo "Column 'role' already exists in 'users' table.\n";
    } else {
        echo "Column 'role' does not exist. Adding it...\n";
        $sql = \"ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user'\";
        $pdo->exec($sql);
        echo "Column 'role' added successfully to 'users' table.\n";
    }

} catch (PDOException $e) {
    die("Error updating table: " . $e->getMessage());
}
