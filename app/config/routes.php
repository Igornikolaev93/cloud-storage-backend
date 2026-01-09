<?php

$routes = [
    '/' => [
        'GET' => 'FileController@list'
    ],
    // Auth routes
    '/login' => [
        'GET' => 'UserController@showLogin',
        'POST' => 'UserController@login'
    ],
    '/logout' => [
        'GET' => 'UserController@logout'
    ],
    '/register' => [
        'GET' => 'UserController@showRegister',
        'POST' => 'UserController@register'
    ],

    // File routes
    '/files/add' => [
        'POST' => 'FileController@add'
    ],
    '/files/remove/{id}' => [
        'DELETE' => 'FileController@remove'
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

    // Authenticated routes: only accessible when logged in.
    '/' => 'auth',
    '/logout' => 'auth',
    '/files/*' => 'auth',
    '/share/*' => 'auth',

    // Admin routes
    '/admin/*' => 'admin',
];
