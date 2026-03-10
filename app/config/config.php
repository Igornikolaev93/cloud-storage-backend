<?php

/**
 * Database configuration for Supabase
 */

ini_set('default_charset', 'UTF-8');

define('DB_CONFIG', [
    'driver'    => 'pgsql',
    // ВАЖНО: Используем pooler хост (поддерживает IPv4)
    'host'      => 'aws-0-eu-central-1.pooler.supabase.com',
    'port'      => '5432',
    'dbname'    => 'postgres',
    // ВАЖНО: Для pooler нужно использовать username с project-ref
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