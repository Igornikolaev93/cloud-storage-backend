<?php
require_once 'app/config/config.php';
require_once 'app/models/Database.php';

echo "🔍 Testing Supabase connection to database 'storage'...\n\n";

echo "Connection parameters:\n";
echo "Host: " . DB_CONFIG['host'] . "\n";
echo "Port: " . DB_CONFIG['port'] . "\n";
echo "Database: " . DB_CONFIG['dbname'] . "\n"; // Должно быть 'storage'
echo "Username: " . DB_CONFIG['username'] . "\n";
echo "Password: " . str_repeat('*', strlen(DB_CONFIG['password'])) . "\n";
echo "SSL Mode: require\n\n";

// Тест 1: Проверка DNS
echo "Test 1: DNS resolution...\n";
$host = DB_CONFIG['host'];
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "❌ Failed to resolve hostname\n";
} else {
    echo "✅ Host resolved to IP: " . $ip . "\n";
}
echo "\n";

// Тест 2: Проверка соединения через PDO
echo "Test 2: Database connection...\n";
try {
    $pdo = \App\Models\Database::getConnection();
    echo "✅ Successfully connected to Supabase!\n";
    
    // Тест 3: Проверка, что мы подключились к правильной базе данных
    echo "\nTest 3: Verifying database name...\n";
    $stmt = $pdo->query('SELECT current_database()');
    $currentDb = $stmt->fetchColumn();
    
    if ($currentDb === 'storage') {
        echo "✅ Connected to correct database: '{$currentDb}'\n";
    } else {
        echo "⚠️ Connected to database: '{$currentDb}', but expected 'storage'\n";
    }
    
    // Тест 4: Проверка существующих таблиц (если они есть)
    echo "\nTest 4: Checking existing tables...\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "ℹ️ No tables found in public schema. You may need to run schema.sql\n";
    } else {
        echo "✅ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "   - {$table}\n";
        }
    }
    
    // Тест 5: Информация о подключении
    echo "\nTest 5: Connection info...\n";
    $stmt = $pdo->query('SELECT current_user, version()');
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Current user: " . $info['current_user'] . "\n";
    echo "PostgreSQL version: " . substr($info['version'], 0, 60) . "...\n";
    
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    
    echo "\n🔧 Troubleshooting tips:\n";
    echo "1. Check if database 'storage' exists in your Supabase project\n";
    echo "2. Verify the password is correct\n";
    echo "3. Make sure your IP is allowed in Supabase\n";
    echo "4. Try creating the database first if it doesn't exist\n";
}