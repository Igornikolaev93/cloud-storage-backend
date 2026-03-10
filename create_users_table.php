<?php

require_once 'app/config/config.php';

try {
    $pdo = new PDO(
        DB_CONFIG['driver'] . ':host=' . DB_CONFIG['host'] . ';port=' . DB_CONFIG['port'] . ';dbname=' . DB_CONFIG['dbname'],
        DB_CONFIG['username'],
        DB_CONFIG['password'],
        DB_CONFIG['options']
    );

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $pdo->exec($sql);

    echo "Table 'users' created successfully (if it didn't exist already).";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
