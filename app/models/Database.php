<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $connection = null;

    /**
     * Get the database connection.
     * Uses the DB_CONFIG constant defined in config.php.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            if (!defined('DB_CONFIG')) {
                throw new Exception("Database configuration (DB_CONFIG) is not defined.");
            }
            
            $config = DB_CONFIG;
            
            try {
                if ($config['driver'] === 'pgsql') {
                    $dsn = sprintf(
                        'pgsql:host=%s;port=%s;dbname=%s',
                        $config['host'],
                        $config['port'],
                        $config['database']
                    );
                } else { // default to mysql
                    $dsn = sprintf(
                        'mysql:host=%s;dbname=%s;port=%s;charset=%s',
                        $config['host'],
                        $config['database'],
                        $config['port'],
                        $config['charset']
                    );
                }
                
                self::$connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection error");
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Start a new database transaction.
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit the current database transaction.
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }
    
    /**
     * Roll back the current database transaction.
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }
    
    /**
     * Execute a SQL query with parameters.
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $stmt->bindValue($key + 1, $value);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Fetch a single record from the database.
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Fetch all records from the database.
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch a single column from the next row of a result set.
     */
    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert a new record into a table and return the last insert ID.
     */
    public static function insert(string $table, array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        self::query($sql, $data);
        
        $lastId = self::getConnection()->lastInsertId();
        return $lastId ? (int)$lastId : null;
    }
    
    /**
     * Update records in a table.
     */
    public static function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "`$column` = :$column";
            $params[$column] = $value;
        }
        
        $whereParts = [];
        foreach ($where as $column => $value) {
            $whereParts[] = "`$column` = :w_$column";
            $params["w_$column"] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            implode(' AND ', $whereParts)
        );
        
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete records from a table.
     */
    public static function delete(string $table, array $where): int
    {
        $whereParts = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $whereParts[] = "`$column` = :$column";
            $params[$column] = $value;
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereParts)
        );
        
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Check if a record exists in a table.
     */
    public static function exists(string $table, array $conditions): bool
    {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereParts[] = "`$column` = :$column";
            $params[$column] = $value;
        }
        
        $sql = sprintf(
            'SELECT COUNT(*) FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereParts)
        );
        
        return self::fetchColumn($sql, $params) > 0;
    }
}