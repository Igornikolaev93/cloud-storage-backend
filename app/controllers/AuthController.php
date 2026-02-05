<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use App\Utils\Auth;

class AuthController extends BaseController
{
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showLoginForm(): void
    {
        $this->renderView('login');
    }

    public function handleLogin(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            header('Location: /');
            exit;
        } else {
            $this->renderView('login', ['error' => 'Invalid email or password.']);
        }
    }

    public function showRegisterForm(): void
    {
        $this->renderView('register');
    }

    public function handleRegister(): void
    {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $this->renderView('register', ['error' => 'All fields are required.']);
            return;
        }

        try {
            $userId = User::create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
            ]);

            if ($userId) {
                $user = User::findById($userId);
                if ($user) {
                    Auth::login($user);
                }
                header('Location: /');
                exit;
            } else {
                $this->renderView('register', ['error' => 'An error occurred during registration.']);
            }
        } catch (\Exception $e) {
             $this->renderView('register', ['error' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    public function showPasswordResetRequestForm(): void
    {
        $this->renderView('password_reset_request');
    }

    public function handlePasswordResetRequest(): void
    {
        $email = $_POST['email'] ?? '';
        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            PasswordReset::create($user['id'], $token);
            $this->renderView('password_reset_request', ['message' => 'If an account with that email exists, a password reset link has been sent.']);
        } else {
             $this->renderView('password_reset_request', ['message' => 'If an account with that email exists, a password reset link has been sent.']);
        }
    }

    public function showPasswordResetForm(string $token): void
    {
        $passwordReset = PasswordReset::findByToken($token);

        if ($passwordReset && strtotime($passwordReset['expires_at']) > time()) {
            $this->renderView('password_reset', ['token' => $token]);
        } else {
            $this->renderView('password_reset_request', ['error' => 'Invalid or expired token.']);
        }
    }

    public function handlePasswordReset(string $token): void
    {
        $passwordReset = PasswordReset::findByToken($token);

        if (!$passwordReset || strtotime($passwordReset['expires_at']) <= time()) {
            $this->renderView('password_reset_request', ['error' => 'Invalid or expired token.']);
            return;
        }

        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            $this->renderView('password_reset', ['token' => $token, 'error' => 'Password cannot be empty.']);
            return;
        }

        User::updatePassword($passwordReset['user_id'], $password);
        PasswordReset::deleteByToken($token);

        header('Location: /login?message=Password+reset+successful.+Please+login.');
        exit;
    }
}
