<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;

// Include the configuration file
require_once __DIR__ . '/../config/config.php';

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $driver = DB_CONFIG['driver'];
            $host = DB_CONFIG['host'];
            $port = DB_CONFIG['port'];
            $dbname = DB_CONFIG['dbname'];
            $username = DB_CONFIG['username'];
            $password = DB_CONFIG['password'];
            
            // Добавляем sslmode=require для Supabase
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;sslmode=require',
                $driver, $host, $port, $dbname
            );

            try {
                error_log("Connecting to Supabase pooler...");
                error_log("DSN: " . $dsn);
                error_log("Username: " . $username);
                
                self::$connection = new PDO($dsn, $username, $password, DB_CONFIG['options']);
                
                // Проверяем соединение
                self::$connection->query('SELECT 1');
                
                error_log("✅ Successfully connected to Supabase pooler");
                
            } catch (PDOException $e) {
                error_log("❌ Connection error: " . $e->getMessage());
                
                $errorMessage = 'Could not connect to Supabase. ';
                $errorMessage .= 'Make sure you are using the pooler connection. ';
                $errorMessage .= 'Error: ' . $e->getMessage();
                
                throw new Exception($errorMessage);
            }
        }
        return self::$connection;
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Database FetchOne Error: " . $e->getMessage());
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database FetchAll Error: " . $e->getMessage());
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }

    public static function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Execute Error: " . $e->getMessage());
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }
}
