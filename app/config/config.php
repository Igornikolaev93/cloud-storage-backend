<?php

/**
 * Database configuration for Supabase
 */

ini_set('default_charset', 'UTF-8');

define('DB_CONFIG', [
    'driver'    => 'pgsql',
    // Используем shared pooler (работает с IPv4)
    'host'      => 'aws-0-eu-central-1.pooler.supabase.com',
    'port'      => '6543',  // Transaction pooler порт
    'dbname'    => 'postgres',
    // Username с project-ref
    'username'  => 'postgres.vvqrogorxkspdudypriy',
    // НОВЫЙ ПАРОЛЬ
    'password'  => 'pppx37CRZxhSUqCt',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 30,
    ],
]);