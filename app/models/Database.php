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
            $driver = 'pgsql';
            $host = '35.227.164.209'; // <-- Updated IP Address
            $port = '5432';
            $dbname = 'cloude_db';
            $username = 'cloude_user';
            $password = 'miiW801cahpwa8KTjGk7LASxtKYnGilT';

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s',
                $driver, $host, $port, $dbname
            );

            try {
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
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
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO \"{$table}\" ({$columns}) VALUES ({$placeholders}) RETURNING id";

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
