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

            $userId = User::create($username, $email, $password);
            $user = User::findById($userId);

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

    public function login(): void
    {
        try {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::findByUsername($username);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new Exception('Invalid username or password.');
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
