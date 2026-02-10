<?php
declare(strict_types=1);

// --- Static File Server ---
$request_uri = $_SERVER['REQUEST_URI'];
$request_path = parse_url($request_uri, PHP_URL_PATH);

// Prevent directory traversal attacks.
if (strpos($request_path, '..') !== false) {
    http_response_code(400);
    echo "400 Bad Request";
    exit;
}

// Construct the full path to the requested file in the 'public' directory.
$file_path = __DIR__ . '/public' . $request_path;

if (is_file($file_path)) {
    // Determine the MIME type based on the file extension.
    $mime_types = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
    ];
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mime_type = $mime_types[$extension] ?? 'application/octet-stream';

    // Set the content type header and serve the file.
    header('Content-Type: ' . $mime_type);
    readfile($file_path);
    exit;
}

// --- CRITICAL FIX ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Error Reporting ---
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// --- Project Root and Uploads Directory ---
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', PROJECT_ROOT . '/uploads');
}

// --- Autoloading and Routing ---
require_once PROJECT_ROOT . '/app/utils/Router.php';
require_once PROJECT_ROOT . '/app/utils/Auth.php';
require_once PROJECT_ROOT . '/app/models/Database.php';
require_once PROJECT_ROOT . '/app/models/User.php';
require_once PROJECT_ROOT . '/app/models/File.php';
require_once PROJECT_ROOT . '/app/controllers/BaseController.php';
require_once PROJECT_ROOT . '/app/controllers/HomeController.php';
require_once PROJECT_ROOT . '/app/controllers/AuthController.php';
require_once PROJECT_ROOT . '/app/controllers/FileController.php';
require_once PROJECT_ROOT . '/app/controllers/DirectoryController.php';

// --- Route Definitions ---
require_once PROJECT_ROOT . '/app/routes.php';
