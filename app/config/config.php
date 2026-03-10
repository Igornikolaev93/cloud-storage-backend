<?php

/**
 * Database configuration for Supabase
 * Используем SHARED POOLER для IPv4 подключения
 */

ini_set('default_charset', 'UTF-8');

define('DB_CONFIG', [
    'driver'    => 'pgsql',
    // ВАЖНО: Используем shared pooler (работает с IPv4)
    'host'      => 'aws-0-eu-central-1.pooler.supabase.com',
    'port'      => '6543',  // Для shared pooler используем порт 6543
    'dbname'    => 'postgres',
    // Username должен быть в формате postgres.[PROJECT_REF]
    'username'  => 'postgres.vvqrogorxkspdudypriy',
    'password'  => 'Ybrjkftdbujhm16',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 30,
    ],
]);