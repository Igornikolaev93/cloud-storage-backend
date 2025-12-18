<?php

$routes = [
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
    '/users/*' => 'auth',
    '/admin/*' => 'admin',
    '/files/*' => 'auth',
    '/directories/*' => 'auth',
];
