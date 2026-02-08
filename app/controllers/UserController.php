<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utils\View;
use Exception;

class UserController extends BaseController
{
    public function showPasswordResetRequestForm(): void
    {
        View::render('password_reset_request');
    }

    public function handlePasswordResetRequest(): void
    {
        try {
            $email = $_POST['email'] ?? '';
            $user = User::findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                User::createPasswordResetToken($user['id'], $token);
                // In a real application, you would send an email with this link:
                // $resetLink = "http://your.domain/password-reset/{$token}";
                // For this example, we'll just show a success message.
            }

            // Always show a generic success message to prevent user enumeration
            View::render('password_reset_request', ['success' => true]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function showPasswordResetForm(array $params): void
    {
        $token = $params['token'] ?? '';
        $user = User::findByPasswordResetToken($token);

        if (!$user) {
            // Or render a view with an error
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Invalid or expired token'], 404);
            return;
        }

        View::render('password_reset', ['token' => $token]);
    }

    public function handlePasswordReset(array $params): void
    {
        try {
            $token = $params['token'] ?? '';
            $password = $_POST['password'] ?? '';
            // Add password confirmation and validation

            $user = User::findByPasswordResetToken($token);

            if ($user) {
                User::updatePassword($user['id'], $password);
                User::deletePasswordResetToken($token);
                // Redirect to login or show success message
                header('Location: /login');
                exit;
            } else {
                // Or render a view with an error
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Invalid or expired token'], 404);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
