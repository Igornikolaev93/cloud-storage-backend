<?php
declare(strict_types=1);

// --- ГАРАНТИРОВАННАЯ ЗАГРУЗКА ROUTER ---
require_once __DIR__ . '/utils/Router.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Controllers\DirectoryController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Utils\Router;

// Главная страница
Router::get('/', [HomeController::class, 'index']);

// Аутентификация
Router::get('/register', [AuthController::class, 'showRegisterForm']);
Router::post('/register', [AuthController::class, 'handleRegister']);
Router::get('/login', [AuthController::class, 'showLoginForm']);
Router::post('/login', [AuthController::class, 'handleLogin']);
Router::get('/logout', [AuthController::class, 'logout']);

// --- Управление файлами ---
Router::get('/files', [FileController::class, 'index']);
Router::get('/files/list', [FileController::class, 'list']);
Router::get('/files/get/{id}', [FileController::class, 'get']);
Router::post('/files/add', [FileController::class, 'add']);
Router::post('/files/rename', [FileController::class, 'rename']);
Router::post('/files/remove', [FileController::class, 'remove']);

// --- Управление доступом к файлам ---
Router::get('/files/share/{id}', [FileController::class, 'getSharedUsers']);
Router::put('/files/share/{id}/{user_id}', [FileController::class, 'shareWithUser']);
Router::delete('/files/share/{id}/{user_id}', [FileController::class, 'unshareWithUser']);

// --- Управление папками ---
Router::post('/directories/add', [DirectoryController::class, 'add']);
Router::post('/directories/rename', [DirectoryController::class, 'rename']);
Router::get('/directories/get/{id}', [DirectoryController::class, 'get']);
Router::post('/directories/remove/{id}', [DirectoryController::class, 'remove']);
