<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;

// FINAL VERSION - This file is corrected for type errors.
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
                        $config['dbname']
                    );
                } else { // default to mysql
                    $dsn = sprintf(
                        'mysql:host=%s;dbname=%s;port=%s;charset=%s',
                        $config['host'],
                        $config['dbname'],
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
                throw new Exception("Database connection error: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Get the correct quote character for the database driver.
     */
    private static function getQuoteChar(): string
    {
        $driver = self::getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
        return $driver === 'pgsql' ? '"' : '`';
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
        $stmt->execute($params);
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
    public static function insert(string $table, array $data): ?string
    {
        $q = self::getQuoteChar();
        $columns = array_keys($data);
        $quotedColumns = array_map(fn($col) => $q . $col . $q, $columns);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        
        $driver = self::getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $returning = ($driver === 'pgsql') ? ' RETURNING id' : '';

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)%s',
            $table,
            implode(', ', $quotedColumns),
            implode(', ', $placeholders),
            $returning
        );
        
        $stmt = self::query($sql, $data);

        if ($returning) {
            // For PostgreSQL, fetchColumn() can return an int. We must cast it to a string.
            $id = $stmt->fetchColumn();
            return $id !== false ? (string)$id : null;
        } else {
            // For MySQL, lastInsertId() returns a string or false.
            $lastId = self::getConnection()->lastInsertId();
            return $lastId !== false ? $lastId : null;
        }
    }
    
    /**
     * Update records in a table.
     */
    public static function update(string $table, array $data, array $where): int
    {
        $q = self::getQuoteChar();
        $setParts = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "$q$column$q = :s_$column";
            $params["s_$column"] = $value;
        }
        
        $whereParts = [];
        foreach ($where as $column => $value) {
            $whereParts[] = "$q$column$q = :w_$column";
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
        $q = self::getQuoteChar();
        $whereParts = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $paramName = 'w_' . $column;
            $whereParts[] = "$q$column$q = :$paramName";
            $params[$paramName] = $value;
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
        $q = self::getQuoteChar();
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereParts[] = "$q$column$q = :$column";
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