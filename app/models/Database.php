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
            // Use the configuration from config.php
            $driver = DB_CONFIG['driver'];
            $host = DB_CONFIG['host'];
            $port = DB_CONFIG['port'];
            $dbname = DB_CONFIG['dbname'];
            $username = DB_CONFIG['username'];
            $password = DB_CONFIG['password'];

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s',
                $driver, $host, $port, $dbname
            );

            try {
                self::$connection = new PDO($dsn, $username, $password, DB_CONFIG['options']);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception('Could not connect to the database. Please check your configuration.');
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

    public static function insert(string $table, array $data): ?int
    {
        $columns = implode(", ", array_map(fn($col) => '"'.$col.'"', array_keys($data)));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO \"{$table}\" ({$columns}) VALUES ({$placeholders}) RETURNING id";

        error_log("SQL Query: " . $sql);
        error_log("Data: " . print_r($data, true));

        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log("Database Insert Error: " . $e->getMessage());
            throw new Exception('Database insert failed: ' . $e->getMessage());
        }
    }

    public static function update(string $table, array $data, array $conditions): int
    {
        $setParams = $data;
        $whereParams = [];
        $dataFields = [];
        foreach ($data as $key => $value) {
            $dataFields[] = "\"{$key}\" = :{$key}";
        }
        $dataString = implode(", ", $dataFields);

        $conditionFields = [];
        foreach ($conditions as $key => $value) {
            $conditionFields[] = "\"{$key}\" = :cond_{$key}";
            $whereParams["cond_{$key}"] = $value;
        }
        $conditionString = implode(" AND ", $conditionFields);

        $sql = "UPDATE \"{$table}\" SET {$dataString} WHERE {$conditionString}";
        $allParams = array_merge($setParams, $whereParams);

        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($allParams);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Update Error: " . $e->getMessage());
            throw new Exception('Database update failed: ' . $e->getMessage());
        }
    }

    public static function delete(string $table, array $conditions): int
    {
        $conditionFields = [];
        foreach ($conditions as $key => $value) {
            $conditionFields[] = "\"{$key}\" = :{$key}";
        }
        $conditionString = implode(" AND ", $conditionFields);

        $sql = "DELETE FROM \"{$table}\" WHERE {$conditionString}";

        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($conditions);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Delete Error: " . $e->getMessage());
            throw new Exception('Database delete failed: ' . $e->getMessage());
        }
    }
}