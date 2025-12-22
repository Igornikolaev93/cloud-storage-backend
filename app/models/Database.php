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
     * Получить подключение к БД
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
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
     * Начать транзакцию
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Подтвердить транзакцию
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }
    
    /**
     * Откатить транзакцию
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }
    
    /**
     * Выполнить запрос с параметрами
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Получить одну запись
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Получить все записи
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Получить значение одного столбца
     */
    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Вставить запись и вернуть ID
     */
    public static function insert(string $table, array $data): ?int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute(array_values($data));
        
        // For PostgreSQL, you might need to pass the sequence name, e.g., 'table_id_seq'
        $lastId = self::getConnection()->lastInsertId();
        return $lastId ? (int)$lastId : null;
    }
    
    /**
     * Обновить запись
     */
    public static function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $params[] = $value;
        }
        
        $whereParts = [];
        foreach ($where as $column => $value) {
            $whereParts[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            implode(' AND ', $whereParts)
        );
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Удалить запись
     */
    public static function delete(string $table, array $where): int
    {
        $whereParts = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $whereParts[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereParts)
        );
        
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Проверить существование записи
     */
    public static function exists(string $table, array $conditions): bool
    {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $whereParts[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'SELECT COUNT(*) FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereParts)
        );
        
        return self::fetchColumn($sql, $params) > 0;
    }
}