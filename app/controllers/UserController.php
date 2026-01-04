<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\View;
use Exception;

class UserController extends BaseController
{
    /**
     * Show the login page.
     */
    public function showLogin(): void
    {
        View::render('login');
    }

    /**
     * Handle user login from a form submission.
     */
    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            View::render('login', ['error' => 'Email and password are required']);
            return;
        }

        try {
            $user = User::verifyCredentials($email, $password);
            if ($user) {
                Auth::login($user);

                // Redirect admins to the admin dashboard
                if (Auth::hasRole('admin')) {
                    header('Location: /admin/users');
                } else {
                    header('Location: /');
                }
                exit;
            } else {
                View::render('login', ['error' => 'Invalid credentials']);
            }
        } catch (Exception $e) {
            View::render('login', ['error' => 'Login failed: ' . $e->getMessage()]);
        }
    }

    /**
     * User logout.
     */
    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    /**
     * Show the registration page.
     */
    public function showRegister(): void
    {
        View::render('register');
    }

    /**
     * Handle user registration from a form submission.
     */
    public function register(): void
    {
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];

        try {
            $userId = User::create($data);
            if ($userId) {
                header('Location: /login');
                exit;
            } else {
                View::render('register', ['error' => 'Failed to create user']);
            }
        } catch (Exception $e) {
            View::render('register', ['error' => $e->getMessage()]);
        }
    }
}
