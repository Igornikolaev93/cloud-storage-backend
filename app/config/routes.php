<?php
declare(strict_types=1);

$routes = [
    // Главная страница
    '/' => [
        'GET' => 'IndexController@index'
    ],
    
    // Аутентификация
    '/auth/login' => [
        'POST' => 'AuthController@login'
    ],
    '/auth/logout' => [
        'GET' => 'AuthController@logout'
    ],
    '/auth/register' => [
        'POST' => 'AuthController@register'
    ],
    '/auth/reset-password' => [
        'POST' => 'AuthController@resetPassword'
    ],
    '/auth/verify-token' => [
        'POST' => 'AuthController@verifyToken'
    ],
    '/auth/change-password' => [
        'POST' => 'AuthController@changePassword'
    ],
    
    // Пользователи
    '/users/profile' => [
        'GET' => 'UserController@getProfile',
        'PUT' => 'UserController@updateProfile'
    ],
    '/users/list' => [
        'GET' => 'UserController@listUsers'
    ],
    '/users/search' => [
        'GET' => 'UserController@search'
    ],
    '/users/get/{id}' => [
        'GET' => 'UserController@getUser'
    ],
    
    // Администратор
    '/admin/users' => [
        'GET' => 'AdminController@listUsers',
        'POST' => 'AdminController@createUser'
    ],
    '/admin/users/{id}' => [
        'GET' => 'AdminController@getUser',
        'PUT' => 'AdminController@updateUser',
        'DELETE' => 'AdminController@deleteUser'
    ],
    '/admin/stats' => [
        'GET' => 'AdminController@getStats'
    ],
    
    // Файлы
    '/files' => [
        'GET' => 'FileController@list',
        'POST' => 'FileController@upload'
    ],
    '/files/{id}' => [
        'GET' => 'FileController@get',
        'PUT' => 'FileController@update',
        'DELETE' => 'FileController@delete'
    ],
    '/files/{id}/download' => [
        'GET' => 'FileController@download'
    ],
    '/files/{id}/share' => [
        'GET' => 'FileController@getShares',
        'POST' => 'FileController@addShare',
        'DELETE' => 'FileController@removeShare'
    ],
    '/files/shared' => [
        'GET' => 'FileController@listShared'
    ],
    
    // Папки
    '/folders' => [
        'GET' => 'FolderController@list',
        'POST' => 'FolderController@create'
    ],
    '/folders/{id}' => [
        'GET' => 'FolderController@get',
        'PUT' => 'FolderController@update',
        'DELETE' => 'FolderController@delete'
    ],
    '/folders/{id}/files' => [
        'GET' => 'FolderController@listFiles'
    ],
    
    // Поиск
    '/search' => [
        'GET' => 'SearchController@search'
    ],
    
    // Статистика
    '/stats' => [
        'GET' => 'StatsController@getStats'
    ]
];

// Фильтры для маршрутов (middleware)
$routeFilters = [
    '/auth/login' => 'guest',
    '/auth/register' => 'guest',
    '/auth/reset-password' => 'guest',
    '/users/*' => 'auth',
    '/admin/*' => ['auth', 'admin'],
    '/files/*' => 'auth',
    '/folders/*' => 'auth',
    '/search' => 'auth',
    '/stats' => 'auth'
];