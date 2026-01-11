<?php

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\View;

class AuthController extends BaseController
{
    public function showRegistrationForm(): void
    {
        View::render('register');
    }

    public function register(): void
    {
        $data = [
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
        ];

        // Basic validation
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            // Handle error: fields cannot be empty
            View::render('register', ['error' => 'All fields are required.']);
            return;
        }

        if (User::findByEmail($data['email'])) {
            // Handle error: user already exists
            View::render('register', ['error' => 'User with this email already exists.']);
            return;
        }

        $userId = User::create($data);

        if ($userId) {
            Auth::login($userId);
            header('Location: /');
        } else {
            // Handle error: registration failed
            View::render('register', ['error' => 'An error occurred during registration.']);
        }
    }

    public function showLoginForm(): void
    {
        View::render('login');
    }

    public function login(): void
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $user = User::findByEmail($email);

        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            Auth::login($user['id']);
            header('Location: /');
        } else {
            View::render('login', ['error' => 'Invalid email or password.']);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
    }

    public function showPasswordResetRequestForm(): void
    {
        View::render('password_reset_request');
    }

    public function handlePasswordResetRequest(): void
    {
        $email = $_POST['email'];
        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            User::createPasswordResetToken($email, $token);

            // In a real application, you would send an email with this link.
            // For this example, we'll just show a message.
            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $token;
            View::render('password_reset_request', ['message' => 'Password reset link: ' . $resetLink]);
        } else {
            View::render('password_reset_request', ['error' => 'No user found with that email address.']);
        }
    }

    public function showPasswordResetForm($params): void
    {
        $token = $params['token'];
        View::render('password_reset', ['token' => $token]);
    }

    public function resetPassword($params): void
    {
        $token = $params['token'];
        $password = $_POST['password'];
        
        $user = User::findUserByPasswordResetToken($token);

        if ($user) {
            User::updatePassword($user['id'], $password);
            User::deletePasswordResetToken($token);
            View::render('login', ['message' => 'Password has been reset. You can now log in.']);
        } else {
            View::render('password_reset', ['token' => $token, 'error' => 'Invalid or expired token.']);
        }
    }
}
