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
// The Database model expects a constant named DB_CONFIG to be defined.
define('DB_CONFIG', [
    'driver'    => 'mysql',
    'host'      => 'database',
    'dbname'    => 'dribbbox',
    'username'  => 'dribbbox',
    'password'  => 'dribbbox',
    'port'      => '3306',
    'charset'   => 'utf8mb4',
    'options'   => [
        // Set the error mode to throw exceptions
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Set the default fetch mode to associative array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Disable emulation of prepared statements
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
]);
