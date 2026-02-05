<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;

class HomeController extends BaseController
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * GET /
     * Отображает главную страницу.
     */
    public function index(): void
    {
        if (Auth::check()) {
            $this->renderView('home');
        } else {
            header('Location: /login');
            exit;
        }
    }
}
