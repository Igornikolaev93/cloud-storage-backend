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
    'driver'    => 'pgsql',
    'host'      => 'localhost',
    'dbname'    => 'dribbbox',
    'username'  => 'postgres',
    'password'  => 'password', // Please replace with your actual password
    'port'      => '5432',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
]);
