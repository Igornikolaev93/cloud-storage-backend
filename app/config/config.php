<?php

/**
 * Database configuration for Supabase.
 */

// Set the default charset
ini_set('default_charset', 'UTF-8');

// --- SUPABASE DATABASE CONFIGURATION ---
define('DB_CONFIG', [
    'driver'    => 'pgsql',
    // ВАЖНО: Используем Session Pooler вместо direct connection
    'host'      => 'aws-0-eu-central-1.pooler.supabase.com',  // Session pooler host
    'port'      => '5432',                                      // Session pooler port
    'dbname'    => 'postgres', // CORRECTED: Should be 'postgres'
    // Имя пользователя из вашей строки подключения, но с правильным форматом для pooler
    'username'  => 'postgres.vvqrogorxkspdudypriy',            // Формат: postgres.PROJECT_REF
    'password'  => 'Ybrjkftdbujhm16',                           // Ваш пароль
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ],
]);