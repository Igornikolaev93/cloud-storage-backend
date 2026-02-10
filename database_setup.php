<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Database;

// This script should be run from the command line, not in a browser.
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "Starting PostgreSQL database setup...\n";

try {
    // Get the PDO connection
    $pdo = Database::getConnection();

    echo "Successfully connected to the database.\n";

    // Read the schema file
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) {
        throw new RuntimeException("Could not read schema.sql file.");
    }

    echo "Read schema.sql file successfully.\n";

    // Execute the SQL to create tables
    $pdo->exec($sql);

    echo "Database tables created (or already exist).\n";

    // Optional: Check if tables were created
    $tables = ['users', 'files', 'password_resets'];
    $all_exist = true;
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SELECT 1 FROM \"{$table}\" LIMIT 1");
            if ($result === false) {
                echo "Verification failed for table: {$table}.\n";
                $all_exist = false;
            } else {
                echo "Table '{$table}' verified.\n";
            }
        } catch (PDOException $e) {
            echo "Verification failed for table: {$table}. Error: " . $e->getMessage() . "\n";
            $all_exist = false;
        }
    }

    if ($all_exist) {
        echo "\nDatabase setup completed successfully!\n";
    } else {
        echo "\nDatabase setup completed, but some tables may not have been created correctly. Please check the output above.\n";
    }

} catch (Exception $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    exit(1); // Exit with an error code
}
