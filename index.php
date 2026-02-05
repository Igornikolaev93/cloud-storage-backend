<?php
declare(strict_types=1);

// Start the session at the very beginning.
session_start();

require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/controllers/BaseController.php';
require_once __DIR__ . '/app/utils/Auth.php';

use App\Models\Database;

// --- AUTOMATED DATABASE SETUP FOR POSTGRESQL ---
try {
    $pdo = Database::getConnection();

    // Check if the main 'users' table exists. This is a reliable, cross-database way to check for setup.
    // We query the information schema for the table name.
    $stmt = $pdo->prepare("SELECT to_regclass('public.users')");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn();

    // If the table does not exist, run the setup script.
    if ($tableExists === null) {
        // Include the setup script that executes the .sql file.
        require_once __DIR__ . '/database_setup.php';

        // After setup, redirect to the homepage to ensure a clean start.
        header('Location: /');
        exit;
    }
} catch (Exception $e) {
    // If there is any exception during the connection or check, die with a clear error message.
    die("An error occurred during application startup: " . $e->getMessage());
}

// --- ROUTING ---
// The file containing the route definitions is app/routes.php
require_once __DIR__ . '/app/routes.php';
