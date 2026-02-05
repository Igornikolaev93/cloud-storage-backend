<?php

require_once __DIR__ . '/app/models/Database.php';

use App\Models\Database;

echo "Starting PostgreSQL database setup...\n";

try {
    $pdo = Database::getConnection();

    // Read the PostgreSQL schema from the dedicated SQL file.
    $sql = file_get_contents(__DIR__ . '/sql/database.pgsql.sql');

    if ($sql === false) {
        throw new Exception("Could not read the database schema file: sql/database.pgsql.sql");
    }

    // Execute the entire SQL script.
    // This is more robust than running individual CREATE TABLE statements.
    $pdo->exec($sql);

    echo "Database setup completed successfully from sql/database.pgsql.sql!\n";

} catch (Exception $e) {
    // Provide a clear error message if the setup fails.
    die("Database setup failed: " . $e->getMessage() . "\n");
}
