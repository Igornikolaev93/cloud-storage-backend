<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * GET /
     * Отображает главную страницу.
     */
    public function index(): void
    {
        // Просто выводим приветственное сообщение в формате JSON
        $this->sendJsonResponse([
            'status' => 'success',
            'message' => 'Welcome to the Cloud Storage API!',
            'documentation' => 'Please see the API documentation for available endpoints.'
        ]);
    }
}
