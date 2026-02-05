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
     * Displays the home page.
     * This method now fetches the user data and passes it to the view.
     */
    public function index(): void
    {
        if (Auth::check()) {
            // Fetch the user data from the Auth utility.
            $user = Auth::getUser();

            // If for some reason the user is not found in the session, redirect to login.
            if (!$user) {
                header('Location: /login');
                exit;
            }
            
            // Pass the user data to the view.
            $this->renderView('home', ['user' => $user]);
        } else {
            header('Location: /login');
            exit;
        }
    }
}
