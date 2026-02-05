<?php
declare(strict_types=1);

// This file defines the application's routes and now correctly loads all necessary files.

// --- MANUAL FILE LOADING ---
// Because there is no autoloader, we must require each file manually.

// Controllers
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/FileController.php';
require_once __DIR__ . '/controllers/DirectoryController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/UserController.php';

// Models
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/File.php';
require_once __DIR__ . '/models/Directory.php';
require_once __DIR__ . '/models/Folder.php';
require_once __DIR__ . '/models/Share.php';

// Utilities
require_once __DIR__ . '/utils/Router.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Controllers\DirectoryController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Utils\Router;

// --- Route Definitions ---

// Home Page
Router::get('/', [HomeController::class, 'index']);

// Authentication
Router::get('/register', [AuthController::class, 'showRegisterForm']);
Router::post('/register', [AuthController::class, 'handleRegister']);
Router::get('/login', [AuthController::class, 'showLoginForm']);
Router::post('/login', [AuthController::class, 'handleLogin']);
Router::get('/logout', [AuthController::class, 'logout']);

// File Management
Router::get('/files', [FileController::class, 'index']);
Router::get('/files/list', [FileController::class, 'list']);
Router::get('/files/get/{id}', [FileController::class, 'get']);
Router::post('/files/add', [FileController::class, 'add']);
Router::post('/files/rename', [FileController::class, 'rename']);
Router::post('/files/remove', [FileController::class, 'remove']);

// File Sharing
Router::get('/files/share/{id}', [FileController::class, 'getSharedUsers']);
Router::put('/files/share/{id}/{user_id}', [FileController::class, 'shareWithUser']);
Router::delete('/files/share/{id}/{user_id}', [FileController::class, 'unshareWithUser']);

// Directory Management
Router::post('/directories/add', [DirectoryController::class, 'add']);
Router::post('/directories/rename', [DirectoryController::class, 'rename']);
Router::get('/directories/get/{id}', [DirectoryController::class, 'get']);
Router::post('/directories/remove/{id}', [DirectoryController::class, 'remove']);

// --- DISPATCH THE ROUTER ---
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

Router::dispatch($uri, $method);
