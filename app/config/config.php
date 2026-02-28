<?php

/**
 * Database configuration for Supabase
 */

ini_set('default_charset', 'UTF-8');

define('DB_CONFIG', [
    'driver'    => 'pgsql',
    // Используем direct connection как в SQL Editor (это РАБОТАЕТ)
    'host'      => 'db.vvqrogorxkspdudypriy.supabase.co',
    'port'      => '5432',
    'dbname'    => 'postgres',  // Сначала подключаемся к postgres
    // В direct connection используем просто 'postgres'
    'username'  => 'postgres',
    'password'  => 'Ybrjkftdbujhm16',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 30,
    ],
]);