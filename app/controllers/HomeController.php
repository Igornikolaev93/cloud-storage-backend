<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;

class HomeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
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
            $user = Auth::getUser();

            if (!$user) {
                $this->redirect('/login');
            }
            
            $this->render('home', ['user' => $user]);
        } else {
            $this->redirect('/login');
        }
    }
}
