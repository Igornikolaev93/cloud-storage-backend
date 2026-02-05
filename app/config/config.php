<?php

/**
 * Database configuration.
 *
 * This file defines the configuration for the database connection.
 * It is used by the Database model to establish a connection to the database.
 */

// Set the default charset
ini_set('default_charset', 'UTF-8');

// --- DATABASE CONFIGURATION ARRAY ---
define('DB_CONFIG', [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'dbname'    => 'dribbbox',
    // --- CORRECTED FOR XAMPP DEFAULTS ---
    // Use the default root user for XAMPP, which has no password.
    // This will resolve the connection refused error if the 'dribbbox' user does not exist.
    'username'  => 'root',
    'password'  => '',
    'port'      => '3306',
    'charset'   => 'utf8mb4',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
]);
