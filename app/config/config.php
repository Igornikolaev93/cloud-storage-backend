<?php

/**
 * Database configuration for Supabase
 */

ini_set('default_charset', 'UTF-8');

define('DB_CONFIG', [
    'driver'    => 'pgsql',
    'host'      => 'aws-1-eu-west-1.pooler.supabase.com',
    'port'      => '6543',
    'dbname'    => 'storage',
    'username'  => 'postgres.vvqrogorxkspdudypriy',
    'password'  => 'pppx37CRZxhSUqCt',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
]);