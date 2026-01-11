<?php
declare(strict_types=1);

namespace App\Utils;

class Router
{
    private static array $routes = [];

    // Методы для регистрации маршрутов
    public static function get(string $path, array $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, array $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    public static function put(string $path, array $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    public static function delete(string $path, array $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    private static function addRoute(string $method, string $path, array $handler): void
    {
        self::$routes[$method][$path] = $handler;
    }

    // Основной метод для обработки запроса
    public static function dispatch(string $uri, string $method): void
    {
        foreach (self::$routes[$method] ?? [] as $path => $handler) {
            $params = [];
            $pattern = self::buildRegex($path, $params);

            if (preg_match($pattern, $uri, $matches)) {
                // Извлекаем параметры из URI
                $routeParams = [];
                foreach ($params as $key => $name) {
                    if (isset($matches[$key])) {
                        // Декодируем URI-компоненты (например, %20 -> пробел)
                        $routeParams[$name] = urldecode($matches[$key]);
                    }
                }

                self::executeHandler($handler, $routeParams);
                return;
            }
        }

        // Если маршрут не найден
        self::sendErrorResponse('Route not found', 404);
    }

    // Вспомогательный метод для построения регулярного выражения из пути
    private static function buildRegex(string $path, array &$params): string
    {
        $paramIndex = 1;
        // Заменяем плейсхолдеры {id}, {token} и т.д. на регулярные выражения
        $regex = preg_replace_callback('/\\{([^}]+)\\}/', function ($matches) use (&$params, &$paramIndex) {
            $params[$paramIndex++] = $matches[1];
            return '([^/]+)';
        }, $path);

        return "#^$regex$#";
    }

    // Метод для вызова контроллера
    private static function executeHandler(array $handler, array $params): void
    {
        [$controllerClass, $method] = $handler;

        if (!class_exists($controllerClass)) {
            self::sendErrorResponse("Controller class {$controllerClass} not found.");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            self::sendErrorResponse("Method {$method} not found in controller {$controllerClass}.");
            return;
        }

        // Передаем параметры в метод контроллера
        call_user_func_array([$controller, $method], [$params]);
    }

    // Метод для отправки JSON-ответа с ошибкой
    private static function sendErrorResponse(string $message, int $code = 500): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
}
