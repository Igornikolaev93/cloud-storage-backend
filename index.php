<?php
declare(strict_types=1);

// Включение отображения ошибок (только для разработки)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Автозагрузка Composer
require_once __DIR__ . '/vendor/autoload.php';

// Загрузка конфигураций
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/routes.php';

// Функция для обработки маршрутов
function handleRequest(array $routes, array $routeFilters): void
{
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Убираем конечный слеш
    $requestUri = rtrim($requestUri, '/');
    if (empty($requestUri)) {
        $requestUri = '/';
    }
    
    // Ищем маршрут
    $handler = null;
    $params = [];

    if (isset($routes[$requestUri])) {
        if (isset($routes[$requestUri][$requestMethod])) {
            $handler = $routes[$requestUri][$requestMethod];
        }
    } else {
        // Динамические маршруты
        foreach ($routes as $route => $methods) {
            if (strpos($route, '{') !== false) {
                $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
                if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
                    if (isset($methods[$requestMethod])) {
                        $handler = $methods[$requestMethod];
                        preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
                        $params = array_combine($paramNames[1], array_slice($matches, 1));
                        break;
                    }
                }
            }
        }
    }

    if (!$handler) {
        App\Utils\Response::json(['error' => 'Route not found'], 404);
        return;
    }

    // Проверка фильтров
    foreach ($routeFilters as $filter => $roles) {
        $pattern = str_replace('*', '.*', $filter);
        if (preg_match('#^' . $pattern . '$#', $requestUri)) {
            $roles = is_array($roles) ? $roles : [$roles];
            foreach ($roles as $role) {
                if ($role === 'guest' && App\Utils\Auth::check()) {
                    App\Utils\Response::json(['error' => 'Unauthorized'], 401);
                    return;
                }
                if ($role === 'auth' && !App\Utils\Auth::check()) {
                    App\Utils\Response::json(['error' => 'Unauthorized'], 401);
                    return;
                }
                if ($role === 'admin' && !App\Utils\Auth::hasRole('admin')) {
                    App\Utils\Response::json(['error' => 'Forbidden'], 403);
                    return;
                }
            }
        }
    }

    list($controllerName, $methodName) = explode('@', $handler);
    $fullControllerName = 'App\\Controllers\\' . $controllerName;

    if (!class_exists($fullControllerName)) {
        App\Utils\Response::json(['error' => 'Controller not found'], 500);
        return;
    }

    $controller = new $fullControllerName();

    if (!method_exists($controller, $methodName)) {
        App\Utils\Response::json(['error' => 'Method not found'], 500);
        return;
    }

    // Передаем параметры в метод контроллера
    call_user_func_array([$controller, $methodName], $params);
}

// Запускаем обработку запроса
try {
    App\Utils\Auth::start();
    handleRequest($routes, $routeFilters);
} catch (Throwable $e) {
    App\Utils\Response::json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
    error_log("Unhandled error: " . $e->getMessage());
}
