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

            if (empty($username) || empty($email) || empty($password)) {
                throw new Exception('Please fill in all required fields.');
            }

            // This check was removed as there is no password_confirm field in the form
            // if ($password !== $passwordConfirm) {
            //     throw new Exception('Passwords do not match.');
            // }
            
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => 'user' // Default role
            ];

            // Grant admin role if the email matches
            if ($email === 'admin@example.com') {
                $userData['role'] = 'admin';
            }

            $userId = User::create($userData);

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

            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new Exception('Invalid email or password.');
            }

            Auth::login($user['id']);

            header('Location: /files');
            exit;

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
