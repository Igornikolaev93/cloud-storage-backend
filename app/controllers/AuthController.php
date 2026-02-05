<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\Router;
use Exception;

class AuthController extends BaseController
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showRegistrationForm(): void
    {
        $this->renderView('register');
    }

    public function register(): void
    {
        try {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            if ($password !== $passwordConfirm) {
                throw new Exception('Passwords do not match.');
            }
            
            $user = User::create(['username' => $username, 'email' => $email, 'password' => $password]);

            if (!$user) {
                throw new Exception('Registration failed. The email might already be in use.');
            }

            Auth::login($user);
            
            header('Location: /files');
            exit;

        } catch (Exception $e) {
            $this->renderView('register', ['error' => $e->getMessage()]);
        }
    }

    public function showLoginForm(): void
    {
        $this->renderView('login');
    }

    // --- FIX: Corrected the login method to use email instead of username ---
    public function login(): void
    {
        try {
            // The form will now submit an 'email' field instead of 'username'.
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // The User model uses `findByEmail`, not `findByUsername`.
            $user = User::findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new Exception('Invalid email or password.');
            }

            Auth::login($user);

            header('Location: /files');
            exit;

        } catch (Exception $e) {
            $this->renderView('login', ['error' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
