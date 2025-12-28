<?php

$routes = [
    '/' => [
        'GET' => 'FileController@list'
    ],
    // Auth routes
    '/login' => [
        'POST' => 'UserController@login'
    ],
    '/logout' => [
        'GET' => 'UserController@logout'
    ],
    '/register' => [
        'POST' => 'UserController@register'
    ],
    '/reset-password' => [
        'POST' => 'UserController@reset_password'
    ],

    // User routes
    '/users/list' => [
        'GET' => 'UserController@listUsers'
    ],
    '/users/get/{id}' => [
        'GET' => 'UserController@getUser'
    ],
    '/users/update' => [
        'PUT' => 'UserController@updateProfile'
    ],
    '/users/search/{email}' => [
        'GET' => 'UserController@search'
    ],

    // Admin routes
    '/admin/users/list' => [
        'GET' => 'AdminController@listUsers'
    ],
    '/admin/users/get/{id}' => [
        'GET' => 'AdminController@getUser'
    ],
    '/admin/users/update/{id}' => [
        'PUT' => 'AdminController@updateUser'
    ],
    '/admin/users/delete/{id}' => [
        'DELETE' => 'AdminController@deleteUser'
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
    '/files/share/{id}' => [
        'GET' => 'FileController@getSharedUsers'
    ],
    '/files/share/{id}/{user_id}' => [
        'PUT' => 'FileController@share',
        'DELETE' => 'FileController@unshare'
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
];

$routeFilters = [
    // Guest routes: only accessible when not logged in.
    '/login' => 'guest',
    '/register' => 'guest',
    '/reset-password' => 'guest',

    // Authenticated routes: only accessible when logged in.
    '/' => 'auth',
    '/logout' => 'auth',
    '/users/*' => 'auth',
    '/files/*' => 'auth',
    '/directories/*' => 'auth',

    // Admin routes: only accessible by admin users.
    '/admin/*' => 'admin',
];
