<?php
declare(strict_types=1);

// --- Static File Server ---
// If the request is for a file in the public directory, serve it directly.
$path = __DIR__ . '/public' . $_SERVER['REQUEST_URI'];
if (is_file($path)) {
    return false;
}

// --- CRITICAL FIX ---
// The session must be started at the very beginning of the application's entry point.
// Without this, session data (like the logged-in user) is lost on every request.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Error Reporting ---
// Enables detailed error reporting to help with debugging during development.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// --- Project Root and Uploads Directory ---
// Defines constants for the project root and the directory for file uploads.
// Using __DIR__ ensures these paths are always correct, regardless of where the script is run.
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', PROJECT_ROOT . '/uploads');
}

// --- Autoloading and Routing ---
// This section includes all necessary files for the application to function.
// Because there is no Composer autoloader, each file is required manually.
// The order of inclusion is important: utilities and base classes first, then models, then controllers.

// Utilities - Core components like the router and database connection.
require_once PROJECT_ROOT . '/app/utils/Router.php';
require_once PROJECT_ROOT . '/app/utils/Auth.php';

// Models - Data-layer classes that interact with the database.
require_once PROJECT_ROOT . '/app/models/Database.php';
require_once PROJECT_ROOT . '/app/models/User.php';
require_once PROJECT_ROOT . '/app/models/File.php';

// Controllers - Logic that handles user requests and interacts with models and views.
require_once PROJECT_ROOT . '/app/controllers/BaseController.php';
require_once PROJECT_ROOT . '/app/controllers/HomeController.php';
require_once PROJECT_ROOT . '/app/controllers/AuthController.php';
require_once PROJECT_ROOT . '/app/controllers/FileController.php';
require_once PROJECT_ROOT . '/app/controllers/DirectoryController.php';

// --- Route Definitions ---
// This file contains all the URL-to-controller mappings for the application.
require_once PROJECT_ROOT . '/app/routes.php';
