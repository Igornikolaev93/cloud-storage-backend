<?php
declare(strict_types=1);

// --- FORCE ERROR DISPLAY FOR DEBUGGING ---
// This is the most important step to solve the 'empty page' problem.
// It will force PHP to display the error that is being hidden.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Start the session at the very beginning.
session_start();

require_once __DIR__ . '/app/models/Database.php';
require_once __DIR__ . '/app/controllers/BaseController.php';
require_once __DIR__ . '/app/utils/Auth.php';

use App\Models\Database;

// --- AUTOMATED DATABASE SETUP FOR POSTGRESQL ---
try {
    $pdo = Database::getConnection();

    // Check if the main 'users' table exists.
    $stmt = $pdo->prepare("SELECT to_regclass('public.users')");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn();

    if ($tableExists === null) {
        require_once __DIR__ . '/database_setup.php';
        header('Location: /');
        exit;
    }
} catch (Exception $e) {
    die("An error occurred during application startup: " . $e->getMessage());
}

// --- ROUTING ---
require_once __DIR__ . '/app/routes.php';
