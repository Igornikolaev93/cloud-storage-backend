<?php
declare(strict_types=1);

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
// GET /files/list - Вывести список файлов и папок в корне
Router::get('/files/list', [FileController::class, 'list']);

// GET /files/get/{id} - Получить информацию о конкретном файле
Router::get('/files/get/{id}', [FileController::class, 'get']);

// POST /files/add - Добавить (загрузить) файл
Router::post('/files/add', [FileController::class, 'add']);

// PUT /files/rename - Переименовать файл
Router::put('/files/rename', [FileController::class, 'rename']);

// DELETE /files/remove/{id} - Удалить файл
Router::delete('/files/remove/{id}', [FileController::class, 'remove']);

// --- Управление доступом к файлам ---
// GET /files/share/{id} - Получить список пользователей, имеющих доступ к файлу
Router::get('/files/share/{id}', [FileController::class, 'getSharedUsers']);

// PUT /files/share/{id}/{user_id} - Добавить доступ к файлу пользователю
Router::put('/files/share/{id}/{user_id}', [FileController::class, 'shareWithUser']);

// DELETE /files/share/{id}/{user_id} - Прекратить доступ к файлу
Router::delete('/files/share/{id}/{user_id}', [FileController::class, 'unshareWithUser']);

// --- Управление папками ---
// POST /directories/add - Добавить папку (директорию)
Router::post('/directories/add', [DirectoryController::class, 'add']);

// PUT /directories/rename - Переименовать папку
Router::put('/directories/rename', [DirectoryController::class, 'rename']);

// GET /directories/get/{id} - Получить содержимое папки
Router::get('/directories/get/{id}', [DirectoryController::class, 'get']);

// DELETE /directories/delete/{id} - Удалить папку
Router::delete('/directories/delete/{id}', [DirectoryController::class, 'delete']);
