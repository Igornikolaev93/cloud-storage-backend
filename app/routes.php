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
require_once __DIR__ . '/controllers/ShareController.php';

// Models
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/File.php';
require_once __DIR__ . '/models/Directory.php';
require_once __DIR__ . '/models/Share.php';

// Utilities
require_once __DIR__ . '/utils/Router.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Controllers\DirectoryController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\ShareController;
use App\Utils\Router;

// --- Route Definitions ---

// Home Page
Router::get('/', [HomeController::class, 'index']);

// Authentication
Router::get('/register', [AuthController::class, 'showRegistrationForm']);
Router::post('/register', [AuthController::class, 'register']);
Router::get('/login', [AuthController::class, 'showLoginForm']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);

// File Management
Router::get('/files', [FileController::class, 'index']);
Router::get('/files/list', [FileController::class, 'list']);
Router::get('/files/get/{id}', [FileController::class, 'get']);
Router::post('/files/upload', [FileController::class, 'upload']);
Router::post('/files/rename', [FileController::class, 'rename']);
Router::post('/files/remove', [FileController::class, 'remove']);
Router::get('/files/download/{id}', [FileController::class, 'download']);

// File Sharing
Router::post('/files/share/{id}', [ShareController::class, 'share']);
Router::get('/files/share/{id}', [ShareController::class, 'getSharedUsers']);
Router::put('/files/share/{id}/{user_id}', [ShareController::class, 'shareWithUser']);
Router::delete('/files/share/{id}/{user_id}', [ShareController::class, 'unshare']);

// Directory Management
Router::post('/directories/add', [DirectoryController::class, 'add']);
Router::post('/directories/rename', [DirectoryController::class, 'rename']);
Router::get('/directories/get/{id}', [DirectoryController::class, 'get']);
Router::post('/directories/remove', [DirectoryController::class, 'remove']);

// User Management (Admin)
Router::get('/admin/users', [AdminController::class, 'index']);
Router::get('/admin/users/edit', [AdminController::class, 'editUser']);
Router::post('/admin/users/update', [AdminController::class, 'updateUser']);
Router::post('/admin/users/delete', [AdminController::class, 'deleteUser']);

// Password Reset
Router::get('/password-reset', [UserController::class, 'showPasswordResetRequestForm']);
Router::post('/password-reset', [UserController::class, 'handlePasswordResetRequest']);
Router::get('/password-reset/{token}', [UserController::class, 'showPasswordResetForm']);
Router::post('/password-reset/{token}', [UserController::class, 'handlePasswordReset']);


// --- DISPATCH THE ROUTER ---
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

Router::dispatch($uri, $method);
