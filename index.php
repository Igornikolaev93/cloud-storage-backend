<?php
declare(strict_types=1);

// Включение отображения ошибок (только для разработки)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Загрузка конфигураций
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/config/routes.php';

// Автозагрузка классов
spl_autoload_register(function ($className) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($className, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    } else {
        error_log("Class file not found: " . $file);
    }
});

// Функция для обработки маршрутов
function handleRequest(array $routes): void
{
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Убираем конечный слеш
    $requestUri = rtrim($requestUri, '/');
    if (empty($requestUri)) {
        $requestUri = '/';
    }
    
    // Проверяем статические файлы
    if (file_exists(__DIR__ . $requestUri) && $requestUri !== '/') {
        return;
    }
    
    // Ищем маршрут
    if (isset($routes[$requestUri])) {
        $route = $routes[$requestUri];
        
        if (isset($route[$requestMethod])) {
            $handler = $route[$requestMethod];
            
            // Поддерживаем как строки "Controller@method", так и массивы
            if (is_string($handler)) {
                list($controllerName, $methodName) = explode('@', $handler);
            } elseif (is_array($handler) && count($handler) === 2) {
                $controllerName = $handler[0];
                $methodName = $handler[1];
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Invalid route handler']);
                return;
            }
            
            // Проверяем существование класса
            $fullControllerName = 'App\\Controllers\\' . $controllerName;
            if (!class_exists($fullControllerName)) {
                http_response_code(404);
                echo json_encode(['error' => 'Controller not found']);
                return;
            }
            
            // Создаем контроллер и вызываем метод
            try {
                $controller = new $fullControllerName();
                
                if (!method_exists($controller, $methodName)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Method not found']);
                    return;
                }
                
                $controller->$methodName();
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'error' => 'Internal server error',
                    'message' => DEBUG_MODE ? $e->getMessage() : 'Something went wrong'
                ]);
                error_log($e->getMessage());
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    } else {
        // Динамические маршруты с параметрами
        $matched = false;
        foreach ($routes as $route => $methods) {
            // Проверяем динамические маршруты типа /users/get/{id}
            if (strpos($route, '{') !== false) {
                $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
                $pattern = str_replace('/', '\/', $pattern);
                
                if (preg_match('/^' . $pattern . '$/', $requestUri, $matches)) {
                    if (isset($methods[$requestMethod])) {
                        $handler = $methods[$requestMethod];
                        
                        // Извлекаем параметры
                        preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
                        $params = array_combine($paramNames[1], array_slice($matches, 1));
                        
                        // Вызываем обработчик с параметрами
                        $_REQUEST['route_params'] = $params;
                        
                        if (is_string($handler)) {
                            list($controllerName, $methodName) = explode('@', $handler);
                        } else {
                            $controllerName = $handler[0];
                            $methodName = $handler[1];
                        }
                        
                        $fullControllerName = 'App\\Controllers\\' . $controllerName;
                        if (class_exists($fullControllerName)) {
                            $controller = new $fullControllerName();
                            $controller->$methodName();
                            $matched = true;
                            break;
                        }
                    }
                }
            }
        }
        
        if (!$matched) {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found: ' . $requestUri]);
        }
    }
}

// Запускаем обработку запроса
try {
    handleRequest($routes);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
    ]);
    error_log("Unhandled error: " . $e->getMessage());
}