<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Database;

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "Starting PostgreSQL database setup for 'storage' database...\n";

// Check for pdo_pgsql extension
if (!extension_loaded('pdo_pgsql')) {
    echo "\n--- ERROR ---\n";
    echo "The pdo_pgsql PHP extension is not enabled.\n";
    echo "You need to enable it in your php.ini file to connect to a PostgreSQL database.\n";
    echo "Find your php.ini file (usually in C:\\xampp\\php\\php.ini) and uncomment this line:\n";
    echo ";extension=pdo_pgsql  (remove the semicolon at the beginning)\n";
    exit(1);
}

echo "✅ PDO PostgreSQL extension is enabled.\n";

try {
    echo "Attempting to connect to the 'storage' database...\n";
    $pdo = Database::getConnection();
    echo "✅ Successfully connected to the 'storage' database.\n";

    // Проверяем, подключились ли мы к правильной базе
    $stmt = $pdo->query('SELECT current_database()');
    $currentDb = $stmt->fetchColumn();
    echo "Current database: {$currentDb}\n";

    if ($currentDb !== 'storage') {
        echo "⚠️ Warning: Connected to '{$currentDb}' but expected 'storage'\n";
    }

    // Читаем и выполняем schema.sql
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) {
        throw new RuntimeException("Could not read schema.sql file.");
    }
    echo "✅ Read schema.sql file successfully.\n";

    // Выполняем SQL
    $pdo->exec($sql);
    echo "✅ Database tables created (or already exist).\n";
    
    // Проверяем созданные таблицы
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📊 Tables in 'storage' database:\n";
    foreach ($tables as $table) {
        echo "   - {$table}\n";
    }
    
    echo "\n✅ Database setup completed successfully!\n";

} catch (Exception $e) {
    echo "\n--- DATABASE SETUP FAILED ---\n";
    echo $e->getMessage() . "\n";
    echo "\n--- TROUBLESHOOTING ---\n";
    echo "1. Does the database 'storage' exist in your Supabase project?\n";
    echo "2. Is your Supabase project active and not hibernating?\n";
    echo "3. Have you added your IP address to the trusted addresses?\n";
    echo "4. Check if you have permission to create tables in the 'storage' database\n";
    exit(1);
}