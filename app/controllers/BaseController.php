<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * Базовый контроллер, от которого будут наследоваться все остальные контроллеры.
 * Содержит общие вспомогательные методы.
 */
abstract class BaseController
{
    /**
     * Renders a view file.
     *
     * @param string $view The view file to render.
     * @param array $data Data to pass to the view.
     */
    protected function renderView(string $view, array $data = []): void
    {
        // Make data available to the view
        extract($data);

        // Path to the view file
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            // Handle view not found
            $this->sendErrorResponse("View not found: {$view}", 404);
        }
    }

    /**
     * Отправляет унифицированный JSON-ответ клиенту.
     *
     * @param array $data Данные для кодирования в JSON.
     * @param int $statusCode HTTP-статус код ответа (по умолчанию 200).
     */
    protected function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        // Устанавливаем HTTP-статус
        http_response_code($statusCode);

        // Устанавливаем заголовок, чтобы клиент знал, что это JSON
        header('Content-Type: application/json');

        // Кодируем данные в JSON и выводим их
        echo json_encode($data);
    }

    /**
     * Вспомогательный метод для отправки стандартизированного ответа об ошибке.
     *
     * @param string $message Сообщение об ошибке.
     * @param int $statusCode HTTP-статус код (по умолчанию 500).
     */
    protected function sendErrorResponse(string $message, int $statusCode = 500): void
    {
        $this->sendJsonResponse([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }
}
