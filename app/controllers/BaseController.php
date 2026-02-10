<?php
declare(strict_types=1);

namespace App\Controllers;

use League\Plates\Engine;

abstract class BaseController
{
    protected Engine $templates;

    public function __construct()
    {
        $this->templates = new Engine(__DIR__ . '/../views');
    }

    protected function render(string $view, array $data = []): void
    {
        echo $this->templates->render($view, $data);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
    
    protected function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function sendErrorResponse(string $message, int $statusCode = 500): void
    {
        $this->sendJsonResponse([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }
}
