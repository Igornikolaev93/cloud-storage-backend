<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;

require_once __DIR__ . '/../config/config.php';

class Database
{
    private static ?PDO $connection = null;
    private static array $config = [];
    private static int $lastActivity = 0;
    private static int $maxLifetime = 300; // 5 минут максимальное время жизни соединения

    /**
     * Получить соединение с базой данных (с автоматическим переподключением)
     */
    public static function getConnection(): PDO
    {
        self::loadConfig();
        
        // Проверяем, нужно ли переподключиться
        if (self::shouldReconnect()) {
            self::disconnect();
        }
        
        // Если нет соединения - создаем новое
        if (self::$connection === null) {
            self::connect();
        }
        
        // Проверяем, живо ли соединение
        try {
            self::$connection->query('SELECT 1')->fetch();
            self::$lastActivity = time();
        } catch (PDOException $e) {
            error_log("Connection lost, reconnecting... " . $e->getMessage());
            self::connect();
        }
        
        return self::$connection;
    }

    /**
     * Загрузить конфигурацию
     */
    private static function loadConfig(): void
    {
        if (empty(self::$config)) {
            self::$config = DB_CONFIG;
        }
    }

    /**
     * Проверить, нужно ли переподключиться
     */
    private static function shouldReconnect(): bool
    {
        if (self::$connection === null) {
            return false;
        }
        
        // Переподключаемся, если прошло слишком много времени
        if (time() - self::$lastActivity > self::$maxLifetime) {
            error_log("Connection lifetime exceeded, reconnecting...");
            return true;
        }
        
        return false;
    }

    /**
     * Разорвать текущее соединение
     */
    private static function disconnect(): void
    {
        self::$connection = null;
    }

    /**
     * Установить новое соединение
     */
    private static function connect(): void
    {
        $driver = self::$config['driver'];
        $host = self::$config['host'];
        $port = self::$config['port'];
        $dbname = self::$config['dbname'];
        $username = self::$config['username'];
        $password = self::$config['password'];
        
        // Формируем DSN с дополнительными параметрами для стабильности
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;sslmode=require;connect_timeout=10;keepalives=1;keepalives_idle=60;keepalives_interval=10;keepalives_count=5',
            $driver, $host, $port, $dbname
        );

        // Добавляем опции для стабильного соединения
        $options = self::$config['options'];
        $options[PDO::ATTR_TIMEOUT] = 30;
        $options[PDO::ATTR_PERSISTENT] = false; // Не использовать постоянные соединения
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        
        // Добавляем опции для PostgreSQL
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        
        try {
            self::$connection = new PDO($dsn, $username, $password, $options);
            self::$lastActivity = time();
            
            // Устанавливаем параметры сессии
            self::$connection->exec("SET statement_timeout = '30s'");
            self::$connection->exec("SET idle_in_transaction_session_timeout = '5min'");
            
            error_log("Database connection established successfully");
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            error_log("DSN: " . $dsn);
            throw new Exception('Could not connect to database. Please check your configuration. Error: ' . $e->getMessage());
        }
    }

    /**
     * Выполнить запрос с автоматическим переподключением
     */
    private static function executeWithReconnect(callable $operation, string $errorMessage)
    {
        $maxRetries = 2;
        $retryCount = 0;
        
        while ($retryCount <= $maxRetries) {
            try {
                $pdo = self::getConnection();
                return $operation($pdo);
                
            } catch (PDOException $e) {
                error_log("Database operation failed: " . $e->getMessage());
                
                // Если ошибка соединения, пробуем переподключиться
                if (strpos($e->getMessage(), 'no connection') !== false || 
                    strpos($e->getMessage(), 'server closed') !== false ||
                    strpos($e->getMessage(), 'connection lost') !== false) {
                    
                    error_log("Connection error detected, reconnecting... (attempt " . ($retryCount + 1) . ")");
                    self::disconnect();
                    $retryCount++;
                    
                    if ($retryCount <= $maxRetries) {
                        sleep(1); // Пауза перед повторной попыткой
                        continue;
                    }
                }
                
                // Если это не ошибка соединения или кончились попытки
                throw new Exception($errorMessage . ': ' . $e->getMessage());
            }
        }
        
        throw new Exception($errorMessage . ': Max retries exceeded');
    }

    /**
     * Выполнить один запрос и вернуть одну запись
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        return self::executeWithReconnect(
            function($pdo) use ($sql, $params) {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ?: null;
            },
            'Database query failed'
        );
    }

    /**
     * Выполнить запрос и вернуть все записи
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::executeWithReconnect(
            function($pdo) use ($sql, $params) {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            },
            'Database query failed'
        );
    }

    /**
     * Вставить запись и вернуть ID
     */
    public static function insert(string $table, array $data): ?int
    {
        return self::executeWithReconnect(
            function($pdo) use ($table, $data) {
                $columns = implode(", ", array_map(fn($col) => '"' . $col . '"', array_keys($data)));
                $placeholders = ":" . implode(", :", array_keys($data));
                $sql = "INSERT INTO \"{$table}\" ({$columns}) VALUES ({$placeholders}) RETURNING id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? (int)$result['id'] : null;
            },
            'Database insert failed'
        );
    }

    /**
     * Обновить записи
     */
    public static function update(string $table, array $data, array $conditions): int
    {
        return self::executeWithReconnect(
            function($pdo) use ($table, $data, $conditions) {
                $setParams = $data;
                $whereParams = [];
                
                $setFields = [];
                foreach ($data as $key => $value) {
                    $setFields[] = "\"{$key}\" = :{$key}";
                }
                $setString = implode(", ", $setFields);

                $whereFields = [];
                foreach ($conditions as $key => $value) {
                    $whereFields[] = "\"cond_{$key}\" = :cond_{$key}";
                    $whereParams["cond_{$key}"] = $value;
                }
                $whereString = implode(" AND ", $whereFields);

                $sql = "UPDATE \"{$table}\" SET {$setString} WHERE {$whereString}";
                $allParams = array_merge($setParams, $whereParams);

                $stmt = $pdo->prepare($sql);
                $stmt->execute($allParams);
                return $stmt->rowCount();
            },
            'Database update failed'
        );
    }

    /**
     * Удалить записи
     */
    public static function delete(string $table, array $conditions): int
    {
        return self::executeWithReconnect(
            function($pdo) use ($table, $conditions) {
                $whereFields = [];
                foreach ($conditions as $key => $value) {
                    $whereFields[] = "\"{$key}\" = :{$key}";
                }
                $whereString = implode(" AND ", $whereFields);

                $sql = "DELETE FROM \"{$table}\" WHERE {$whereString}";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($conditions);
                return $stmt->rowCount();
            },
            'Database delete failed'
        );
    }

    /**
     * Проверить состояние соединения
     */
    public static function ping(): bool
    {
        try {
            $pdo = self::getConnection();
            $pdo->query('SELECT 1')->fetch();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Явно закрыть соединение
     */
    public static function close(): void
    {
        self::$connection = null;
        error_log("Database connection closed");
    }
}