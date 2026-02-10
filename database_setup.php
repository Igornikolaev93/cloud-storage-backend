<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Database;

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "Starting PostgreSQL database setup...\n";

// Check for pdo_pgsql extension
if (!extension_loaded('pdo_pgsql')) {
    echo "\n--- ERROR ---\n";
    echo "The pdo_pgsql PHP extension is not enabled.\n";
    echo "You need to enable it in your php.ini file to connect to a PostgreSQL database.\n";
    echo "Find your php.ini file (usually in C:\\xampp\\php\\php.ini) and uncomment this line:\n";
    echo ";extension=pdo_pgsql  (remove the semicolon at the beginning)\n";
    exit(1);
}

echo "PDO PostgreSQL extension is enabled.\n";

try {
    echo "Attempting to connect to the database...\n";
    $pdo = Database::getConnection();
    echo "Successfully connected to the database.\n";

    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) {
        throw new RuntimeException("Could not read schema.sql file.");
    }
    echo "Read schema.sql file successfully.\n";

    $pdo->exec($sql);
    echo "Database tables created (or already exist).\n";
    echo "\nDatabase setup completed successfully!\n";

} catch (Exception $e) {
    echo "\n--- DATABASE SETUP FAILED ---\n";
    echo $e->getMessage() . "\n";
    echo "\n--- TROUBLESHOOTING ---\n";
    echo "1. Is the database on render.com active and not hibernating?\n";
    echo "2. Have you added your IP address to the trusted addresses in the render.com dashboard?\n";
    echo "   For testing, you can add 0.0.0.0/0 to allow all IPs.\n";
    exit(1);
}
