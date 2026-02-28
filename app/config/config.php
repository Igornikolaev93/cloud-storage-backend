<?php

/**
 * Database configuration for Supabase.
 */

// Set the default charset
ini_set('default_charset', 'UTF-8');

// --- SUPABASE DATABASE CONFIGURATION ---
define('DB_CONFIG', [
    'driver'    => 'pgsql',
    // Session pooler host для поддержки IPv4
    'host'      => 'aws-0-eu-central-1.pooler.supabase.com',
    'port'      => '5432',              // Порт для session pooler
    'dbname'    => 'storage',            // ИЗМЕНЕНО: теперь 'storage'
    // Формат пользователя: postgres.[PROJECT-REF]
    'username'  => 'postgres.vvqrogorxkspdudypriy',
    'password'  => 'лёха27733868',       // Ваш пароль
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ],
]);