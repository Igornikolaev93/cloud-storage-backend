<?php
/**
 * Полный тест подключения к Supabase и работы с базой данных
 * Запуск: php test_complete.php
 */

require_once 'app/config/config.php';
require_once 'app/models/Database.php';

use App\Models\Database;

// Цвета для вывода в консоль
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

echo COLOR_BLUE . "╔════════════════════════════════════════════════════════════╗\n";
echo "║        ПОЛНОЕ ТЕСТИРОВАНИЕ ПОДКЛЮЧЕНИЯ К SUPABASE         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n" . COLOR_RESET;
echo "Время теста: " . date('Y-m-d H:i:s') . "\n\n";

// Функция для вывода результатов тестов
function testResult($name, $success, $message = '') {
    $status = $success ? COLOR_GREEN . "✓ УСПЕШНО" . COLOR_RESET : COLOR_RED . "✗ ОШИБКА" . COLOR_RESET;
    echo str_pad($name, 40, '.') . " [ {$status} ]\n";
    if ($message) {
        echo "  → " . $message . "\n";
    }
    return $success;
}

// Функция для замера времени выполнения
function measureTime($callback) {
    $start = microtime(true);
    $result = $callback();
    $time = round((microtime(true) - $start) * 1000, 2);
    return [$result, $time];
}

$testsPassed = 0;
$testsTotal = 0;

echo COLOR_YELLOW . "\n📋 КОНФИГУРАЦИЯ:\n" . COLOR_RESET;
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Хост:      " . DB_CONFIG['host'] . "\n";
echo "Порт:      " . DB_CONFIG['port'] . "\n";
echo "База:      " . DB_CONFIG['dbname'] . "\n";
echo "Пользователь: " . DB_CONFIG['username'] . "\n";
echo "Пароль:    " . str_repeat('•', strlen(DB_CONFIG['password'])) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo COLOR_YELLOW . "🔬 ТЕСТ 1: ПРОВЕРКА РАСШИРЕНИЙ PHP\n" . COLOR_RESET;
echo "────────────────────────────────────────────────────────────\n";

// Проверка расширения PDO
$testsTotal++;
list($pdoEnabled, $time) = measureTime(function() {
    return extension_loaded('pdo');
});
testResult('PDO расширение', $pdoEnabled, 
    $pdoEnabled ? 'PDO доступен' : 'PDO не установлен');
if ($pdoEnabled) $testsPassed++;

// Проверка расширения pdo_pgsql
$testsTotal++;
list($pgsqlEnabled, $time) = measureTime(function() {
    return extension_loaded('pdo_pgsql');
});
testResult('PDO PostgreSQL', $pgsqlEnabled,
    $pgsqlEnabled ? 'PostgreSQL драйвер доступен' : 'Драйвер pdo_pgsql не установлен');
if ($pgsqlEnabled) $testsPassed++;

echo "\n" . COLOR_YELLOW . "🌐 ТЕСТ 2: DNS И СЕТЕВАЯ ДОСТУПНОСТЬ\n" . COLOR_RESET;
echo "────────────────────────────────────────────────────────────\n";

// Проверка DNS
$testsTotal++;
list($dnsResolved, $time) = measureTime(function() {
    $host = DB_CONFIG['host'];
    $ip = gethostbyname($host);
    return $ip !== $host ? $ip : false;
});
testResult('DNS резолвинг', $dnsResolved !== false,
    $dnsResolved ? "IP адрес: {$dnsResolved}" : "Не удается найти хост");
if ($dnsResolved !== false) $testsPassed++;

// Проверка доступности порта
if ($dnsResolved !== false) {
    $testsTotal++;
    list($portOpen, $time) = measureTime(function() {
        $host = DB_CONFIG['host'];
        $port = DB_CONFIG['port'];
        $connection = @fsockopen($host, $port, $errno, $errstr, 3);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    });
    testResult('Порт ' . DB_CONFIG['port'] . ' доступен', $portOpen,
        $portOpen ? 'Порт открыт' : 'Порт недоступен');
    if ($portOpen) $testsPassed++;
}

echo "\n" . COLOR_YELLOW . "🔌 ТЕСТ 3: ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ\n" . COLOR_RESET;
echo "────────────────────────────────────────────────────────────\n";

