<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Database;

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "Starting database seeding...\n";

try {
    echo "Attempting to connect to the database...\n";
    $pdo = Database::getConnection();
    echo "Successfully connected to the database.\n";

    $sql = file_get_contents(__DIR__ . '/sql/seed_data.sql');
    if ($sql === false) {
        throw new RuntimeException("Could not read seed_data.sql file.");
    }
    echo "Read seed_data.sql file successfully.\n";

    $pdo->exec($sql);
    echo "Database seeding completed successfully!\n";

} catch (Exception $e) {
    echo "\n--- DATABASE SEEDING FAILED ---\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
