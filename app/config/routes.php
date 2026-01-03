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
        'GET' => 'FileController@remove'
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
];