// Прямое PDO подключение
$testsTotal++;
list($connectionResult, $time) = measureTime(function() {
    try {
        $pdo = Database::getConnection();
        return ['success' => true, 'pdo' => $pdo];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
});

if ($connectionResult['success']) {
    testResult('PDO подключение', true, "Время: {$time}мс");
    $testsPassed++;
    $pdo = $connectionResult['pdo'];
    
    // Тест простого запроса
    $testsTotal++;
    list($queryResult, $time) = measureTime(function() use ($pdo) {
        try {
            $stmt = $pdo->query('SELECT 1 as test');
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    });
    testResult('Выполнение запроса', $queryResult, "Время: {$time}мс");
    if ($queryResult) $testsPassed++;
    
} else {
    testResult('PDO подключение', false, $connectionResult['error']);
}

echo "\n" . COLOR_YELLOW . "ℹ️  ТЕСТ 4: ИНФОРМАЦИЯ О СЕРВЕРЕ\n" . COLOR_RESET;
echo "────────────────────────────────────────────────────────────\n";

if (isset($pdo) && $pdo) {
    try {
        // Версия PostgreSQL
        $stmt = $pdo->query('SELECT version() as ver');
        $version = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "PostgreSQL: " . substr($version['ver'], 0, 70) . "...\n";
        
        // Текущая база данных
        $stmt = $pdo->query('SELECT current_database() as db');
        $db = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Текущая БД: " . $db['db'] . "\n";
        
        // Текущий пользователь
        $stmt = $pdo->query('SELECT current_user as user');
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Пользователь: " . $user['user'] . "\n";
        
        // Серверные настройки
        $stmt = $pdo->query("SHOW server_version");
        $serverVer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $testsTotal++;
        echo "Статус: " . COLOR_GREEN . "Активно" . COLOR_RESET . "\n";
        $testsPassed++;
        
    } catch (Exception $e) {
        echo COLOR_RED . "Ошибка получения информации: " . $e->getMessage() . COLOR_RESET . "\n";
    }
} else {
    echo COLOR_RED . "Нет подключения к базе данных" . COLOR_RESET . "\n";
}

echo "\n" . COLOR_YELLOW . "📊 ТЕСТ 5: БАЗА ДАННЫХ 'STORAGE'\n" . COLOR_RESET;
echo "────────────────────────────────────────────────────────────\n";

if (isset($pdo) && $pdo) {
    // Проверка существования базы storage
    try {
        $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datname = 'storage'");
        $exists = $stmt->fetch();
        
        $testsTotal++;
        if ($exists) {
            testResult('База данных storage', true, 'Существует');
            $testsPassed++;
            
            // Подключение к storage
            try {
                $dsnStorage = sprintf(
                    'pgsql:host=%s;port=%s;dbname=storage;sslmode=require',
                    DB_CONFIG['host'],
                    DB_CONFIG['port']
                );
                
                $pdoStorage = new PDO($dsnStorage, DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['options']);
                
                $testsTotal++;
                testResult('Подключение к storage', true, 'Успешно');
                $testsPassed++;
                
                // Проверка таблиц в storage
                $tables = $pdoStorage->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($tables)) {
                    echo "\n📋 Таблицы в базе storage:\n";
                    foreach ($tables as $table) {
                        $count = $pdoStorage->query("SELECT COUNT(*) FROM \"{$table}\"")->fetchColumn();
                        echo "  • {$table}: " . $count . " записей\n";
                    }
                    
                    $testsTotal++;
                    testResult('Проверка таблиц', true, 'Найдено ' . count($tables) . ' таблиц');
                    $testsPassed++;
                    
                } else {
                    echo "⚠️  В базе storage нет таблиц\n";
                    echo "   Запустите php database_setup.php для создания таблиц\n";
                }
                
            } catch (Exception $e) {
                testResult('Подключение к storage', false, $e->getMessage());
            }
            
        } else {
            testResult('База данных storage', false, 'Не существует');
            echo "   Создайте базу данных: CREATE DATABASE storage;\n";
        }
        
    } catch (Exception $e) {
        echo COLOR_RED . "Ошибка проверки: " . $e->getMessage() . COLOR_RESET . "\n";
    }
}

echo "\n" . COLOR_YELLOW . "📈 ИТОГОВЫЙ РЕЗУЛЬТАТ\n" . COLOR_RESET;
echo "────────────────────────────────────────────────────────────\n";
$successRate = round(($testsPassed / $testsTotal) * 100, 1);
$color = $successRate >= 80 ? COLOR_GREEN : ($successRate >= 50 ? COLOR_YELLOW : COLOR_RED);

echo "Пройдено тестов: {$testsPassed} из {$testsTotal}\n";
echo "Успешность: {$color}{$successRate}%" . COLOR_RESET . "\n";

if ($testsPassed === $testsTotal) {
    echo "\n" . COLOR_GREEN . "✅ ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!" . COLOR_RESET . "\n";
    echo "База данных готова к работе.\n";
} else {
    echo "\n" . COLOR_YELLOW . "⚠️  НЕКОТОРЫЕ ТЕСТЫ НЕ ПРОЙДЕНЫ" . COLOR_RESET . "\n";
    echo "Проверьте ошибки выше и исправьте проблемы.\n";
}

echo "\n" . COLOR_BLUE . "════════════════════════════════════════════════════════════\n" . COLOR_RESET;