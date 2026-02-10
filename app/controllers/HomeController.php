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
     * This method now fetches the user data using the correct Auth::getUser() method and passes it to the view.
     */
    public function index(): void
    {
        if (Auth::check()) {
            // Fetch the user data from the Auth utility using the correct method name.
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
