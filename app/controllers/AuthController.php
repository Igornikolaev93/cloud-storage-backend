<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use Exception;

// --- FIX: The controller's constructor has been removed. ---
// The session is now started reliably in `index.php` and does not need to be managed here.
class AuthController extends BaseController
{
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
            
            $userId = User::create(['username' => $username, 'email' => $email, 'password' => $password]);

            if (!$userId) {
                throw new Exception('Registration failed. The email might already be in use.');
            }

            $user = User::findById($userId);

            Auth::login($user['id']);
            
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
