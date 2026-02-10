<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use Exception;

class AuthController extends BaseController
{
    public function showRegistrationForm(): void
    {
        $this->render('register');
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
            
            $userId = User::create(['username' => $username, 'email' => $email, 'password' => $password]);

            if (!$userId) {
                throw new Exception('Registration failed. The email might already be in use.');
            }

            $user = User::findById($userId);

            Auth::login($user['id']);
            
            header('Location: /files');
            exit;

        } catch (Exception $e) {
            $this->render('register', ['error' => $e->getMessage()]);
        }
    }

    public function showLoginForm(): void
    {
        $this->render('login');
    }

    public function login(): void
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::findByEmail($email);

            if ($user) {
                // User exists, verify password
                if (password_verify($password, $user['password_hash'])) {
                    Auth::login($user['id']);
                    header('Location: /files');
                    exit;
                } else {
                    throw new Exception('Invalid email or password.');
                }
            } else {
                // User does not exist, create a new one
                $username = explode('@', $email)[0];
                $userId = User::create(['username' => $username, 'email' => $email, 'password' => $password]);

                if (!$userId) {
                    throw new Exception('Registration failed.');
                }

                $newUser = User::findById($userId);
                Auth::login($newUser['id']);
                header('Location: /files');
                exit;
            }
        } catch (Exception $e) {
            $this->render('login', ['error' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
