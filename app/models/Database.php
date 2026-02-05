<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // --- NEW RENDER.COM POSTGRESQL CONFIGURATION ---
            $driver = 'pgsql';
            $host = 'dpg-d624o9shg0os7387am6g-a.oregon-postgres.render.com';
            $port = '5432'; // Default PostgreSQL port
            $dbname = 'cloude_db';
            $username = 'cloude_user';
            $password = 'miiW801cahpwa8KTjGk7LASxtKYnGilT';

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s',
                $driver,
                $host,
                $port,
                $dbname
            );

            try {
                // Establishes the database connection.
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                // If the connection fails for any reason, throw a generic but clear exception.
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
