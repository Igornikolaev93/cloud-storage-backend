<?php
declare(strict_types=1);

// --- Static File Server ---
$request_uri = $_SERVER['REQUEST_URI'];
$request_path = parse_url($request_uri, PHP_URL_PATH);

if (strpos($request_path, '..') !== false) {
    http_response_code(400);
    echo "400 Bad Request";
    exit;
}

$file_path = __DIR__ . '/public' . $request_path;

if (is_file($file_path)) {
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

    header('Content-Type: ' . $mime_type);
    readfile($file_path);
    exit;
}

// --- Autoloader and Environment Variables ---
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --- Session ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Error Reporting ---
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// --- Project Constants ---
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__);
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', PROJECT_ROOT . '/uploads');
}

// --- Routing ---
require_once PROJECT_ROOT . '/app/routes.php';
