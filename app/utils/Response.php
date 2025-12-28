<?php
declare(strict_types=1);

namespace App\Utils;

class Response
{
    /**
     * Отправляет JSON-ответ.
     *
     * @param mixed $data Данные для кодирования в JSON.
     * @param int $statusCode HTTP-статус код.
     */
    public static function json($data, int $statusCode = 200): void
    {
        // Устанавливаем HTTP-статус
        http_response_code($statusCode);

        // Устанавливаем заголовок Content-Type
        header('Content-Type: application/json');

        // Выводим данные в формате JSON и завершаем выполнение скрипта
        echo json_encode($data);
        exit;
    }
}
