<?php
declare(strict_types=1);

namespace App\Utils;

class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            // Handle view not found
            Response::json(['error' => 'View not found'], 404);
        }
    }
}
