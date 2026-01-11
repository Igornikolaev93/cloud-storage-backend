<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Controllers\DirectoryController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Utils\Router; // <--- ДОБАВЛЕНО

// Главная страница
Router::get('/', [HomeController::class, 'index']);

// Аутентификация
Router::get('/register', [AuthController::class, 'showRegistrationForm']);
Router::post('/register', [AuthController::class, 'register']);
Router::get('/login', [AuthController::class, 'showLoginForm']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);

// Сброс пароля
Router::get('/password/reset', [AuthController::class, 'showPasswordResetRequestForm']);
Router::post('/password/reset', [AuthController::class, 'handlePasswordResetRequest']);
Router::get('/password/reset/{token}', [AuthController::class, 'showPasswordResetForm']);
Router::post('/password/reset/{token}', [AuthController::class, 'resetPassword']);

// --- Поиск пользователя ---
Router::get('/user/search/{email}', [UserController::class, 'search']);

// --- Администрирование пользователей ---
Router::get('/admin/users/list', [AdminController::class, 'listUsers']);
Router::get('/admin/users/get/{id}', [AdminController::class, 'getUser']);
Router::delete('/admin/users/delete/{id}', [AdminController::class, 'deleteUser']);
Router::put('/admin/users/update/{id}', [AdminController::class, 'updateUser']);

// --- Управление файлами ---
Router::get('/files/list', [FileController::class, 'list']);
Router::get('/files/get/{id}', [FileController::class, 'get']);
Router::post('/files/add', [FileController::class, 'add']);
Router::put('/files/rename', [FileController::class, 'rename']);
Router::delete('/files/remove/{id}', [FileController::class, 'remove']);

// --- Управление доступом к файлам ---
Router::get('/files/share/{id}', [FileController::class, 'getSharedUsers']);
Router::put('/files/share/{id}/{user_id}', [FileController::class, 'shareWithUser']);
Router::delete('/files/share/{id}/{user_id}', [FileController::class, 'unshareWithUser']);

// --- Управление папками ---
Router::post('/directories/add', [DirectoryController::class, 'add']);
Router::put('/directories/rename', [DirectoryController::class, 'rename']);
Router::get('/directories/get/{id}', [DirectoryController::class, 'get']);
Router::delete('/directories/delete/{id}', [DirectoryController::class, 'delete']);
