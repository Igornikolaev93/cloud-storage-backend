<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;

class HomeController extends BaseController
{
    public function __construct()
    {
        parent::__construct(); // Initializes the templating engine
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * GET /
     * Displays the home page.
     */
    public function index(): void
    {
        if (Auth::check()) {
            $user = Auth::getUser();

            if (!$user) {
                // Use the new redirect method
                $this->redirect('/login');
            }
            
            // Use the new render method
            $this->render('home', ['user' => $user]);
        } else {
            // Use the new redirect method
            $this->redirect('/login');
        }
    }
}
