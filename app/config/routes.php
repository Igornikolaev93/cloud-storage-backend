<?php

$routes = [
    '/' => [
        'GET' => 'FileController@list'
    ],
    // Auth routes
    '/login' => [
        'GET' => 'AuthController@showLoginForm',
        'POST' => 'AuthController@login'
    ],
    '/logout' => [
        'GET' => 'AuthController@logout'
    ],
    '/register' => [
        'GET' => 'AuthController@showRegistrationForm',
        'POST' => 'AuthController@register'
    ],
    '/password/reset' => [
        'GET' => 'AuthController@showPasswordResetRequestForm',
        'POST' => 'AuthController@handlePasswordResetRequest',
    ],
    '/password/reset/{token}' => [
        'GET' => 'AuthController@showPasswordResetForm',
        'POST' => 'AuthController@resetPassword'
    ],

    // File routes
    '/files/list' => [
        'GET' => 'FileController@list'
    ],
    '/files/get/{id}' => [
        'GET' => 'FileController@get'
    ],
    '/files/add' => [
        'POST' => 'FileController@add'
    ],
    '/files/rename' => [
        'PUT' => 'FileController@rename'
    ],
    '/files/remove/{id}' => [
        'DELETE' => 'FileController@remove'
    ],

    // Directory routes
    '/directories/add' => [
        'POST' => 'DirectoryController@add'
    ],
    '/directories/rename' => [
        'PUT' => 'DirectoryController@rename'
    ],
    '/directories/get/{id}' => [
        'GET' => 'DirectoryController@get'
    ],
    '/directories/delete/{id}' => [
        'DELETE' => 'DirectoryController@delete'
    ],

    // Share routes
    '/share/add/{fileId}' => [
        'POST' => 'ShareController@share'
    ],
    '/share/remove/{fileId}/{userId}' => [
        'GET' => 'ShareController@unshare'
    ],

    // Admin routes
    '/admin/users' => [
        'GET' => 'AdminController@users'
    ],
    '/admin/users/{id}/role' => [
        'POST' => 'AdminController@changeRole'
    ],
    '/admin/users/delete/{id}' => [
        'POST' => 'AdminController@deleteUser'
    ],
];

$routeFilters = [
    // Guest routes: only accessible when not logged in.
    '/login' => 'guest',
    '/register' => 'guest',
    '/password/reset' => 'guest',
    '/password/reset/*' => 'guest',

    // Authenticated routes: only accessible when logged in.
    '/' => 'auth',
    '/logout' => 'auth',
    '/files/*' => 'auth',
    '/directories/*' => 'auth',
    '/share/*' => 'auth',

    // Admin routes
    '/admin/*' => 'admin',
];
