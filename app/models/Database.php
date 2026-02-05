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
            // Check if the configuration constant is defined
            if (!defined('DB_CONFIG')) {
                throw new Exception('Database configuration (DB_CONFIG) is not defined.');
            }

            $config = DB_CONFIG;
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                // --- CUSTOM ERROR FOR USER DIAGNOSIS ---
                // Check for the specific 'Connection Refused' error code.
                if ($e->getCode() === 2002 || str_contains($e->getMessage(), 'Connection refused')) {
                    $errorMessage = sprintf(
                        "<h1>Database Connection Refused</h1>"
                        . "<p>The application could not connect to the MySQL database. This is a common issue with local development environments like XAMPP.</p>"
                        . "<p><b>Please check the following in your XAMPP Control Panel:</b></p>"
                        . "<ol>"
                        . "<li><b>Is the MySQL service running?</b> Look for the 'MySQL' module and ensure it has a green background. If not, click the 'Start' button next to it.</li>"
                        . "<li><b>Which port is MySQL using?</b> Next to the MySQL module, you will see the port number it is using (e.g., '3306').</li>"
                        . "</ol>"
                        . "<p>The current configuration is trying to connect to <b>%s</b> on port <b>%s</b>.</p>"
                        . "<p>If the port in your XAMPP Control Panel is different, you must update the 'port' value in the <code>app/config/config.php</code> file to match.</p>",
                        $config['host'], $config['port']
                    );
                    throw new Exception($errorMessage, (int)$e->getCode());
                }
                
                // Handle other database errors, like 'Unknown database'
                if ($e->getCode() === 1049) {
                     $errorMessage = sprintf(
                        "<h1>Database Not Found</h1>"
                        . "<p>The application connected to MySQL, but could not find the database named '<b>%s</b>'.</p>"
                        . "<p><b>Please create the database in phpMyAdmin:</b></p>"
                        . "<ol>"
                        . "<li>Go to <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a>.</li>"
                        . "<li>Click the 'Databases' tab.</li>"
                        . "<li>Under 'Create database', enter the name '<b>%s</b>' and click 'Create'.</li>"
                        . "</ol>"
                        . "<p>Once created, refresh this page. The application will set up the tables automatically.</p>",
                        $config['dbname'], $config['dbname']
                    );
                    throw new Exception($errorMessage, (int)$e->getCode());
                }

                // For all other errors, throw a generic message.
                throw new Exception('Database connection error: ' . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$connection;
    }
}
